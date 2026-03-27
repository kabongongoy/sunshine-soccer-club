<?php
require('/var/www/html/wp-load.php');

$team_page = get_page_by_path('team');
if (!$team_page) { die("Team page not found\n"); }

// Build player list from SportsPress sp_player posts
$players = get_posts([
    'post_type'      => 'sp_player',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);

$player_cards = '';
foreach ($players as $p) {
    $url      = get_permalink($p->ID);
    $number   = get_post_meta($p->ID, 'sp_number', true);
    $number_display = $number ? '#' . $number . ' · ' : '';

    // Try to get a position if set via SportsPress taxonomy
    $positions = get_the_terms($p->ID, 'sp_position');
    $position = ($positions && !is_wp_error($positions)) ? $positions[0]->name : 'Player';

    $player_cards .= '<!-- wp:group {"style":{"border":{"radius":"8px","width":"1px","color":"#e0e0e0"},"spacing":{"padding":{"top":"24px","right":"24px","bottom":"24px","left":"24px"}}},"backgroundColor":"white"} -->' . "\n";
    $player_cards .= '<div class="wp-block-group has-white-background-color has-background" style="border-color:#e0e0e0;border-width:1px;border-style:solid;border-radius:8px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px">' . "\n";
    $player_cards .= '<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"20px","fontWeight":"700"},"color":{"text":"#1e3a8a"}}} -->' . "\n";
    $player_cards .= '<h3 class="wp-block-heading" style="color:#1e3a8a;font-size:20px;font-weight:700"><a href="' . esc_url($url) . '" style="color:#1e3a8a;text-decoration:none">' . esc_html($p->post_title) . '</a></h3>' . "\n";
    $player_cards .= '<!-- /wp:heading -->' . "\n";
    $player_cards .= '<!-- wp:paragraph {"style":{"color":{"text":"#666666"},"typography":{"fontSize":"14px"}}} -->' . "\n";
    $player_cards .= '<p style="color:#666666;font-size:14px">' . esc_html($number_display . $position) . '</p>' . "\n";
    $player_cards .= '<!-- /wp:paragraph -->' . "\n";
    $player_cards .= '<!-- wp:paragraph -->' . "\n";
    $player_cards .= '<p><a href="' . esc_url($url) . '" style="color:#f5a623;font-weight:600;text-decoration:none">View Profile →</a></p>' . "\n";
    $player_cards .= '<!-- /wp:paragraph -->' . "\n";
    $player_cards .= '</div>' . "\n";
    $player_cards .= '<!-- /wp:group -->' . "\n\n";
}

$content = '<!-- wp:heading {"style":{"color":{"text":"#1e3a8a"},"typography":{"fontSize":"36px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading" style="color:#1e3a8a;font-size:36px;font-weight:700">The Squad</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} -->
<p style="line-height:1.8">Meet the players who make Sunshine Soccer Club what it is. Click any player to view their full profile, position, and stats.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"columns":3,"style":{"spacing":{"padding":{"top":"32px","bottom":"32px"}}}} -->
<div class="wp-block-columns" style="padding-top:32px;padding-bottom:32px">

<!-- wp:column -->
<div class="wp-block-column">
' . $player_cards . '</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

<!-- wp:separator {"style":{"color":{"background":"#e0e0e0"}}} -->
<hr class="wp-block-separator has-alpha-channel-opacity has-background" style="background-color:#e0e0e0;border-color:#e0e0e0"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"14px","lineHeight":"1.7"},"color":{"text":"#666666"}}} -->
<p style="color:#666666;font-size:14px;line-height:1.7"><strong>Admin:</strong> To add a new player, go to <strong>wp-admin → SP → Players → Add New</strong>. Fill in the player\'s name, position, and jersey number. The player will automatically appear on this page.</p>
<!-- /wp:paragraph -->';

wp_update_post([
    'ID'           => $team_page->ID,
    'post_content' => $content,
]);

echo "Team page updated. Players listed: " . count($players) . "\n";
foreach ($players as $p) {
    echo "  - " . $p->post_title . " → " . get_permalink($p->ID) . "\n";
}
