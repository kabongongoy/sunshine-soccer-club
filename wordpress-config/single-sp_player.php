<?php
/**
 * Template for single SportsPress player profiles.
 * Drop into the Sydney theme as single-sp_player.php
 */
get_header(); ?>

<style>
/* ── Player profile layout ── */
.ssc-player-profile { max-width: 860px; margin: 0 auto; padding: 0 20px 60px; }

/* Hero card */
.ssc-profile-hero {
    display: flex;
    align-items: center;
    gap: 36px;
    background: linear-gradient(135deg, #1e3a8a 0%, #162d6e 100%);
    border-radius: 16px;
    padding: 40px;
    margin: 32px 0 36px;
    color: #fff;
    flex-wrap: wrap;
}
.ssc-profile-photo-wrap { position: relative; flex-shrink: 0; }
.ssc-profile-photo,
.ssc-profile-initials {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 4px solid #f5a623;
    display: block;
    object-fit: cover;
}
.ssc-profile-initials {
    background: rgba(255,255,255,0.12);
    font-size: 52px;
    font-weight: 800;
    color: #f5a623;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ssc-jersey-badge {
    position: absolute;
    bottom: 0; right: 0;
    background: #f5a623;
    color: #1e3a8a;
    font-size: 13px;
    font-weight: 800;
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
}
.ssc-profile-meta { flex: 1; min-width: 200px; }
.ssc-profile-name {
    font-size: clamp(26px, 4vw, 40px);
    font-weight: 800;
    margin: 0 0 8px;
    color: #fff;
    line-height: 1.1;
}
.ssc-profile-position {
    font-size: 15px;
    color: #f5a623;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 12px;
}
.ssc-profile-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.ssc-profile-tag {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    font-size: 12px;
    padding: 4px 12px;
    border-radius: 20px;
}

/* Bio */
.ssc-profile-body { font-size: 16px; line-height: 1.85; color: #333; }
.ssc-profile-body p:first-child { font-size: 17px; }

/* SportsPress details overrides */
.ssc-player-profile .sp-template { margin-top: 28px; }
.ssc-player-profile .sp-player-details { display: flex; flex-wrap: wrap; gap: 0; margin: 0; padding: 0; border: 1px solid #e8e8e8; border-radius: 10px; overflow: hidden; }
.ssc-player-profile .sp-player-details dt,
.ssc-player-profile .sp-player-details dd {
    padding: 12px 16px;
    margin: 0;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.ssc-player-profile .sp-player-details dt { color: #888; font-weight: 600; width: 140px; background: #fafafa; }
.ssc-player-profile .sp-player-details dd { color: #222; flex: 1; }

/* Back link */
.ssc-back-link { display: inline-flex; align-items: center; gap: 6px; color: #1e3a8a; font-size: 14px; font-weight: 600; text-decoration: none; margin-bottom: 8px; }
.ssc-back-link:hover { color: #f5a623; }
</style>

<div class="ssc-player-profile">
    <?php while (have_posts()) : the_post();
        $player_id = get_the_ID();
        $number    = get_post_meta($player_id, 'sp_number', true);
        $positions = get_the_terms($player_id, 'sp_position');
        $position  = ($positions && !is_wp_error($positions)) ? $positions[0]->name : '';
        $photo_url = get_the_post_thumbnail_url($player_id, 'medium');
        $initials  = mb_strtoupper(mb_substr(get_the_title(), 0, 1));
    ?>

    <a href="<?php echo esc_url(get_post_type_archive_link('sp_player') ?: home_url('/team/')); ?>" class="ssc-back-link">
        ← Back to Team
    </a>

    <!-- Hero card -->
    <div class="ssc-profile-hero">
        <div class="ssc-profile-photo-wrap">
            <?php if ($photo_url): ?>
                <img src="<?php echo esc_url($photo_url); ?>" alt="<?php the_title_attribute(); ?>" class="ssc-profile-photo" />
            <?php else: ?>
                <div class="ssc-profile-initials"><?php echo esc_html($initials); ?></div>
            <?php endif; ?>
            <?php if ($number): ?>
                <div class="ssc-jersey-badge"><?php echo esc_html($number); ?></div>
            <?php endif; ?>
        </div>

        <div class="ssc-profile-meta">
            <h1 class="ssc-profile-name"><?php the_title(); ?></h1>
            <?php if ($position): ?>
                <div class="ssc-profile-position"><?php echo esc_html($position); ?></div>
            <?php endif; ?>
            <div class="ssc-profile-tags">
                <span class="ssc-profile-tag">Sunshine Soccer Club</span>
                <?php if ($number): ?>
                    <span class="ssc-profile-tag">Jersey #<?php echo esc_html($number); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bio + SP stats + social links (all via the_content / hooks) -->
    <div class="ssc-profile-body">
        <?php the_content(); ?>
    </div>

    <?php endwhile; ?>
</div>

<?php get_footer();
