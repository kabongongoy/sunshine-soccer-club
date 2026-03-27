<?php
/**
 * Sunshine Soccer Club — Player Social Media Links
 *
 * Adds social media fields to every SportsPress player post:
 *  - Meta box in wp-admin → SP → Players → Edit
 *  - Renders icons + links on the public player profile page
 *
 * Supported networks: X (Twitter), Instagram, Facebook,
 *                     LinkedIn, YouTube, TikTok
 */

// ──────────────────────────────────────────────────────────────
// 1. Social network definitions
// ──────────────────────────────────────────────────────────────

function ssc_social_networks() {
    return [
        'twitter'   => [
            'label' => 'X / Twitter',
            'placeholder' => 'https://x.com/username',
            'color' => '#000000',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.731-8.843L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'placeholder' => 'https://instagram.com/username',
            'color' => '#E1306C',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        ],
        'facebook'  => [
            'label' => 'Facebook',
            'placeholder' => 'https://facebook.com/username',
            'color' => '#1877F2',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        ],
        'linkedin'  => [
            'label' => 'LinkedIn',
            'placeholder' => 'https://linkedin.com/in/username',
            'color' => '#0A66C2',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        ],
        'youtube'   => [
            'label' => 'YouTube',
            'placeholder' => 'https://youtube.com/@username',
            'color' => '#FF0000',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        ],
        'tiktok'    => [
            'label' => 'TikTok',
            'placeholder' => 'https://tiktok.com/@username',
            'color' => '#010101',
            'svg'   => '<svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
        ],
    ];
}

// ──────────────────────────────────────────────────────────────
// 2. Birthday + Funny Fact meta box (visible to players too)
// ──────────────────────────────────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ssc_player_extras',
        'Birthday &amp; Fun Facts',
        'ssc_player_extras_box_html',
        'sp_player',
        'normal',
        'high'
    );
});

function ssc_player_extras_box_html($post) {
    wp_nonce_field('ssc_extras_save', 'ssc_extras_nonce');
    $birth_day   = (int) get_post_meta($post->ID, 'ssc_birth_day', true);
    $birth_month = (int) get_post_meta($post->ID, 'ssc_birth_month', true);
    $funny_fact  = esc_textarea(get_post_meta($post->ID, 'ssc_funny_fact', true));

    $months = ['', 'January','February','March','April','May','June',
               'July','August','September','October','November','December'];

    echo '<table style="width:100%;border-collapse:collapse">';

    // Birthday row
    echo '<tr style="border-bottom:1px solid #f0f0f0">';
    echo '<td style="width:130px;padding:10px 12px 10px 0;font-weight:600;color:#444;vertical-align:top;padding-top:14px">Birthday</td>';
    echo '<td style="padding:10px 0">';
    echo '<div style="display:flex;gap:10px;align-items:center">';

    // Day dropdown
    echo '<select name="ssc_birth_day" style="padding:6px 8px;border:1px solid #ddd;border-radius:4px">';
    echo '<option value="0">Day</option>';
    for ($d = 1; $d <= 31; $d++) {
        $sel = selected($birth_day, $d, false);
        echo "<option value=\"$d\"$sel>$d</option>";
    }
    echo '</select>';

    // Month dropdown
    echo '<select name="ssc_birth_month" style="padding:6px 8px;border:1px solid #ddd;border-radius:4px">';
    echo '<option value="0">Month</option>';
    for ($m = 1; $m <= 12; $m++) {
        $sel = selected($birth_month, $m, false);
        echo "<option value=\"$m\"$sel>{$months[$m]}</option>";
    }
    echo '</select>';
    echo '</div>';
    echo '<p style="color:#888;font-size:12px;margin:6px 0 0">Day and month only — no year needed. Used for birthday celebrations!</p>';
    echo '</td></tr>';

    // Funny fact row
    echo '<tr>';
    echo '<td style="width:130px;padding:10px 12px 10px 0;font-weight:600;color:#444;vertical-align:top;padding-top:14px">Funny Fact</td>';
    echo '<td style="padding:10px 0">';
    echo '<textarea name="ssc_funny_fact" rows="3" style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px;resize:vertical" placeholder="Share a fun or funny fact about yourself...">' . $funny_fact . '</textarea>';
    echo '<p style="color:#888;font-size:12px;margin:4px 0 0">Shown on your public profile. Keep it fun!</p>';
    echo '</td></tr>';

    echo '</table>';
}

add_action('save_post_sp_player', function ($post_id) {
    if (! isset($_POST['ssc_extras_nonce']) || ! wp_verify_nonce($_POST['ssc_extras_nonce'], 'ssc_extras_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (! current_user_can('edit_post', $post_id)) return;

    $day   = (int) ($_POST['ssc_birth_day'] ?? 0);
    $month = (int) ($_POST['ssc_birth_month'] ?? 0);
    if ($day >= 1 && $day <= 31)   update_post_meta($post_id, 'ssc_birth_day',   $day);
    else                            delete_post_meta($post_id, 'ssc_birth_day');
    if ($month >= 1 && $month <= 12) update_post_meta($post_id, 'ssc_birth_month', $month);
    else                             delete_post_meta($post_id, 'ssc_birth_month');

    $fact = sanitize_textarea_field($_POST['ssc_funny_fact'] ?? '');
    if ($fact) update_post_meta($post_id, 'ssc_funny_fact', $fact);
    else       delete_post_meta($post_id, 'ssc_funny_fact');
});

// ──────────────────────────────────────────────────────────────
// Admin meta box — Social Links
// ──────────────────────────────────────────────────────────────

add_action('add_meta_boxes', function () {
    add_meta_box(
        'ssc_social_links',
        'Social Media Links',
        'ssc_social_meta_box_html',
        'sp_player',
        'normal',
        'default'
    );
});

function ssc_social_meta_box_html($post) {
    wp_nonce_field('ssc_social_save', 'ssc_social_nonce');
    $networks = ssc_social_networks();
    echo '<table style="width:100%;border-collapse:collapse">';
    foreach ($networks as $key => $net) {
        $val = esc_attr(get_post_meta($post->ID, 'ssc_social_' . $key, true));
        echo '<tr style="border-bottom:1px solid #f0f0f0">';
        echo '<td style="width:130px;padding:8px 12px 8px 0;font-weight:600;color:#444">' . esc_html($net['label']) . '</td>';
        echo '<td style="padding:8px 0"><input type="url" name="ssc_social_' . esc_attr($key) . '" value="' . $val . '" placeholder="' . esc_attr($net['placeholder']) . '" style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:4px" /></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '<p style="color:#888;font-size:12px;margin-top:8px">Enter full URLs (e.g. https://instagram.com/username). Leave blank to hide.</p>';
}

add_action('save_post_sp_player', function ($post_id) {
    if (! isset($_POST['ssc_social_nonce']) || ! wp_verify_nonce($_POST['ssc_social_nonce'], 'ssc_social_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (! current_user_can('edit_post', $post_id)) return;

    foreach (array_keys(ssc_social_networks()) as $key) {
        $field = 'ssc_social_' . $key;
        if (isset($_POST[$field])) {
            $url = esc_url_raw(trim($_POST[$field]));
            if ($url) {
                update_post_meta($post_id, $field, $url);
            } else {
                delete_post_meta($post_id, $field);
            }
        }
    }
});

// ──────────────────────────────────────────────────────────────
// 3. Front-end: append social icons to player page content
// ──────────────────────────────────────────────────────────────

add_filter('the_content', function ($content) {
    if (! is_singular('sp_player')) return $content;

    $id       = get_the_ID();
    $networks = ssc_social_networks();
    $links    = [];

    foreach ($networks as $key => $net) {
        $url = get_post_meta($id, 'ssc_social_' . $key, true);
        if ($url) {
            $links[] = [
                'url'   => $url,
                'label' => $net['label'],
                'color' => $net['color'],
                'svg'   => $net['svg'],
            ];
        }
    }

    // Birthday & funny fact
    $months      = ['','January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    $birth_day   = (int) get_post_meta($id, 'ssc_birth_day', true);
    $birth_month = (int) get_post_meta($id, 'ssc_birth_month', true);
    $funny_fact  = get_post_meta($id, 'ssc_funny_fact', true);

    $extras_html = '';
    if ($birth_day && $birth_month) {
        $birthday_str = $months[$birth_month] . ' ' . $birth_day;
        $extras_html .= '<div class="ssc-extra-item"><span class="ssc-extra-icon">🎂</span><div><strong>Birthday</strong><span>' . esc_html($birthday_str) . '</span></div></div>';
    }
    if ($funny_fact) {
        $extras_html .= '<div class="ssc-extra-item ssc-extra-fact"><span class="ssc-extra-icon">😄</span><div><strong>Fun Fact</strong><span>' . esc_html($funny_fact) . '</span></div></div>';
    }

    $extras_block = $extras_html ? '
<div class="ssc-extras-section">
    <h4 class="ssc-social-heading">About</h4>
    <div class="ssc-extras-list">' . $extras_html . '</div>
</div>' : '';

    if (empty($links) && ! $extras_block) return $content;

    $icons_html = '';
    foreach ($links as $link) {
        $icons_html .= sprintf(
            '<a href="%s" target="_blank" rel="noopener noreferrer" class="ssc-social-link" title="%s" style="--ssc-brand:%s">%s</a>',
            esc_url($link['url']),
            esc_attr($link['label']),
            esc_attr($link['color']),
            $link['svg']
        );
    }

    $social_block = $icons_html ? '
<div class="ssc-social-section">
    <h4 class="ssc-social-heading">Connect</h4>
    <div class="ssc-social-icons">' . $icons_html . '</div>
</div>' : '';

    $social_html = $extras_block . $social_block . '
<style>
.ssc-extras-section, .ssc-social-section {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}
.ssc-social-heading {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #888;
    margin: 0 0 12px;
    font-weight: 600;
}
.ssc-extras-list { display: flex; flex-direction: column; gap: 10px; }
.ssc-extra-item {
    display: flex; align-items: flex-start; gap: 12px;
    background: #f8faff; border-radius: 8px; padding: 10px 14px;
    border: 1px solid #e8eef8;
}
.ssc-extra-fact { background: #fffbf0; border-color: #fde68a; }
.ssc-extra-icon { font-size: 20px; flex-shrink: 0; margin-top: 2px; }
.ssc-extra-item strong { display: block; font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
.ssc-extra-item span { font-size: 15px; color: #1e293b; }
.ssc-social-icons { display: flex; flex-wrap: wrap; gap: 10px; }
.ssc-social-link {
    display: inline-flex; align-items: center; justify-content: center;
    width: 42px; height: 42px; border-radius: 8px;
    background: #f4f4f4; color: var(--ssc-brand, #333);
    text-decoration: none; transition: background 0.18s, transform 0.15s, color 0.18s;
    border: 1px solid #e8e8e8;
}
.ssc-social-link:hover {
    background: var(--ssc-brand, #333); color: #fff;
    transform: translateY(-2px); border-color: transparent;
}
.ssc-social-link svg { display: block; }
</style>';

    return $content . $social_html;
});
