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
$WP post create --post_type=page --post_title='Home'      --post_status=publish
$WP post create --post_type=page --post_title='Team'      --post_status=publish
$WP post create --post_type=page --post_title='Fixtures'  --post_status=publish
$WP post create --post_type=page --post_title='Results'   --post_status=publish
$WP post create --post_type=page --post_title='Gallery'   --post_status=publish
$WP post create --post_type=page --post_title='Contact'   --post_status=publish

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
