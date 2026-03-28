#!/bin/bash
# Runs inside the WordPress pod via scripts/run-wpcli.sh
# Downloads WP-CLI if not present, then installs plugins and configures the site.
set -e

# ── Download WP-CLI if not already available ─────────────────────────────────
if ! command -v wp &>/dev/null; then
  echo "==> Downloading WP-CLI..."
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp
fi

WP="wp --allow-root"
SITE_URL="http://soccer.local:32300"
ADMIN_USER="admin"
ADMIN_PASS="changeme123"    # CHANGE THIS before running
ADMIN_EMAIL="admin@soccerteam.local"
SITE_TITLE="Sunday Soccer Team"

# ── Install WordPress core ────────────────────────────────────────────────────
echo "==> Installing WordPress core..."
$WP core install \
  --url="$SITE_URL" \
  --title="$SITE_TITLE" \
  --admin_user="$ADMIN_USER" \
  --admin_password="$ADMIN_PASS" \
  --admin_email="$ADMIN_EMAIL" \
  --skip-email

# ── Plugins ───────────────────────────────────────────────────────────────────
echo "==> Installing and activating plugins..."
while IFS= read -r plugin; do
  [[ "$plugin" =~ ^#.*$ || -z "$plugin" ]] && continue
  echo "  Installing: $plugin"
  $WP plugin install "$plugin" --activate
done < /plugins.txt

# ── Theme ─────────────────────────────────────────────────────────────────────
echo "==> Installing Sydney theme..."
$WP theme install sydney --activate

# ── Pages ─────────────────────────────────────────────────────────────────────
echo "==> Creating key pages..."
$WP post create --post_type=page --post_title='Home'             --post_status=publish
$WP post create --post_type=page --post_title='Team'             --post_status=publish
$WP post create --post_type=page --post_title='Fixtures'         --post_status=publish
$WP post create --post_type=page --post_title='Results'          --post_status=publish
$WP post create --post_type=page --post_title='Gallery'          --post_status=publish
$WP post create --post_type=page --post_title='Contact'          --post_status=publish

# ── Code of Conduct ───────────────────────────────────────────────────────────
echo "==> Creating Code of Conduct page..."
$WP post create \
  --post_type=page \
  --post_title='Code of Conduct' \
  --post_name='code-of-conduct' \
  --post_status=publish \
  --post_content='<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><strong>The Club'\''s Code of Conduct applies to all of our members, officials and supporters.</strong></p>
<!-- /wp:paragraph -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>Play by the rules – the rules of Sunshine Soccer Club and the laws of the game.</li>
<li>Players should respect the methods employed by the club to ensure proper implement of the laws of the game or the rules of the competition.</li>
<li>We expect the highest level of behaviour both on and off the field during training and/match days.</li>
<li>Enjoyment of the game is more important than winning. Play improves your skills.</li>
<li>Be a team player and treat all players as you would like to be treated – fairly.</li>
<li>Do not contradict the coaches or team management instructions.</li>
<li>No argument with referee. Referee'\''s decision is final. Any confrontation with the referee could attract penalty such as yellow or red cards or as defined as below.</li>
<li>Control your temper – verbal abuse of officials and sledging other players, team-mates and spectators does not help you enjoy or win any games.</li>
<li>Lead by example and respect all players, team managers, coaches, referees and spectators.</li>
<li>Physical or verbal abuse will not be tolerated. <strong>Physical Abuse/Altercation if determined by the team management is immediate expulsion from the Club.</strong></li>
<li>Caution of rough play during training or soccer matches is at the discretion of the referee.</li>
<li>Never ridicule mistakes or losses, including those of your own team, opposition players and supporters.</li>
<li>Applaud good play by both your team and by members of the opposing team.</li>
<li>Support your club management, offer your assistance to the team so that every opportunity is being provided for the best supervision of the club.</li>
<li>Do not post marketing, political or religious content on our social media and chat platforms. Do not use ugly remarks based on race, religion, gender or ability.</li>
<li>All Players are expected to be and remain on the field of play except in circumstances such as injuries or substitutions. Leaving the field during the course of play without informing the referee or team manager is a cautionable action or could result to penalties stated below.</li>
<li>Selection for soccer matches is based on minimum 2 consecutive attendance at training and payment of all match fees.</li>
<li>If a player has an issue within the club that needs to be resolved, they need to follow the provided procedures. Issues with playing time, scheduling conflicts, or any other matters will be dealt with by the team management.</li>
<li>The club reserve the rights to suspend or remove any member who breaches the code of conduct from the club and/or social media platforms.</li>
</ol>
<!-- /wp:list -->

<!-- wp:separator -->
<hr class="wp-block-separator"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3} -->
<h3>Penalty for Breaches</h3>
<!-- /wp:heading -->

<!-- wp:heading {"level":4} -->
<h4>Soccer Related</h4>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
<li><strong>First offence</strong> – Warning from Coaches/Team Management.</li>
<li><strong>Repeat offence</strong> – Sent off the field for time out e.g. Including minimum of 10 mins or entire half duration.</li>
<li><strong>Serious breach</strong> – Expelled from that day'\''s training or soccer match. The club management committee will then consider appropriate disciplinary action including longer suspensions and expulsion from the club.</li>
<li><strong>Physical Abuse/Altercation</strong> – Immediate expulsion from the Club.</li>
<li>2 yellow cards constitute 1 suspension.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":4} -->
<h4>Social Media / Chat Platform Related</h4>
<!-- /wp:heading -->
<!-- wp:list -->
<ul>
<li><strong>First offence</strong> – Warning from Administrators/Team Management.</li>
<li><strong>Second offence</strong> – Suspension from social media / chat platforms for 2 weeks.</li>
<li><strong>Repeat offence</strong> – Permanent removal of account from social media / chat platforms.</li>
</ul>
<!-- /wp:list -->

<!-- wp:separator -->
<hr class="wp-block-separator"/>
<!-- /wp:separator -->

<!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">Acceptance of Code of Conduct Policy</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Your participation in the Sunshine Soccer Club Social Media Platforms and/or at any Sunshine Soccer Club training or match games indicates your agreement to this code of conduct. If you do not agree or would like to opt out, email <a href="mailto:sunshinesoccerclub@gmail.com">sunshinesoccerclub@gmail.com</a> or discuss with any of the SSC Management members.</p>
<!-- /wp:paragraph -->'

# ── Front-end Edit Profile page (used by sunshine-profile-edit.php mu-plugin) ─
echo "==> Creating Edit Profile page..."
$WP post create \
  --post_type=page \
  --post_title='Edit My Profile' \
  --post_name='edit-profile' \
  --post_status=publish \
  --post_content='<!-- wp:shortcode -->[player_profile_edit]<!-- /wp:shortcode -->'

echo "==> Setting front page..."
HOME_ID=$($WP post list --post_type=page --post_title='Home' --field=ID)
$WP option update show_on_front page
$WP option update page_on_front "$HOME_ID"

echo "==> Configuring permalink structure..."
$WP rewrite structure '/%postname%/'
$WP rewrite flush

echo "==> Enabling user registration (for player profiles)..."
$WP option update users_can_register 1
$WP option update default_role subscriber

echo ""
echo "==> Done! Visit $SITE_URL/wp-admin to finish setup."
echo "    Admin user: $ADMIN_USER / $ADMIN_PASS"
echo "    IMPORTANT: Change the admin password immediately at $SITE_URL/wp-admin/profile.php"
