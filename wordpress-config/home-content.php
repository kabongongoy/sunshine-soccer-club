<?php
// Run with: kubectl exec -n soccer <pod> -- php /tmp/home-content.php

require('/var/www/html/wp-load.php');

$home_content = <<<'HTML'
<!-- wp:cover {"customOverlayColor":"#1a6b3a","dimRatio":70,"minHeight":520,"minHeightUnit":"px","align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:80px;padding-bottom:80px;min-height:520px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-70 has-background-dim" style="background-color:#1a6b3a"></span><div class="wp-block-cover__inner-container">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(36px,6vw,72px)","fontWeight":"700"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:clamp(36px,6vw,72px);font-weight:700">&#x26BD; Sunday FC</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"20px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:20px">Local Sunday League Football &#x2022; Est. 2018 &#x2022; Playing with passion every week</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#ffffff","text":"#1a6b3a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://soccer.local:32300/fixtures/" style="border-radius:6px;background-color:#ffffff;color:#1a6b3a">View Fixtures</a></div>
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
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1a6b3a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1a6b3a;font-size:48px;font-weight:700">7+</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Years of Play</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}},"border":{"right":{"color":"#e0e0e0","width":"1px"}}}} -->
<div class="wp-block-column" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px;border-right-color:#e0e0e0;border-right-width:1px;border-right-style:solid">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1a6b3a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1a6b3a;font-size:48px;font-weight:700">20+</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Squad Members</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column {"style":{"spacing":{"padding":{"top":"32px","right":"32px","bottom":"32px","left":"32px"}}}} -->
<div class="wp-block-column" style="padding-top:32px;padding-right:32px;padding-bottom:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"48px","fontWeight":"700"},"color":{"text":"#1a6b3a"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1a6b3a;font-size:48px;font-weight:700">Sun</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontWeight":"600"}}} -->
<p class="has-text-align-center" style="font-weight:600">Every Week, Rain or Shine</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}},"color":{"background":"#f0f7f2"}}} -->
<div class="wp-block-group alignfull" style="background-color:#f0f7f2;padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"style":{"color":{"text":"#1a6b3a"}}} -->
<h2 class="wp-block-heading" style="color:#1a6b3a">Who We Are</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} -->
<p style="line-height:1.8">Sunday FC is a friendly, inclusive Sunday league football club based in the local area. We welcome players of all abilities — from seasoned veterans to complete beginners. Whether you are here for the love of the game, the post-match banter, or just to keep fit, there is a place for you at Sunday FC.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} -->
<p style="line-height:1.8">We play weekly fixtures in the local Sunday league from September through to May, with friendly matches and tournaments throughout the summer. Our squad is a close-knit group of friends who share a passion for football and a good sense of humour.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"24px"}}}} -->
<div class="wp-block-buttons" style="margin-top:24px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1a6b3a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/team/" style="border-radius:6px;background-color:#1a6b3a">Meet the Squad</a></div>
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
<tr><td><strong>Kit colours</strong></td><td>Green and white</td></tr>
<tr><td><strong>Founded</strong></td><td>2018</td></tr>
</tbody></table></figure>
<!-- /wp:table -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}},"color":{"background":"#00102E"}}} -->
<div class="wp-block-group alignfull" style="background-color:#00102E;padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center","textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color">Next Match</h2>
<!-- /wp:heading -->
<!-- wp:separator {"customColor":"#1a6b3a","className":"is-style-wide"} -->
<hr class="wp-block-separator has-text-color has-alpha-channel-opacity has-background is-style-wide" style="background-color:#1a6b3a;color:#1a6b3a"/>
<!-- /wp:separator -->
<!-- wp:columns {"align":"wide","style":{"spacing":{"padding":{"top":"24px"}}}} -->
<div class="wp-block-columns alignwide" style="padding-top:24px">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"textAlign":"center","level":3,"textColor":"white"} -->
<h3 class="wp-block-heading has-text-align-center has-white-color has-text-color">Sunday FC</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:14px">Home</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"textAlign":"center","level":2,"style":{"color":{"text":"#4ade80"},"typography":{"fontSize":"40px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#4ade80;font-size:40px;font-weight:700">VS</h2>
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
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1a6b3a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/fixtures/" style="border-radius:6px;background-color:#1a6b3a">Full Fixtures List</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px","right":"32px","left":"32px"}}}} -->
<div class="wp-block-group alignfull" style="padding-top:64px;padding-bottom:64px;padding-right:32px;padding-left:32px">
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Want to Play?</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px","lineHeight":"1.8"}}} -->
<p class="has-text-align-center" style="font-size:18px;line-height:1.8">We are always looking for new players to join the squad. No matter your position or skill level, get in touch and come along for a trial. The first session is always free.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px"><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1a6b3a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/contact/" style="border-radius:6px;background-color:#1a6b3a">Get In Touch</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
HTML;

// Update the Home page (ID 5)
$result = wp_update_post([
    'ID'           => 5,
    'post_content' => $home_content,
    'post_status'  => 'publish',
]);

echo $result ? "Home page updated successfully (ID: $result)\n" : "Error updating home page\n";

// Update Team page
wp_update_post([
    'ID' => 6,
    'post_content' => '<!-- wp:heading {"style":{"color":{"text":"#1a6b3a"}}} --><h2 class="wp-block-heading" style="color:#1a6b3a">The Squad</h2><!-- /wp:heading --><!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} --><p style="line-height:1.8">Meet the players who make Sunday FC what it is. Each player has their own profile page where you can find out more about them, their position, and their stats. Players can log in and update their own profiles at any time.</p><!-- /wp:paragraph --><!-- wp:paragraph {"style":{"typography":{"lineHeight":"1.8"}}} --><p style="line-height:1.8">To add players to this page, go to <strong>wp-admin &rarr; Users &rarr; Add New</strong>, create an account for each player, and then link their name here to their BuddyPress profile page.</p><!-- /wp:paragraph --><!-- wp:buttons --><!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1a6b3a"}}} --><div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" style="border-radius:6px;background-color:#1a6b3a">Player Profiles</a></div><!-- /wp:button --><!-- /wp:buttons -->',
    'post_status'  => 'publish',
]);

// Update Fixtures page
wp_update_post([
    'ID' => 7,
    'post_content' => '<!-- wp:heading {"style":{"color":{"text":"#1a6b3a"}}} --><h2 class="wp-block-heading" style="color:#1a6b3a">Upcoming Fixtures</h2><!-- /wp:heading --><!-- wp:paragraph --><p>All upcoming matches, training sessions, and events are listed below. Kick-off is at <strong>10:00 AM</strong> every Sunday at Riverside Park, Pitch 3, unless otherwise noted.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>To add new fixtures, go to <strong>wp-admin &rarr; Events &rarr; Add New</strong> using The Events Calendar plugin.</p><!-- /wp:paragraph -->',
    'post_status'  => 'publish',
]);

// Update Results page
wp_update_post([
    'ID' => 8,
    'post_content' => '<!-- wp:heading {"style":{"color":{"text":"#1a6b3a"}}} --><h2 class="wp-block-heading" style="color:#1a6b3a">Results &amp; League Table</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Match results, league standings, and player statistics are managed through the SportsPress plugin. To add a result, go to <strong>wp-admin &rarr; SP &rarr; Events &rarr; Add New</strong> and enter the match details.</p><!-- /wp:paragraph -->',
    'post_status'  => 'publish',
]);

// Update Contact page
wp_update_post([
    'ID' => 10,
    'post_content' => '<!-- wp:heading {"style":{"color":{"text":"#1a6b3a"}}} --><h2 class="wp-block-heading" style="color:#1a6b3a">Get In Touch</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Interested in joining the squad, sponsoring the club, or just want to know more? Drop us a message using the form below and we will get back to you as soon as possible.</p><!-- /wp:paragraph --><!-- wp:shortcode -->[contact-form-7 id="1" title="Contact form 1"]<!-- /wp:shortcode --><!-- wp:separator {"className":"is-style-wide"} --><hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/><!-- /wp:separator --><!-- wp:columns --><!-- wp:column --><!-- wp:heading {"level":4} --><h4 class="wp-block-heading">Training &amp; Match Day</h4><!-- /wp:heading --><!-- wp:paragraph --><p>&#x1F4CD; Riverside Park, Pitch 3<br>&#x23F0; 10:00 AM every Sunday</p><!-- /wp:paragraph --><!-- /wp:column --><!-- wp:column --><!-- wp:heading {"level":4} --><h4 class="wp-block-heading">New Players</h4><!-- /wp:heading --><!-- wp:paragraph --><p>All abilities welcome. First session is free. Just turn up or send us a message first.</p><!-- /wp:paragraph --><!-- /wp:column --><!-- /wp:columns -->',
    'post_status'  => 'publish',
]);

echo "All pages updated.\n";
