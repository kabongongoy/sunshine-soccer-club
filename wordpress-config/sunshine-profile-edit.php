<?php
/**
 * Sunshine Soccer Club — Front-End Player Profile Editor
 *
 * Provides the [player_profile_edit] shortcode which renders a styled
 * front-end form so players can update their own profile without ever
 * entering wp-admin.
 *
 * Fields editable by the player:
 *  - Profile photo (featured image)
 *  - Bio (post content)
 *  - Birthday (day / month — no year)
 *  - Fun fact
 *  - Social media links (X, Instagram, Facebook, LinkedIn, YouTube, TikTok)
 *
 * Deploy:
 *   python transfer.py wordpress-config/sunshine-profile-edit.php \
 *     //var/www/html/wp-content/mu-plugins/sunshine-profile-edit.php
 *
 * Then create a WordPress page with the slug "edit-profile" and paste
 * [player_profile_edit] into its content.
 */

// ──────────────────────────────────────────────────────────────
// Helper — URL of the front-end edit page
// ──────────────────────────────────────────────────────────────

/**
 * Returns the permalink of the page whose slug is "edit-profile",
 * or null if that page doesn't exist yet.
 */
function ssc_profile_edit_url() {
    $page = get_page_by_path( 'edit-profile' );
    return $page ? get_permalink( $page->ID ) : null;
}

// ──────────────────────────────────────────────────────────────
// Process form submission (fires before any output)
// ──────────────────────────────────────────────────────────────

add_action( 'init', function () {
    if ( ! isset( $_POST['ssc_profile_edit_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['ssc_profile_edit_nonce'], 'ssc_profile_edit' ) ) return;
    if ( ! is_user_logged_in() ) return;

    $user = wp_get_current_user();
    if ( ! in_array( 'ssc_player', (array) $user->roles ) ) return;

    $post_id = (int) get_user_meta( $user->ID, 'ssc_player_post', true );
    if ( ! $post_id || get_post_type( $post_id ) !== 'sp_player' ) return;

    // ── Bio / post content ────────────────────────────────────
    $bio = wp_kses_post( stripslashes( $_POST['ssc_bio'] ?? '' ) );
    wp_update_post( [ 'ID' => $post_id, 'post_content' => $bio ] );

    // ── Profile photo upload ──────────────────────────────────
    if ( ! empty( $_FILES['ssc_photo']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload( 'ssc_photo', $post_id );
        if ( ! is_wp_error( $attachment_id ) ) {
            set_post_thumbnail( $post_id, $attachment_id );
        }
    }

    // ── Social links ──────────────────────────────────────────
    foreach ( [ 'twitter', 'instagram', 'facebook', 'linkedin', 'youtube', 'tiktok' ] as $key ) {
        $field = 'ssc_social_' . $key;
        $url   = esc_url_raw( trim( $_POST[ $field ] ?? '' ) );
        if ( $url ) {
            update_post_meta( $post_id, $field, $url );
        } else {
            delete_post_meta( $post_id, $field );
        }
    }

    // ── Birthday ──────────────────────────────────────────────
    $day   = (int) ( $_POST['ssc_birth_day']   ?? 0 );
    $month = (int) ( $_POST['ssc_birth_month'] ?? 0 );
    if ( $day >= 1   && $day <= 31  ) update_post_meta( $post_id, 'ssc_birth_day',   $day );
    else                              delete_post_meta( $post_id, 'ssc_birth_day' );
    if ( $month >= 1 && $month <= 12) update_post_meta( $post_id, 'ssc_birth_month', $month );
    else                              delete_post_meta( $post_id, 'ssc_birth_month' );

    // ── Funny fact ────────────────────────────────────────────
    $fact = sanitize_textarea_field( $_POST['ssc_funny_fact'] ?? '' );
    if ( $fact ) update_post_meta( $post_id, 'ssc_funny_fact', $fact );
    else         delete_post_meta( $post_id, 'ssc_funny_fact' );

    // ── Redirect with success flag ────────────────────────────
    $redirect = ssc_profile_edit_url() ?? home_url( '/' );
    wp_redirect( add_query_arg( 'profile_updated', '1', $redirect ) );
    exit;
} );

// ──────────────────────────────────────────────────────────────
// [player_profile_edit] shortcode
// ──────────────────────────────────────────────────────────────

add_shortcode( 'player_profile_edit', function () {
    ob_start();

    // ── Guard: must be logged-in player ──────────────────────
    if ( ! is_user_logged_in() ) {
        echo '<div class="ssc-edit-notice">
            <p>Please <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">log in</a> to edit your profile.</p>
        </div>';
        return ob_get_clean();
    }

    $user = wp_get_current_user();
    if ( ! in_array( 'ssc_player', (array) $user->roles ) ) {
        echo '<div class="ssc-edit-notice"><p>This page is for registered players only.</p></div>';
        return ob_get_clean();
    }

    $post_id = (int) get_user_meta( $user->ID, 'ssc_player_post', true );
    if ( ! $post_id ) {
        echo '<div class="ssc-edit-notice"><p>Your account is not linked to a player profile yet. Contact the club admin to get set up.</p></div>';
        return ob_get_clean();
    }

    $player = get_post( $post_id );
    if ( ! $player ) {
        echo '<div class="ssc-edit-notice"><p>Player profile not found. Please contact the admin.</p></div>';
        return ob_get_clean();
    }

    // ── Current values ────────────────────────────────────────
    $bio         = $player->post_content;
    $photo_id    = get_post_thumbnail_id( $post_id );
    $photo_url   = $photo_id ? wp_get_attachment_image_url( $photo_id, 'medium' ) : null;
    $player_name = get_the_title( $post_id );
    $player_url  = get_permalink( $post_id );

    $birth_day   = (int) get_post_meta( $post_id, 'ssc_birth_day',   true );
    $birth_month = (int) get_post_meta( $post_id, 'ssc_birth_month', true );
    $funny_fact  = get_post_meta( $post_id, 'ssc_funny_fact', true );

    $months = [ '', 'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December' ];

    $social_labels = [
        'twitter'   => 'X / Twitter',
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'linkedin'  => 'LinkedIn',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
    ];
    $social_placeholders = [
        'twitter'   => 'https://x.com/username',
        'instagram' => 'https://instagram.com/username',
        'facebook'  => 'https://facebook.com/username',
        'linkedin'  => 'https://linkedin.com/in/username',
        'youtube'   => 'https://youtube.com/@username',
        'tiktok'    => 'https://tiktok.com/@username',
    ];

    // ── Success banner ────────────────────────────────────────
    $updated = isset( $_GET['profile_updated'] ) && $_GET['profile_updated'] === '1';

    ?>
    <style>
    /* ── Wrapper ─────────────────────────────────────────── */
    .ssc-edit-wrap {
        max-width: 720px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #1e293b;
    }
    .ssc-edit-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
        padding-bottom: 20px;
        border-bottom: 3px solid #f5a623;
    }
    .ssc-edit-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #1e3a8a;
    }
    .ssc-edit-header a {
        font-size: 13px;
        color: #64748b;
        text-decoration: none;
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .ssc-edit-header a:hover { color: #1e3a8a; }

    /* ── Success / error notices ─────────────────────────── */
    .ssc-edit-notice {
        padding: 14px 18px;
        border-radius: 8px;
        margin-bottom: 24px;
        font-size: 14px;
        font-weight: 500;
    }
    .ssc-edit-notice.success {
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #15803d;
    }
    .ssc-edit-notice.info {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e3a8a;
    }

    /* ── Sections ────────────────────────────────────────── */
    .ssc-edit-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    .ssc-edit-section h3 {
        margin: 0 0 18px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }

    /* ── Photo area ──────────────────────────────────────── */
    .ssc-photo-row {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    .ssc-photo-preview {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f5a623;
        flex-shrink: 0;
    }
    .ssc-photo-initials {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: #1e3a8a;
        color: #fff;
        font-size: 28px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 3px solid #f5a623;
    }
    .ssc-file-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f8faff;
        border: 1.5px dashed #93c5fd;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 13px;
        color: #1e3a8a;
        cursor: pointer;
        transition: background .15s;
    }
    .ssc-file-label:hover { background: #eff6ff; }
    .ssc-file-label input[type="file"] { display: none; }
    .ssc-file-name {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }

    /* ── Form fields ─────────────────────────────────────── */
    .ssc-field { margin-bottom: 16px; }
    .ssc-field:last-child { margin-bottom: 0; }
    .ssc-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .ssc-field input[type="url"],
    .ssc-field input[type="text"],
    .ssc-field select,
    .ssc-field textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        outline: none;
        transition: border-color .15s;
    }
    .ssc-field input:focus,
    .ssc-field select:focus,
    .ssc-field textarea:focus {
        border-color: #1e3a8a;
        box-shadow: 0 0 0 3px rgba(30,58,138,.08);
    }
    .ssc-field textarea { resize: vertical; min-height: 100px; }
    .ssc-field .hint {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 5px;
    }

    /* ── Birthday row ────────────────────────────────────── */
    .ssc-birthday-row {
        display: flex;
        gap: 12px;
    }
    .ssc-birthday-row select { flex: 1; }

    /* ── Social grid ─────────────────────────────────────── */
    .ssc-social-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }

    /* ── Submit button ───────────────────────────────────── */
    .ssc-submit-row {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .ssc-btn-save {
        background: #1e3a8a;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 12px 28px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, transform .12s;
    }
    .ssc-btn-save:hover { background: #1e40af; transform: translateY(-1px); }
    .ssc-view-profile {
        font-size: 13px;
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
    }
    .ssc-view-profile:hover { color: #1e3a8a; }
    </style>

    <div class="ssc-edit-wrap">

        <?php if ( $updated ) : ?>
        <div class="ssc-edit-notice success">
            Profile updated! Your changes are now live on your <a href="<?php echo esc_url( $player_url ); ?>">public profile</a>.
        </div>
        <?php endif; ?>

        <div class="ssc-edit-header">
            <h2>Edit My Profile</h2>
            <a href="<?php echo esc_url( $player_url ); ?>">View public profile &rarr;</a>
        </div>

        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field( 'ssc_profile_edit', 'ssc_profile_edit_nonce' ); ?>

            <!-- ── Photo ───────────────────────────────────── -->
            <div class="ssc-edit-section">
                <h3>Profile Photo</h3>
                <div class="ssc-photo-row">
                    <?php if ( $photo_url ) : ?>
                        <img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $player_name ); ?>" class="ssc-photo-preview" id="ssc-photo-preview">
                    <?php else :
                        $initials = implode( '', array_map( fn($w) => strtoupper( $w[0] ), explode( ' ', $player_name ) ) );
                        ?>
                        <div class="ssc-photo-initials" id="ssc-photo-preview-wrap">
                            <?php echo esc_html( mb_substr( $initials, 0, 2 ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label class="ssc-file-label">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Upload new photo
                            <input type="file" name="ssc_photo" id="ssc_photo" accept="image/*">
                        </label>
                        <div class="ssc-file-name" id="ssc-file-name">JPG, PNG or WEBP, max 8 MB</div>
                    </div>
                </div>
            </div>

            <!-- ── Bio ────────────────────────────────────── -->
            <div class="ssc-edit-section">
                <h3>About Me</h3>
                <div class="ssc-field">
                    <label for="ssc_bio">Bio</label>
                    <textarea name="ssc_bio" id="ssc_bio" rows="5"><?php echo esc_textarea( $bio ); ?></textarea>
                    <p class="hint">A short introduction shown on your profile page. You can talk about your position, playing style, or anything about yourself.</p>
                </div>
                <div class="ssc-field">
                    <label for="ssc_funny_fact">Fun Fact</label>
                    <textarea name="ssc_funny_fact" id="ssc_funny_fact" rows="2"><?php echo esc_textarea( $funny_fact ); ?></textarea>
                    <p class="hint">Something fun or funny about you — shown with a smile emoji on your profile!</p>
                </div>
                <div class="ssc-field">
                    <label>Birthday <span style="font-weight:400;color:#94a3b8">(day and month only)</span></label>
                    <div class="ssc-birthday-row">
                        <select name="ssc_birth_day">
                            <option value="0">Day</option>
                            <?php for ( $d = 1; $d <= 31; $d++ ) : ?>
                                <option value="<?php echo $d; ?>"<?php selected( $birth_day, $d ); ?>><?php echo $d; ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="ssc_birth_month">
                            <option value="0">Month</option>
                            <?php for ( $m = 1; $m <= 12; $m++ ) : ?>
                                <option value="<?php echo $m; ?>"<?php selected( $birth_month, $m ); ?>><?php echo $months[ $m ]; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <p class="hint">Used for birthday shout-outs. No year needed.</p>
                </div>
            </div>

            <!-- ── Social links ───────────────────────────── -->
            <div class="ssc-edit-section">
                <h3>Social Media</h3>
                <div class="ssc-social-grid">
                    <?php foreach ( $social_labels as $key => $label ) :
                        $val = get_post_meta( $post_id, 'ssc_social_' . $key, true );
                        ?>
                        <div class="ssc-field">
                            <label for="ssc_social_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
                            <input type="url"
                                   name="ssc_social_<?php echo esc_attr( $key ); ?>"
                                   id="ssc_social_<?php echo esc_attr( $key ); ?>"
                                   value="<?php echo esc_attr( $val ); ?>"
                                   placeholder="<?php echo esc_attr( $social_placeholders[ $key ] ); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="hint" style="margin-top:12px">Enter full URLs. Leave blank to hide the icon on your profile.</p>
            </div>

            <!-- ── Submit ──────────────────────────────────── -->
            <div class="ssc-submit-row">
                <button type="submit" class="ssc-btn-save">Save Profile</button>
                <a href="<?php echo esc_url( $player_url ); ?>" class="ssc-view-profile">View my public profile &rarr;</a>
            </div>

        </form>
    </div>

    <script>
    // Show chosen filename next to upload button
    document.getElementById('ssc_photo').addEventListener('change', function () {
        var label = document.getElementById('ssc-file-name');
        label.textContent = this.files.length ? this.files[0].name : 'JPG, PNG or WEBP, max 8 MB';

        // Live preview
        if (this.files.length) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var preview = document.getElementById('ssc-photo-preview');
                if (!preview) {
                    // replace the initials div
                    var wrap = document.getElementById('ssc-photo-preview-wrap');
                    if (wrap) {
                        var img = document.createElement('img');
                        img.id = 'ssc-photo-preview';
                        img.className = 'ssc-photo-preview';
                        img.alt = '';
                        wrap.parentNode.replaceChild(img, wrap);
                        preview = img;
                    }
                }
                if (preview) preview.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    </script>
    <?php

    return ob_get_clean();
} );
