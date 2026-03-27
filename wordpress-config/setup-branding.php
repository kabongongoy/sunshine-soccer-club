<?php
require('/var/www/html/wp-load.php');

// ── 1. Import logo into media library ─────────────────────────────────────
$logo_path = '/var/www/html/sfc-logo.jpg';
$upload_dir = wp_upload_dir();

$filename  = 'sunshine-fc-logo.jpg';
$dest_path = $upload_dir['path'] . '/' . $filename;
copy($logo_path, $dest_path);

$filetype   = wp_check_filetype($filename, null);
$attachment = [
    'guid'           => $upload_dir['url'] . '/' . $filename,
    'post_mime_type' => $filetype['type'],
    'post_title'     => 'Sunshine Football Club Logo',
    'post_content'   => '',
    'post_status'    => 'inherit',
];
$attach_id = wp_insert_attachment($attachment, $dest_path);
require_once(ABSPATH . 'wp-admin/includes/image.php');
$attach_data = wp_generate_attachment_metadata($attach_id, $dest_path);
wp_update_attachment_metadata($attach_id, $attach_data);

echo "Logo imported, attachment ID: $attach_id\n";

// ── 2. Set as site logo (custom_logo) ──────────────────────────────────────
set_theme_mod('custom_logo', $attach_id);
echo "Logo set as site logo.\n";

// ── 3. Update site name & tagline ──────────────────────────────────────────
update_option('blogname', 'Sunshine Soccer Club');
update_option('blogdescription', 'Your Local Sunday Football Club');
echo "Site name updated.\n";

// ── 4. Update theme colours to navy blue + gold ────────────────────────────
set_theme_mod('accent_color', '#f5a623');           // gold
set_theme_mod('header_textcolor', '#ffffff');
set_theme_mod('background_color', '#f5f5f5');
set_theme_mod('footer_widgets_background', '#1e3a8a');
set_theme_mod('footer_background', '#1e3a8a');
set_theme_mod('offcanvas_menu_background', '#1e3a8a');
echo "Theme colours updated (navy + gold).\n";

// ── 5. Update Home page content ─────────────────────────────────────────────
$home_content = <<<'HTML'
<!-- wp:cover {"customOverlayColor":"#1e3a8a","dimRatio":75,"minHeight":520,"minHeightUnit":"px","align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:80px;padding-bottom:80px;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-75 has-background-dim" style="background-color:#1e3a8a"></span><div class="wp-block-cover__inner-container">
<!-- wp:image {"align":"center","width":180,"height":180,"sizeSlug":"full","style":{"border":{"radius":"50%"},"spacing":{"margin":{"bottom":"16px"}}}} -->
<figure class="wp-block-image aligncenter is-resized" style="margin-bottom:16px"><img src="http://soccer.local:32300/wp-content/uploads/2026/03/sfc-logo.jpg" alt="Sunshine Soccer Club" width="180" height="180" style="border-radius:50%"/></figure>
<!-- /wp:image -->
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(30px,5vw,60px)","fontWeight":"700"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:clamp(30px,5vw,60px);font-weight:700">Sunshine Soccer Club</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"20px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:20px">Local Sunday League Football &#x2022; Est. 2018 &#x2022; Playing with passion every week</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#f5a623","text":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://soccer.local:32300/fixtures/" style="border-radius:6px;background-color:#f5a623;color:#1e3a8a;font-weight:700">View Fixtures</a></div>
<!-- /wp:button -->
<!-- wp:button {"style":{"border":{"radius":"6px","color":"#ffffff","width":"2px"},"color":{"text":"#ffffff","background":"transparent"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://soccer.local:32300/team/" style="border-radius:6px;border-color:#ffffff;border-width:2px;color:#ffffff;background-color:transparent">Meet the Team</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div></div>
<!-- /wp:cover -->

<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"48px","bottom":"48px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:48px;padding-bottom:48px">
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}},"border":{"right":{"color":"#e0e0e0","width":"1px"}}}} -->
<div class="wp-block-column" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px;border-right-color:#e0e0e0;border-right-width:1px;border-right-style:solid">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1e3a8a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a;font-size:48px;font-weight:700">7+</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Years of Play</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}},"border":{"right":{"color":"#e0e0e0","width":"1px"}}}} -->
<div class="wp-block-column" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px;border-right-color:#e0e0e0;border-right-width:1px;border-right-style:solid">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1e3a8a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a;font-size:48px;font-weight:700">20+</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Squad Members</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}}}} -->
<div class="wp-block-column" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1e3a8a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a;font-size:48px;font-weight:700">Sun</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Every Week, Rain or Shine</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}},"color":{"background":"#eef2ff"}}} -->
<div class="wp-block-group alignfull" style="background-color:#eef2ff;padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"style":{"color":{"text":"#1e3a8a"}}} -->
<h2 class="wp-block-heading" style="color:#1e3a8a">Who We Are</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} -->
<p style="line-height:1.8">Sunshine Soccer Club is a friendly, inclusive Sunday league football club based in the local area. We welcome players of all abilities — from seasoned veterans to complete beginners. Whether you are here for the love of the game, the post-match banter, or just to keep fit, there is a place for you at Sunshine SC.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} -->
<p style="line-height:1.8">We play weekly fixtures in the local Sunday league from September through to May, with friendly matches and tournaments throughout the summer. Our squad is a close-knit group of friends who share a passion for football and a good sense of humour.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"24px"}}}} -->
<div class="wp-block-buttons" style="margin-top:24px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/team/" style="border-radius:6px;background-color:#1e3a8a">Meet the Squad</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:table {"className":"is-style-stripes"} -->
<figure class="wp-block-table is-style-stripes"><table><tbody>
<tr><td><strong>League</strong></td><td>Local Sunday League, Division 2</td></tr>
<tr><td><strong>Kick-off</strong></td><td>10:00 AM every Sunday</td></tr>
<tr><td><strong>Home ground</strong></td><td>Riverside Park, Pitch 3</td></tr>
<tr><td><strong>Kit colours</strong></td><td>Navy blue and gold</td></tr>
<tr><td><strong>Founded</strong></td><td>2018</td></tr>
</tbody></table></figure>
<!-- /wp:table -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}},"color":{"background":"#1e3a8a"}}} -->
<div class="wp-block-group alignfull" style="background-color:#1e3a8a;padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center","textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color">Next Match</h2>
<!-- /wp:heading -->
<!-- wp:separator {"customColor":"#f5a623","className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#f5a623;color:#f5a623"/>
<!-- /wp:separator -->
<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"24px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:24px">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"textAlign":"center","level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color">Sunshine SC</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:14px">Home</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"color":{"text":"#f5a623"},"typography":{"fontSize":"40px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#f5a623;font-size:40px;font-weight:700">VS</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:13px">Sunday &#x2022; 10:00 AM &#x2022; Riverside Park, Pitch 3</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"textAlign":"center","level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color">Opponents TBA</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:14px">Away</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#f5a623","text":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/fixtures/" style="border-radius:6px;background-color:#f5a623;color:#1e3a8a;font-weight:700">Full Fixtures List</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}}}} -->
<div class="wp-block-group alignfull" style="padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#1e3a8a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a">Want to Play?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.8"}}} -->
<p class="has-text-align-center" style="font-size:18px;line-height:1.8">We are always looking for new players to join the squad. No matter your position or skill level, get in touch and come along for a trial. The first session is always free.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/contact/" style="border-radius:6px;background-color:#1e3a8a">Get In Touch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
HTML;

$result = wp_update_post(['ID' => 5, 'post_content' => $home_content, 'post_status' => 'publish']);
echo $result ? "Home page updated (ID: $result)\n" : "Error updating home page\n";

// ── 6. Update Team page ───────────────────────────────────────────────────
wp_update_post([
    'ID' => 6,
    'post_content' => '<!-- wp:heading {"style":{"color":{"text":"#1e3a8a"}}} --><h2 class="wp-block-heading" style="color:#1e3a8a">The Squad</h2><!-- /wp:heading --><!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} --><p style="line-height:1.8">Meet the players who make Sunshine Soccer Club what it is. Each player has their own profile page where you can find out more about them, their position, and their stats. Players can log in and update their own profiles at any time.</p><!-- /wp:paragraph --><!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} --><p style="line-height:1.8">To add players to this page, go to <strong>wp-admin &rarr; Users &rarr; Add New</strong>, create an account for each player, and then link their name here to their BuddyPress profile page.</p><!-- /wp:paragraph --><!-- wp:buttons --><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1e3a8a"}}} --><div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/members/" style="border-radius:6px;background-color:#1e3a8a">Player Profiles</a></div><!-- /wp:button --><!-- /wp:buttons -->',
    'post_status' => 'publish',
]);

// ── 7. Clean up temp file ─────────────────────────────────────────────────
@unlink('/var/www/html/sfc-logo.jpg');

echo "All done. Sunshine Soccer Club branding applied.\n";
