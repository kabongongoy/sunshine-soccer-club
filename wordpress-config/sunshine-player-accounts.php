<?php
/**
 * Sunshine Soccer Club — Player Account System
 *
 * Links WordPress user accounts to SportsPress player posts so that:
 *  - Admin links a user to a player post via a meta box (SP → Players → Edit)
 *  - The user gets the 'ssc_player' role automatically
 *  - On login, players land on their own player edit page
 *  - Players can ONLY edit their own player post (not anyone else's)
 *  - An "Edit My Profile" button appears on their public profile page
 */

// ── Helpers ───────────────────────────────────────────────────
function ssc_is_admin( $user = null ) {
    if ( ! $user ) $user = wp_get_current_user();
    return in_array( 'administrator', (array) $user->roles ) || is_super_admin( $user->ID );
}

function ssc_is_player( $user = null ) {
    if ( ! $user ) $user = wp_get_current_user();
    return in_array( 'ssc_player', (array) $user->roles );
}

function ssc_player_post( $user = null ) {
    if ( ! $user ) $user = wp_get_current_user();
    return (int) get_user_meta( $user->ID, 'ssc_player_post', true );
}

// ── 1. Register role ──────────────────────────────────────────
add_action( 'init', function () {
    if ( ! get_role( 'ssc_player' ) ) {
        add_role( 'ssc_player', 'Player', [
            'read'         => true,
            'upload_files' => true,
        ] );
    }
} );

// ── 2. Capability grants ──────────────────────────────────────
add_filter( 'user_has_cap', function ( $allcaps, $caps, $args, $user ) {

    // Grant all SportsPress caps to administrators
    // NOTE: must use direct role check here — calling user_can() or is_super_admin() inside user_has_cap causes recursion
    if ( in_array( 'administrator', (array) $user->roles ) ) {
        foreach ( $caps as $cap ) {
            if ( strpos( $cap, 'sp_' ) !== false || $cap === 'manage_sportspress' ) {
                $allcaps[ $cap ] = true;
            }
        }
        return $allcaps;
    }

    // For players: grant edit access to their linked post only
    if ( ! in_array( 'ssc_player', (array) $user->roles ) ) return $allcaps;
    if ( ( $args[0] ?? '' ) !== 'edit_post' )               return $allcaps;

    $post_id = (int) ( $args[2] ?? 0 );
    if ( ! $post_id ) return $allcaps;

    $linked = ssc_player_post( $user );
    if ( $linked && $post_id === $linked && get_post_type( $post_id ) === 'sp_player' ) {
        // Grant whatever primitive caps WordPress mapped to — covers edit_sp_player
        // AND edit_others_sp_players (when the post author is admin, not the player)
        foreach ( $caps as $cap ) {
            $allcaps[ $cap ] = true;
        }
        $allcaps['edit_posts']           = true;
        $allcaps['edit_published_posts'] = true;
    }

    return $allcaps;
}, 10, 4 );

// ── 3. Login redirect ─────────────────────────────────────────
add_filter( 'login_redirect', function ( $redirect_to, $request, $user ) {
    if ( is_wp_error( $user ) )      return $redirect_to;
    if ( ssc_is_admin( $user ) )     return $redirect_to;
    if ( ! ssc_is_player( $user ) )  return $redirect_to;

    // Prefer the front-end edit page if it exists
    if ( function_exists( 'ssc_profile_edit_url' ) ) {
        $front_url = ssc_profile_edit_url();
        if ( $front_url ) return $front_url;
    }

    $post_id = ssc_player_post( $user );
    return $post_id
        ? admin_url( 'post.php?post=' . $post_id . '&action=edit' )
        : home_url( '/' );
}, 10, 3 );

// ── 4. Hide admin bar and block wp-admin for players ─────────

// Hide the WordPress admin bar entirely — players only use the front-end form
add_filter( 'show_admin_bar', function ( $show ) {
    if ( is_user_logged_in() && ssc_is_player() ) return false;
    return $show;
} );

// Redirect any wp-admin visit straight to the front-end edit page
add_action( 'current_screen', function () {
    if ( ssc_is_admin() || ! ssc_is_player() ) return;

    $dest = ( function_exists( 'ssc_profile_edit_url' ) ? ssc_profile_edit_url() : null )
            ?? home_url( '/' );

    wp_redirect( $dest );
    exit;
} );

// ── 5. "Edit My Profile" button on public player page ────────
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'sp_player' ) || ! is_user_logged_in() ) return $content;
    if ( ssc_is_admin() ) return $content;

    $post_id = get_the_ID();
    $linked  = ssc_player_post();
    if ( $linked !== $post_id ) return $content;

    // Prefer the front-end edit page; fall back to wp-admin
    $url = null;
    if ( function_exists( 'ssc_profile_edit_url' ) ) {
        $url = ssc_profile_edit_url();
    }
    if ( ! $url ) {
        $url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    }

    $button = '<div style="margin-bottom:20px">
        <a href="' . esc_url( $url ) . '"
           style="display:inline-flex;align-items:center;gap:8px;background:#1e3a8a;
                  color:#fff;padding:10px 20px;border-radius:6px;
                  text-decoration:none;font-weight:600;font-size:14px">
            ✏ Edit My Profile
        </a></div>';

    return $button . $content;
}, 5 );

// ── 7. Admin meta box: link a user to a player post ──────────
add_action( 'add_meta_boxes', function () {
    add_meta_box( 'ssc_linked_user', 'Linked Player Account',
        'ssc_linked_user_box', 'sp_player', 'side', 'default' );
} );

function ssc_linked_user_box( $post ) {
    wp_nonce_field( 'ssc_linked_user_save', 'ssc_linked_user_nonce' );
    $linked = (int) get_post_meta( $post->ID, 'ssc_linked_user', true );
    $users  = get_users( [ 'role__not_in' => [ 'administrator' ], 'orderby' => 'display_name' ] );

    echo '<select name="ssc_linked_user" style="width:100%;margin-bottom:8px">';
    echo '<option value="0">— Not linked —</option>';
    foreach ( $users as $u ) {
        $tag = in_array( 'ssc_player', (array) $u->roles ) ? ' [Player]' : '';
        printf( '<option value="%d"%s>%s</option>',
            $u->ID,
            selected( $linked, $u->ID, false ),
            esc_html( $u->display_name . ' (' . $u->user_login . ')' . $tag )
        );
    }
    echo '</select>';
    echo '<p style="color:#888;font-size:11px;margin:0">Linked user can edit bio and photo only.</p>';
}

add_action( 'save_post_sp_player', function ( $post_id ) {
    if ( ! isset( $_POST['ssc_linked_user_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['ssc_linked_user_nonce'], 'ssc_linked_user_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! ssc_is_admin() ) return;

    $old_id = (int) get_post_meta( $post_id, 'ssc_linked_user', true );
    $new_id = (int) ( $_POST['ssc_linked_user'] ?? 0 );

    if ( $old_id && $old_id !== $new_id ) {
        delete_user_meta( $old_id, 'ssc_player_post' );
        $old = get_user_by( 'id', $old_id );
        if ( $old && ssc_is_player( $old ) ) $old->set_role( 'subscriber' );
        delete_post_meta( $post_id, 'ssc_linked_user' );
    }

    if ( $new_id ) {
        update_post_meta( $post_id, 'ssc_linked_user', $new_id );
        update_user_meta( $new_id, 'ssc_player_post', $post_id );
        $new = get_user_by( 'id', $new_id );
        if ( $new && ! ssc_is_admin( $new ) ) $new->set_role( 'ssc_player' );
    }
} );
