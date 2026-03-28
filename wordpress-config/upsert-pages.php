<?php
/**
 * Creates or updates the Code of Conduct and Edit Profile pages.
 * Run via: wp eval-file /tmp/upsert-pages.php --allow-root
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

$pages = [
    'code-of-conduct' => [
        'title'        => 'Code of Conduct',
        'content_file' => '/tmp/coc-content.html',
    ],
    'edit-profile' => [
        'title'        => 'Edit My Profile',
        'content_file' => '/tmp/edit-profile-content.html',
    ],
];

foreach ( $pages as $slug => $data ) {
    $content = file_get_contents( $data['content_file'] );
    if ( $content === false ) {
        WP_CLI::error( "Could not read {$data['content_file']}" );
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
        WP_CLI::success( "Updated page: {$data['title']} (ID {$existing->ID})" );
    } else {
        $id = wp_insert_post( [
            'post_type'    => 'page',
            'post_title'   => $data['title'],
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
        ] );
        WP_CLI::success( "Created page: {$data['title']} (ID {$id})" );
    }
}
