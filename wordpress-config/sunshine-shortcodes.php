<?php
/**
 * Sunshine Soccer Club — custom shortcodes
 * Installed as a must-use plugin so it always loads.
 *
 * Usage: [squad] — outputs all published SportsPress players as cards
 */

// Dynamic squad grid — auto-updates when players are added/removed in SP
add_shortcode('squad', function () {
    $players = get_posts([
        'post_type'      => 'sp_player',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    if (empty($players)) {
        return '<p style="color:#666">No players added yet. Go to <strong>SP → Players → Add New</strong> to add your first player.</p>';
    }

    $html = '<div class="ssc-squad-grid">';
    foreach ($players as $p) {
        $url      = get_permalink($p->ID);
        $number   = get_post_meta($p->ID, 'sp_number', true);
        $positions = get_the_terms($p->ID, 'sp_position');
        $position  = ($positions && ! is_wp_error($positions)) ? $positions[0]->name : 'Player';
        $thumb     = get_the_post_thumbnail_url($p->ID, 'thumbnail');

        $html .= '<a href="' . esc_url($url) . '" class="ssc-player-card">';
        if ($thumb) {
            $html .= '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($p->post_title) . '" class="ssc-player-photo" />';
        } else {
            $html .= '<div class="ssc-player-avatar">' . esc_html(mb_substr($p->post_title, 0, 1)) . '</div>';
        }
        $html .= '<div class="ssc-player-info">';
        $html .= '<span class="ssc-player-name">' . esc_html($p->post_title) . '</span>';
        $detail = $position;
        if ($number) $detail = '#' . esc_html($number) . ' &middot; ' . $detail;
        $html .= '<span class="ssc-player-meta">' . $detail . '</span>';
        $html .= '</div>';
        $html .= '</a>';
    }
    $html .= '</div>';

    // Inline styles — scoped, no extra stylesheet needed
    $css = '
<style>
.ssc-squad-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
    margin: 24px 0;
}
.ssc-player-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px 12px;
    text-decoration: none;
    transition: box-shadow 0.2s, transform 0.2s;
}
.ssc-player-card:hover {
    box-shadow: 0 4px 16px rgba(30,58,138,0.12);
    transform: translateY(-2px);
}
.ssc-player-photo {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
    border: 3px solid #f5a623;
}
.ssc-player-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #1e3a8a;
    color: #fff;
    font-size: 28px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    border: 3px solid #f5a623;
}
.ssc-player-name {
    display: block;
    font-weight: 700;
    font-size: 15px;
    color: #1e3a8a;
    margin-bottom: 4px;
}
.ssc-player-meta {
    display: block;
    font-size: 12px;
    color: #888;
}
</style>';

    return $css . $html;
});
