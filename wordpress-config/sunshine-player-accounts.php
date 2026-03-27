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

    $post_id = ssc_player_post( $user );
    return $post_id
        ? admin_url( 'post.php?post=' . $post_id . '&action=edit' )
        : home_url( '/' );
}, 10, 3 );

// ── 4. Restrict admin pages for players ──────────────────────
add_action( 'admin_menu', function () {
    if ( ssc_is_admin() || ! ssc_is_player() ) return;

    foreach ( [
        'index.php', 'edit.php', 'upload.php', 'edit-comments.php',
        'themes.php', 'plugins.php', 'users.php', 'tools.php',
        'options-general.php', 'sportspress', 'buddypress',
        'edit.php?post_type=sp_player', 'edit.php?post_type=sp_event',
        'edit.php?post_type=sp_team',
    ] as $page ) {
        remove_menu_page( $page );
    }
}, 999 );

add_action( 'current_screen', function () {
    // Never touch admins — bail immediately
    if ( ssc_is_admin() ) return;
    if ( ! ssc_is_player() ) return;

    $post_id = ssc_player_post();
    if ( ! $post_id ) {
        // Player has no linked post — send to front-end
        wp_redirect( home_url( '/' ) );
        exit;
    }

    $screen = get_current_screen();

    // Allow their own post edit page and media uploader
    if ( in_array( $screen->base, [ 'post', 'media' ] ) ) {
        if ( $screen->base === 'post' ) {
            $editing = (int) ( $_GET['post'] ?? 0 );
            if ( $editing && $editing !== $post_id ) {
                wp_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
                exit;
            }
        }
        return;
    }

    // All other admin screens → their own edit page
    wp_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
    exit;
} );

// ── 5. Clean up edit screen for players ──────────────────────
add_action( 'add_meta_boxes', function () {
    if ( ssc_is_admin() || ! ssc_is_player() ) return;

    foreach ( [
        'sp_playerdetails', 'sp_playerevents', 'sp_playerstatistics',
        'sp_leagues', 'sp_seasons', 'sp_positions', 'sp_nationality',
        'submitdiv', 'authordiv', 'slugdiv', 'pageparentdiv',
        'postcustom', 'commentstatusdiv', 'commentsdiv',
        'revisionsdiv', 'trackbacksdiv', 'postexcerpt',
        'ssc_linked_user',
    ] as $id ) {
        remove_meta_box( $id, 'sp_player', 'normal' );
        remove_meta_box( $id, 'sp_player', 'side' );
        remove_meta_box( $id, 'sp_player', 'advanced' );
    }

    add_meta_box(
        'ssc_player_save', 'Save Profile',
        function () {
            echo '<button type="submit" name="save" value="Save Profile"
                class="button button-primary button-large"
                style="width:100%;padding:10px;font-size:15px">Save My Profile</button>';
            echo '<p style="color:#888;font-size:12px;margin-top:8px">Update your bio and profile photo.</p>';
        },
        'sp_player', 'side', 'high'
    );

    add_meta_box( 'postimagediv', 'My Profile Photo',
        'post_thumbnail_meta_box', 'sp_player', 'side', 'high' );
}, 5 );

// ── 6. "Edit My Profile" button on public player page ────────
add_filter( 'the_content', function ( $content ) {
    if ( ! is_singular( 'sp_player' ) || ! is_user_logged_in() ) return $content;
    if ( ssc_is_admin() ) return $content;

    $post_id = get_the_ID();
    $linked  = ssc_player_post();
    if ( $linked !== $post_id ) return $content;

    $url    = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    $button = '<div style="margin-bottom:20px">
        <a href="' . esc_url( $url ) . '"
           style="display:inline-flex;align-items:center;gap:8px;background:#1e3a8a;
                  color:#fff;padding:10px 20px;border-radius:6px;
                  text-decoration:none;font-weight:600;font-size:14px">
            ✏ Edit My Profile
        </a></div>';

    return $button . $content;
}, 5 );

add_action( 'admin_bar_menu', function ( $bar ) {
    if ( ! is_user_logged_in() || ssc_is_admin() ) return;
    $post_id = ssc_player_post();
    if ( ! $post_id ) return;

    $bar->add_node( [
        'id'    => 'ssc_edit_profile',
        'title' => '✏ Edit My Profile',
        'href'  => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
    ] );
}, 100 );

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
