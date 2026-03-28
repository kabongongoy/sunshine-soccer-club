<?php
/**
 * Creates or updates pages and ensures Code of Conduct is in the nav menu.
 * Run via: wp eval-file /tmp/upsert-pages.php --skip-plugins=wordpress-seo
 *
 * Page content is read from separate .html files (also in /tmp/) to avoid
 * PHP string-escaping issues with large multi-line HTML content.
 *
 * Files expected in /tmp/:
 *   coc-content.html          — Code of Conduct full content
 *   edit-profile-content.html — [player_profile_edit] shortcode
 *
 * Safe to run multiple times — uses post slug to detect existing pages.
 */

// Pages to create/update. 'in_menu' => true adds to the primary nav menu.
$pages = [
    'code-of-conduct' => [
        'title'        => 'Code of Conduct',
        'content_file' => '/tmp/coc-content.html',
        'in_menu'      => true,
    ],
    'edit-profile' => [
        'title'        => 'Edit My Profile',
        'content_file' => '/tmp/edit-profile-content.html',
        'in_menu'      => false,   // players reach this via login redirect / edit button
    ],
];

// Find the primary nav menu
$menu_locations = get_nav_menu_locations();
$menu_id        = $menu_locations['primary'] ?? 0;
if ( ! $menu_id ) {
    // Fallback: grab the first menu by name
    $menus   = wp_get_nav_menus();
    $menu_id = ! empty( $menus ) ? $menus[0]->term_id : 0;
}

foreach ( $pages as $slug => $data ) {
    $content = file_get_contents( $data['content_file'] );
    if ( $content === false ) {
        WP_CLI::warning( "Could not read {$data['content_file']} — skipping {$slug}" );
        continue;
    }
    $content  = trim( $content );
    $existing = get_page_by_path( $slug );

    if ( $existing ) {
        wp_update_post( [
            'ID'           => $existing->ID,
            'post_title'   => $data['title'],
            'post_content' => $content,
            'post_status'  => 'publish',
        ] );
        $page_id = $existing->ID;
        WP_CLI::success( "Updated page: {$data['title']} (ID {$page_id})" );
    } else {
        $page_id = wp_insert_post( [
            'post_type'    => 'page',
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
        ] );
        WP_CLI::success( "Created page: {$data['title']} (ID {$page_id})" );
    }

    // Add to primary menu if requested and not already there
    if ( $data['in_menu'] && $menu_id ) {
        $menu_items = wp_get_nav_menu_items( $menu_id );
        $already_in = false;
        foreach ( (array) $menu_items as $item ) {
            if ( (int) $item->object_id === (int) $page_id ) {
                $already_in = true;
                break;
            }
        }
        if ( ! $already_in ) {
            wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-object-id' => $page_id,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-title'     => $data['title'],
            ] );
            WP_CLI::success( "Added '{$data['title']}' to nav menu (menu ID {$menu_id})" );
        } else {
            WP_CLI::success( "'{$data['title']}' already in nav menu — no change" );
        }
    }
}
