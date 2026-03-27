<?php
require('/var/www/html/wp-load.php');

$content = '
<!-- wp:cover {"customOverlayColor":"#1e3a8a","dimRatio":80,"minHeight":480,"minHeightUnit":"px","align":"full","style":{"spacing":{"padding":{"top":"72px","bottom":"72px"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:72px;padding-bottom:72px;min-height:480px">
<span aria-hidden="true" class="wp-block-cover__background has-background-dim-80 has-background-dim" style="background-color:#1e3a8a"></span>
<div class="wp-block-cover__inner-container">

<!-- wp:image {"align":"center","width":130,"height":130,"sizeSlug":"full","style":{"border":{"radius":"50%"},"spacing":{"margin":{"bottom":"20px"}}}} -->
<figure class="wp-block-image aligncenter is-resized" style="margin-bottom:20px">
<img src="http://soccer.local:32300/wp-content/uploads/2026/03/sfc-logo.jpg" alt="Sunshine Soccer Club" width="130" height="130" style="border-radius:50%" />
</figure>
<!-- /wp:image -->

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(28px,5vw,56px)","fontWeight":"800"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-center has-white-color has-text-color" style="font-size:clamp(28px,5vw,56px);font-weight:800">Sunshine Soccer Club</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"18px"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:18px">Sunday League Football &bull; Est. 2018 &bull; Everyone Welcome</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"28px"}}}} -->
<div class="wp-block-buttons" style="margin-top:28px">
<!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#f5a623","text":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://soccer.local:32300/fixtures/" style="border-radius:6px;background-color:#f5a623;color:#1e3a8a;font-weight:700">View Fixtures</a></div>
<!-- /wp:button -->
<!-- wp:button {"style":{"border":{"radius":"6px","color":"#ffffff","width":"2px"},"color":{"text":"#ffffff","background":"transparent"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="http://soccer.local:32300/team/" style="border-radius:6px;border-color:#ffffff;border-width:2px;color:#ffffff;background-color:transparent">Our Team</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
</div>
<!-- /wp:cover -->

<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"56px","bottom":"48px","right":"24px","left":"24px"}}}} -->
<div class="wp-block-group alignwide" style="padding-top:56px;padding-bottom:48px;padding-right:24px;padding-left:24px">

<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#1e3a8a"},"typography":{"fontSize":"32px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a;font-size:32px;font-weight:700">Who We Are</h2>
<!-- /wp:heading -->

<!-- wp:separator {"style":{"color":{"background":"#f5a623"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-alpha-channel-opacity has-background is-style-wide" style="background-color:#f5a623;border-color:#f5a623"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"17px","lineHeight":"1.9"},"color":{"text":"#444444"}}} -->
<p class="has-text-align-center" style="font-size:17px;line-height:1.9;color:#444">Sunshine Soccer Club is a friendly, inclusive Sunday league club. We welcome players of all abilities — from seasoned veterans to complete beginners. We play weekly from September to May and run summer friendlies throughout the year. Whether you are here for the game, the fitness, or the post-match banter, there is a place for you here.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"48px","bottom":"64px","right":"24px","left":"24px"}},"color":{"background":"#f0f4ff"}}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#f0f4ff;padding-top:48px;padding-bottom:64px;padding-right:24px;padding-left:24px">

<!-- wp:heading {"textAlign":"center","style":{"color":{"text":"#1e3a8a"},"typography":{"fontSize":"32px","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="color:#1e3a8a;font-size:32px;font-weight:700">Meet the Team</h2>
<!-- /wp:heading -->

<!-- wp:separator {"style":{"color":{"background":"#f5a623"}},"className":"is-style-wide"} -->
<hr class="wp-block-separator has-alpha-channel-opacity has-background is-style-wide" style="background-color:#f5a623;border-color:#f5a623"/>
<!-- /wp:separator -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#666666"},"typography":{"fontSize":"15px"}}} -->
<p class="has-text-align-center" style="color:#666;font-size:15px">Click a player to view their profile and stats.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[squad]
<!-- /wp:shortcode -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"32px"}}}} -->
<div class="wp-block-buttons" style="margin-top:32px">
<!-- wp:button {"style":{"border":{"radius":"6px"},"color":{"background":"#1e3a8a"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-background wp-element-button" href="http://soccer.local:32300/team/" style="border-radius:6px;background-color:#1e3a8a">Full Squad Page</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
';

$home = get_page_by_path('home');
wp_update_post([
    'ID'           => $home->ID,
    'post_content' => trim($content),
]);

echo "Home page updated (ID {$home->ID})\n";
