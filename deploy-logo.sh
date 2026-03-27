#!/bin/bash
# Uploads SSC-logo.png to the Lightsail instance and sets it as the WordPress site logo.

set -e

KEY="c:/Users/PC/OneDrive/Documents/claude/first-project/soccer-team-site/ssc-key.pem"
HOST="admin@52.63.255.72"
LOGO="c:/Users/PC/OneDrive/Documents/claude/first-project/soccer-team-site/SSC-logo.png"
REMOTE_TMP="/tmp/SSC-logo.png"

echo "==> Uploading logo..."
scp -i "$KEY" -o StrictHostKeyChecking=no "$LOGO" "$HOST:$REMOTE_TMP"

echo "==> Setting logo in WordPress..."
ssh -i "$KEY" -o StrictHostKeyChecking=no "$HOST" bash << 'EOF'
set -e
WP="wp --allow-root --path=/var/www/html"

# Copy logo into WordPress uploads
DEST="/var/www/html/wp-content/uploads/SSC-logo.png"
sudo cp /tmp/SSC-logo.png "$DEST"
sudo chown www-data:www-data "$DEST"

# Import into WordPress media library
ATTACH_ID=$(sudo -u www-data $WP media import "$DEST" --title="SSC Logo" --porcelain)

echo "Attachment ID: $ATTACH_ID"

# Set as site logo (custom_logo theme mod)
sudo -u www-data $WP theme mod set custom_logo "$ATTACH_ID"

echo "==> Logo updated. Attachment ID: $ATTACH_ID"
EOF

echo "==> Done! Visit your site to confirm the logo."
