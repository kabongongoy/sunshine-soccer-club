# WordPress Club Site on Kubernetes — Deployment Playbook

A repeatable guide for deploying a WordPress-based sports club website on a bare-metal or local
Kubernetes cluster, based on building the Sunshine Soccer Club site.

---

## Prerequisites

```bash
kubectl cluster-info          # cluster must be reachable
docker info                   # Docker running
helm version                  # Helm 3+
kubectl get storageclass      # at least one storage class must exist
```

**Tools needed on the machine running kubectl:**
- `kubectl`, `helm`, `python` (3.x), `base64`

---

## Cluster Realities to Check First

Before deploying, gather these facts — they affect every manifest:

```bash
# 1. What storage class is available?
kubectl get storageclass
# → Use that name in all PVC specs (e.g. nfs-readynas, standard, hostpath)

# 2. Is there already an ingress controller?
kubectl get ingressclass
kubectl get svc -n ingress-nginx

# 3. What type is the ingress controller service? (LoadBalancer vs NodePort)
kubectl get svc ingress-nginx-controller -n ingress-nginx
# → NodePort: note the HTTP port (e.g. 32300) — include it in every URL
# → LoadBalancer: maps to port 80, no port suffix needed

# 4. Which node is the ingress controller pod running on?
kubectl get pods -n ingress-nginx -o wide
# → Use THAT node's IP in /etc/hosts, not the control plane IP
#   (control plane may firewall NodePort traffic)

# 5. Any namespace conflicts?
kubectl get ns
kubectl get ingress -A   # check for hostname clashes before deploying
```

---

## Storage Class Adjustments

If the cluster uses **NFS** storage (e.g. `nfs-readynas`), change PVC access mode:

```yaml
# Default spec (Docker Desktop / hostpath):
accessModes: [ ReadWriteOnce ]

# NFS clusters — must be:
accessModes: [ ReadWriteMany ]
storageClassName: nfs-readynas   # replace with actual class name
```

---

## Deployment Steps

### 1. Create all manifests

Follow the file structure in `soccer-team-site/`:
- `kubernetes/namespace.yaml` — isolates everything in one namespace
- `kubernetes/secrets.yaml` — base64-encoded DB passwords
- `kubernetes/mysql/` — PVC, Deployment, ClusterIP Service
- `kubernetes/wordpress/` — PVC, ConfigMap, Deployment, ClusterIP Service
- `kubernetes/ingress/ingress.yaml` — routes hostname → WordPress service

### 2. Deploy

```bash
make deploy
# or manually:
kubectl apply -f kubernetes/namespace.yaml
kubectl apply -f kubernetes/secrets.yaml
kubectl apply -f kubernetes/mysql/
kubectl rollout status deployment/mysql -n soccer --timeout=120s
kubectl apply -f kubernetes/wordpress/
kubectl rollout status deployment/wordpress -n soccer --timeout=120s
kubectl apply -f kubernetes/ingress/ingress.yaml
```

### 3. Hosts file entry

```
# Windows: C:\Windows\System32\drivers\etc\hosts  (run editor as Administrator)
# Linux/macOS: /etc/hosts

<ingress-node-ip>   soccer.local

# IMPORTANT: use the node the ingress controller pod is running on, not the control plane
# Find it: kubectl get pods -n ingress-nginx -o wide
```

### 4. Access URL

| Controller type | URL |
|---|---|
| LoadBalancer | `http://soccer.local` |
| NodePort (HTTP on 32300) | `http://soccer.local:32300` |

---

## WordPress Setup via WP-CLI

The `wordpress:latest` image does **not** include WP-CLI. Download it first:

```bash
kubectl exec -n soccer <wordpress-pod> -- bash -c "
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp
"
```

Then run setup:
```bash
make wpcli
# which runs scripts/run-wpcli.sh → copies and executes wordpress-config/wp-setup.sh
```

### WP-CLI file transfer on Windows (Git Bash)

`kubectl cp` is broken on Windows/Git Bash because it misinterprets paths with colons (`C:`)
and slashes (`/tmp`). Use **Python stdin piping** instead:

```python
# transfer.py — reusable helper
import subprocess, base64, sys

POD  = 'wordpress-<hash>'
src  = sys.argv[1]
dest = sys.argv[2]   # pod-side path e.g. //var/www/html/script.php

with open(src, 'rb') as f:
    data = f.read()

subprocess.run(
    ['kubectl', 'exec', '-n', 'soccer', POD, '-i', '--', 'bash', '-c',
     f'base64 -d > {dest}'],
    input=base64.b64encode(data), check=True
)
```

```bash
python transfer.py local-file.php //var/www/html/local-file.php
```

**Key gotcha:** use `//var/www/html/` (double leading slash) to prevent Git Bash from
converting the path to a Windows path. Prefix all `kubectl exec` commands that reference
container paths with `MSYS_NO_PATHCONV=1`.

### Running PHP scripts in the pod

```bash
MSYS_NO_PATHCONV=1 kubectl exec -n soccer <pod> -- php //var/www/html/script.php
```

---

## Ingress Hostname Conflicts

If another namespace already has an ingress for the same hostname:

```bash
kubectl get ingress -A | grep soccer.local
# → Delete the old one before deploying:
kubectl delete ingress <name> -n <old-namespace>
```

---

## Sydney Theme — Key Customisation Points

Sydney uses its **own** logo theme mod, not WordPress core's `custom_logo`:

```bash
# WRONG — WordPress core (ignored by Sydney):
wp --allow-root theme mod set custom_logo <attachment-id>

# CORRECT — Sydney-specific (URL, not ID):
wp --allow-root theme mod set site_logo "http://soccer.local:32300/wp-content/uploads/2026/03/logo.jpg"
wp --allow-root theme mod set logo_height 120
wp --allow-root theme mod set sydney_use_height_for_logo 1
```

Hide the text site title when using a logo image:
```bash
wp --allow-root theme mod set display_header_text ""
# NOTE: must be empty string, NOT the string "false" — PHP treats "false" as truthy
```

Remove sidebar on all pages:
```bash
wp --allow-root theme mod set fullwidth_pages 1
# Per-page override:
wp --allow-root post meta update <page-id> _sydney_page_disable_sidebar 1
```

Hide the page title on the front page via custom CSS:
```bash
wp --allow-root eval '
wp_update_custom_css_post(
    ".home .title-post.entry-title { display: none !important; }
     .home .page-header { display: none !important; }
     .site-title, .site-description { display: none !important; }
     .site-logo { height: 120px !important; width: auto !important; }"
);
'
```

---

## Uploading Binary Files to a Pod (Windows)

For files larger than ~50 KB, the base64 string exceeds Windows `CreateProcess` limits.
Use Python stdin piping (see `transfer.py` above). For very large files:

```python
# Chunked approach if needed (>500KB):
chunk_size = 50000
chunks = [data[i:i+chunk_size] for i in range(0, len(data), chunk_size)]
# first chunk: > file; subsequent chunks: >> file
```

For images, use `wp media import` after placing the file in the container:

```bash
ATTACH_ID=$(wp --allow-root media import /var/www/html/logo.jpg \
  --title="Club Logo" --porcelain)
```

---

## BuddyPress Player Profiles

### How BuddyPress stores its pages

BuddyPress creates its own internal posts of type `buddypress` (not regular `page`) when it
activates. Their IDs are stored in the `bp-pages` WordPress option. **Do not create regular
pages for members/activity/register/activate** — BuddyPress already made them.

```bash
# Find the real BuddyPress internal pages
wp --allow-root eval "
global \$wpdb;
\$rows = \$wpdb->get_results(\"SELECT ID, post_name FROM \$wpdb->posts
  WHERE post_type = 'buddypress' AND post_status = 'publish'\");
foreach(\$rows as \$r) echo \$r->ID . '\t' . \$r->post_name . PHP_EOL;
"

# Fix bp-pages to point to those IDs (replace IDs with what you found above)
wp --allow-root option update bp-pages --format=json \
  '{"activity":27,"members":28,"register":29,"activate":30}'

wp --allow-root rewrite flush
```

If you accidentally created duplicate regular pages for these slugs, delete them:
```bash
wp --allow-root post delete <duplicate-id> --force
```

### Setting up custom profile fields

```bash
# Create a field group
GROUP_ID=$(wp --allow-root bp xprofile group create --name="Player Info" --porcelain)

# Add fields (use textbox type — selectbox --options is not supported in all versions)
wp --allow-root bp xprofile field create --field-group-id=$GROUP_ID \
  --name="Position" --type=textbox
wp --allow-root bp xprofile field create --field-group-id=$GROUP_ID \
  --name="Jersey Number" --type=textbox
wp --allow-root bp xprofile field create --field-group-id=$GROUP_ID \
  --name="Player Bio" --type=textarea
```

> **Note:** `--type=selectbox --options="..."` fails silently in BuddyPress WP-CLI 14.x.
> Use `textbox` for all fields and let players type their position.

### Setting profile data for a user

```bash
# List all xprofile fields first to get IDs
wp --allow-root bp xprofile field list --format=table

# Set values (field IDs vary per install)
wp --allow-root bp xprofile data set --user-id=<id> --field-id=1 --value="Full Name"
wp --allow-root bp xprofile data set --user-id=<id> --field-id=2 --value="Jersey number"
wp --allow-root bp xprofile data set --user-id=<id> --field-id=3 --value="Bio text..."
wp --allow-root bp xprofile data set --user-id=<id> --field-id=4 --value="Position"
```

### Adding a new player (admin workflow)

1. **wp-admin → Users → Add New**
2. Set role to **Subscriber**, tick **Send User Notification**
3. Player receives email, sets password, logs in
4. Player edits own profile at `/members/<username>/profile/edit/`
5. Link the player from the Team page: `[Name](/members/<username>/)`

Players can only edit their own profile — they cannot access any other site content.

### Player profile URLs

| Page | URL |
|---|---|
| All members | `http://soccer.local:32300/members/` |
| Player profile | `http://soccer.local:32300/members/<username>/profile/` |
| Player self-edit | `http://soccer.local:32300/members/<username>/profile/edit/` |

---

## Branding — Applying a Custom Logo

### Upload logo to pod then import into WordPress

```bash
# 1. Transfer the logo file using transfer.py (kubectl cp is broken on Windows)
python transfer.py logo.jpg //var/www/html/logo.jpg

# 2. Import into WordPress media library
ATTACH_ID=$(MSYS_NO_PATHCONV=1 kubectl exec -n soccer <pod> -- \
  wp --allow-root media import /var/www/html/logo.jpg --title="Club Logo" --porcelain)

# 3. Set Sydney's own logo mod (URL, not attachment ID)
MSYS_NO_PATHCONV=1 kubectl exec -n soccer <pod> -- \
  wp --allow-root theme mod set site_logo \
  "http://soccer.local:32300/wp-content/uploads/$(date +%Y/%m)/logo.jpg"

# 4. Clean up
MSYS_NO_PATHCONV=1 kubectl exec -n soccer <pod> -- rm //var/www/html/logo.jpg
```

### Replace emoji/icon in hero block with logo image

In the home page Gutenberg content, replace the emoji heading with:

```html
<!-- wp:image {"align":"center","width":180,"height":180,"sizeSlug":"full",
     "style":{"border":{"radius":"50%"},"spacing":{"margin":{"bottom":"16px"}}}} -->
<figure class="wp-block-image aligncenter is-resized" style="margin-bottom:16px">
  <img src="http://soccer.local:32300/wp-content/uploads/2026/03/logo.jpg"
       alt="Club Name" width="180" height="180" style="border-radius:50%"/>
</figure>
<!-- /wp:image -->
```

---

## Troubleshooting

| Problem | Cause | Fix |
|---|---|---|
| `soccer.local` times out | Wrong node IP in hosts file | Use the IP of the node running the ingress controller pod, not the control plane |
| `kubectl cp` fails on Windows | Git Bash path conversion of `C:` paths | Use Python stdin pipe (`transfer.py`) |
| `kubectl exec -- php /tmp/file.php` opens Windows temp | Git Bash converts `/tmp` to `C:/Users/.../AppData/Local/Temp` | Write to `//var/www/html/` instead; prefix with `MSYS_NO_PATHCONV=1` |
| Logo not showing (Sydney theme) | Set `custom_logo` (WP core, ignored by Sydney) instead of `site_logo` | Set `site_logo` theme mod to the image **URL** |
| Logo not showing (header text still visible) | `display_header_text` set to string `"false"` (truthy in PHP) | Set it to empty string `""` |
| Site still routing to old namespace after new deploy | Old ingress in different namespace claiming same hostname | Delete old ingress: `kubectl delete ingress <name> -n <ns>` |
| WP-CLI not found in pod | Not included in `wordpress:latest` image | Download at runtime: `curl -sO .../wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp` |
| NodePort accessible on worker nodes but not control plane | Control plane firewall blocks NodePort | Use worker node IP in `/etc/hosts`, not control plane IP |
| Page still shows sidebar | `fullwidth_pages` theme mod not enough on its own | Also set `_sydney_page_disable_sidebar` post meta on the specific page |
| `/members/` returns 404 | `bp-pages` option pointing to non-existent post IDs | Rebuild from BuddyPress's own `buddypress` post type (see BuddyPress section) |
| BuddyPress `selectbox` field type fails silently | `--options` flag not supported in BP WP-CLI 14.x | Use `--type=textbox` for all custom profile fields |
| Re-running branding PHP imports logo again as new attachment | PHP script tries to copy already-deleted temp file | Fix `custom_logo` and `site_logo` back to original attachment ID/URL after re-run, delete the orphan attachment |
| `wp --allow-root post update <id> --post_content=-` sets content to `-` | `--post_content=-` reads from stdin but shell pipes failed | Use a PHP script via `transfer.py` to update post content with complex HTML |

---

## Useful Day-to-Day Commands

```bash
# Status
make status                  # kubectl get all -n soccer

# Logs
make logs                    # stream WordPress logs

# WP-CLI one-liners
kubectl exec -n soccer <pod> -- wp --allow-root option get siteurl
kubectl exec -n soccer <pod> -- wp --allow-root plugin list
kubectl exec -n soccer <pod> -- wp --allow-root theme mod list

# MySQL backup
kubectl exec -n soccer deployment/mysql -- \
  mysqldump -u root -pSoccerRoot2026! wordpress > backup-$(date +%Y%m%d).sql

# Restart WordPress (without data loss)
kubectl rollout restart deployment/wordpress -n soccer

# Full teardown (DELETES ALL DATA)
make teardown
```
