# CLAUDE.md — Sunday Soccer Team Website

> This file instructs Claude Code on how to build, deploy, and manage a Sunday soccer team website
> running WordPress on a local Kubernetes cluster (e.g. Docker Desktop K8s, minikube, or k3s).

---

## Project Overview

Build a fully functional Sunday soccer team website powered by WordPress, deployed on a local
Kubernetes cluster running on a laptop. Each player gets a personal profile page they can edit
themselves. The site is managed through the WordPress admin dashboard with no coding required
for day-to-day updates.

---

## Tech Stack

| Layer | Technology | Reason |
|---|---|---|
| CMS | WordPress 6.x | Easy page/content management, no coding needed |
| Database | MySQL 8.0 (via Kubernetes) | WordPress default database |
| Web server | Nginx (as ingress) | Routes traffic into the cluster |
| Container runtime | Docker | Packages WordPress and MySQL |
| Orchestration | Kubernetes (local) | Runs on your laptop |
| Ingress | Nginx Ingress Controller | Exposes the site on localhost |
| Storage | Kubernetes PersistentVolumes | Keeps data across pod restarts |
| Secrets | Kubernetes Secrets | Stores DB passwords securely |

---

## Prerequisites — Check These First

Before running any commands, verify these tools are installed on your laptop:

```bash
# Check kubectl is connected to your local cluster
kubectl cluster-info

# Check Docker is running
docker info

# Check helm is installed (used to install Nginx Ingress)
helm version

# Check your cluster has a storage class
kubectl get storageclass
```

If any of these fail, here is what to install:

- **Kubernetes locally**: Install [Docker Desktop](https://www.docker.com/products/docker-desktop/)
  and enable Kubernetes in Settings → Kubernetes → Enable Kubernetes. Alternatively use
  [minikube](https://minikube.sigs.k8s.io/) or [k3s](https://k3s.io/).
- **kubectl**: Comes with Docker Desktop. For minikube: `brew install kubectl` (macOS) or
  `choco install kubernetes-cli` (Windows).
- **Helm**: `brew install helm` (macOS) or see https://helm.sh/docs/intro/install/
- **Storage class**: Docker Desktop provides `hostpath` by default. minikube: run
  `minikube addons enable default-storageclass`.

---

## Repository / File Structure to Create

Claude Code should create ALL of the following files and folders:

```
soccer-team-site/
├── CLAUDE.md                          ← this file
├── README.md                          ← quick-start guide
├── Makefile                           ← convenience commands (make deploy, make teardown, etc.)
│
├── kubernetes/
│   ├── namespace.yaml                 ← isolates all resources in "soccer" namespace
│   ├── secrets.yaml                   ← MySQL root password, WordPress DB password
│   ├── mysql/
│   │   ├── pvc.yaml                   ← PersistentVolumeClaim for MySQL data (10Gi)
│   │   ├── deployment.yaml            ← MySQL 8.0 Deployment
│   │   └── service.yaml               ← ClusterIP Service so WordPress can reach MySQL
│   ├── wordpress/
│   │   ├── pvc.yaml                   ← PersistentVolumeClaim for WordPress uploads (5Gi)
│   │   ├── deployment.yaml            ← WordPress latest Deployment
│   │   ├── service.yaml               ← ClusterIP Service for WordPress
│   │   └── configmap.yaml             ← WordPress environment config (DB host, name)
│   └── ingress/
│       ├── ingress-controller.yaml    ← Nginx Ingress Controller (if not using Helm)
│       └── ingress.yaml               ← Routes soccer.local → WordPress service
│
├── wordpress-config/
│   ├── plugins.txt                    ← List of plugins to auto-install via WP-CLI
│   └── wp-setup.sh                    ← WP-CLI script: installs plugins, sets options
│
├── scripts/
│   ├── deploy.sh                      ← Full deploy: apply all manifests in order
│   ├── teardown.sh                    ← Delete all Kubernetes resources
│   ├── port-forward.sh                ← Local port-forward if not using ingress
│   └── run-wpcli.sh                   ← Helper: exec WP-CLI inside WordPress pod
│
└── docs/
    ├── architecture.md                ← How everything fits together
    ├── player-profiles.md             ← How players edit their own profile pages
    └── managing-the-site.md           ← Day-to-day admin guide
```

---

## Kubernetes Manifests — Detailed Specs

### namespace.yaml

```yaml
apiVersion: v1
kind: Namespace
metadata:
  name: soccer
```

### secrets.yaml

> IMPORTANT: Claude Code must base64-encode the values.
> Example: `echo -n "mysecretpassword" | base64`
> Do NOT commit real passwords to git. Use a `.env` file or sealed secrets for production.

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: mysql-secret
  namespace: soccer
type: Opaque
data:
  mysql-root-password: <base64 of root password>
  mysql-password: <base64 of wordpress db password>
```

### mysql/pvc.yaml

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: mysql-pvc
  namespace: soccer
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 10Gi
```

### mysql/deployment.yaml

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: mysql
  namespace: soccer
spec:
  replicas: 1
  selector:
    matchLabels:
      app: mysql
  template:
    metadata:
      labels:
        app: mysql
    spec:
      containers:
        - name: mysql
          image: mysql:8.0
          env:
            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-root-password
            - name: MYSQL_DATABASE
              value: wordpress
            - name: MYSQL_USER
              value: wordpress
            - name: MYSQL_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-password
          ports:
            - containerPort: 3306
          volumeMounts:
            - name: mysql-storage
              mountPath: /var/lib/mysql
      volumes:
        - name: mysql-storage
          persistentVolumeClaim:
            claimName: mysql-pvc
```

### mysql/service.yaml

```yaml
apiVersion: v1
kind: Service
metadata:
  name: mysql
  namespace: soccer
spec:
  selector:
    app: mysql
  ports:
    - port: 3306
      targetPort: 3306
  type: ClusterIP
```

### wordpress/pvc.yaml

```yaml
apiVersion: v1
kind: PersistentVolumeClaim
metadata:
  name: wordpress-pvc
  namespace: soccer
spec:
  accessModes:
    - ReadWriteOnce
  resources:
    requests:
      storage: 5Gi
```

### wordpress/configmap.yaml

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: wordpress-config
  namespace: soccer
data:
  WORDPRESS_DB_HOST: mysql
  WORDPRESS_DB_NAME: wordpress
  WORDPRESS_DB_USER: wordpress
```

### wordpress/deployment.yaml

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: wordpress
  namespace: soccer
spec:
  replicas: 1
  selector:
    matchLabels:
      app: wordpress
  template:
    metadata:
      labels:
        app: wordpress
    spec:
      containers:
        - name: wordpress
          image: wordpress:latest
          envFrom:
            - configMapRef:
                name: wordpress-config
          env:
            - name: WORDPRESS_DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-password
          ports:
            - containerPort: 80
          volumeMounts:
            - name: wordpress-storage
              mountPath: /var/www/html/wp-content
      volumes:
        - name: wordpress-storage
          persistentVolumeClaim:
            claimName: wordpress-pvc
```

### wordpress/service.yaml

```yaml
apiVersion: v1
kind: Service
metadata:
  name: wordpress
  namespace: soccer
spec:
  selector:
    app: wordpress
  ports:
    - port: 80
      targetPort: 80
  type: ClusterIP
```

### ingress/ingress.yaml

```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: wordpress-ingress
  namespace: soccer
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  ingressClassName: nginx
  rules:
    - host: soccer.local
      http:
        paths:
          - path: /
            pathType: Prefix
            backend:
              service:
                name: wordpress
                port:
                  number: 80
```

---

## Nginx Ingress Controller — Install via Helm

Claude Code should install this via Helm (not a raw manifest) as it is the most reliable method:

```bash
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update
helm install ingress-nginx ingress-nginx/ingress-nginx \
  --namespace ingress-nginx \
  --create-namespace \
  --set controller.service.type=NodePort \
  --set controller.hostNetwork=true
```

> For Docker Desktop on macOS/Windows: use `LoadBalancer` instead of `NodePort` — Docker Desktop
> maps LoadBalancer services to `localhost` automatically.

After installing, add this line to your `/etc/hosts` (macOS/Linux) or
`C:\Windows\System32\drivers\etc\hosts` (Windows):

```
127.0.0.1   soccer.local
```

The site will then be accessible at: **http://soccer.local**

---

## WordPress Plugins to Install

Claude Code should install all of these via WP-CLI (see `wp-setup.sh`):

```
# wordpress-config/plugins.txt

# User profile editing
buddypress                  # Player profiles — each player gets their own profile page

# Team management
sportspress                 # Fixtures, results, league tables, player stats

# Photo galleries
envira-gallery-lite         # Match day photo galleries

# Forms (contact, RSVP for matches)
contact-form-7              # Contact form and match availability forms

# SEO
wordpress-seo               # Yoast SEO — helps the site be found on Google

# Security
wordfence                   # Firewall and login protection

# Performance
wp-super-cache              # Page caching for faster load times

# Backups
updraftplus                 # Scheduled backups to Google Drive / Dropbox

# Social sharing
social-warfare              # Share match results on Facebook, Twitter

# Events / fixtures calendar
the-events-calendar         # Calendar of upcoming matches, training sessions

# Media management
regenerate-thumbnails       # Fix image sizes after theme changes
```

---

## WP-CLI Setup Script

Create `wordpress-config/wp-setup.sh`. This script runs inside the WordPress pod via kubectl exec.

```bash
#!/bin/bash
# Run with: bash scripts/run-wpcli.sh

set -e

WP="wp --allow-root"
SITE_URL="http://soccer.local"
ADMIN_USER="admin"
ADMIN_PASS="changeme123"   # CHANGE THIS before running
ADMIN_EMAIL="admin@soccerteam.local"
SITE_TITLE="Sunday Soccer Team"

echo "==> Installing WordPress core..."
$WP core install \
  --url="$SITE_URL" \
  --title="$SITE_TITLE" \
  --admin_user="$ADMIN_USER" \
  --admin_password="$ADMIN_PASS" \
  --admin_email="$ADMIN_EMAIL" \
  --skip-email

echo "==> Installing and activating plugins..."
while IFS= read -r plugin; do
  # Skip comments and blank lines
  [[ "$plugin" =~ ^#.*$ || -z "$plugin" ]] && continue
  echo "  Installing: $plugin"
  $WP plugin install "$plugin" --activate
done < /plugins.txt

echo "==> Installing a soccer-friendly theme..."
$WP theme install sydney --activate

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

echo "==> Done! Visit $SITE_URL/wp-admin to finish setup."
```

---

## scripts/deploy.sh

```bash
#!/bin/bash
set -e

echo "==> Deploying Sunday Soccer Site to Kubernetes..."

echo "  Creating namespace..."
kubectl apply -f kubernetes/namespace.yaml

echo "  Creating secrets..."
kubectl apply -f kubernetes/secrets.yaml

echo "  Deploying MySQL..."
kubectl apply -f kubernetes/mysql/pvc.yaml
kubectl apply -f kubernetes/mysql/deployment.yaml
kubectl apply -f kubernetes/mysql/service.yaml

echo "  Waiting for MySQL to be ready..."
kubectl rollout status deployment/mysql -n soccer --timeout=120s

echo "  Deploying WordPress..."
kubectl apply -f kubernetes/wordpress/pvc.yaml
kubectl apply -f kubernetes/wordpress/configmap.yaml
kubectl apply -f kubernetes/wordpress/deployment.yaml
kubectl apply -f kubernetes/wordpress/service.yaml

echo "  Waiting for WordPress to be ready..."
kubectl rollout status deployment/wordpress -n soccer --timeout=120s

echo "  Applying ingress..."
kubectl apply -f kubernetes/ingress/ingress.yaml

echo ""
echo "==> Deployment complete!"
echo "    Make sure 'soccer.local' is in your /etc/hosts → 127.0.0.1"
echo "    Open http://soccer.local to complete WordPress setup."
```

### scripts/run-wpcli.sh

```bash
#!/bin/bash
# Runs wp-setup.sh inside the WordPress pod using WP-CLI

POD=$(kubectl get pods -n soccer -l app=wordpress -o jsonpath='{.items[0].metadata.name}')
echo "==> Running WP-CLI in pod: $POD"

# Copy plugins.txt into the pod first
kubectl cp wordpress-config/plugins.txt soccer/$POD:/plugins.txt

# Copy and run the setup script
kubectl cp wordpress-config/wp-setup.sh soccer/$POD:/wp-setup.sh
kubectl exec -n soccer "$POD" -- bash /wp-setup.sh
```

### scripts/teardown.sh

```bash
#!/bin/bash
# WARNING: This deletes ALL data including the database and uploads

read -p "This will DELETE all data. Are you sure? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
  echo "Aborted."
  exit 1
fi

kubectl delete namespace soccer
echo "==> All resources deleted."
```

### scripts/port-forward.sh

```bash
#!/bin/bash
# Use this if ingress is not working — access site at http://localhost:8080

POD=$(kubectl get pods -n soccer -l app=wordpress -o jsonpath='{.items[0].metadata.name}')
echo "==> Port-forwarding WordPress to http://localhost:8080"
echo "    Press Ctrl+C to stop."
kubectl port-forward -n soccer "$POD" 8080:80
```

---

## Makefile

```makefile
.PHONY: deploy teardown status wpcli logs port-forward

deploy:
	bash scripts/deploy.sh

teardown:
	bash scripts/teardown.sh

status:
	kubectl get all -n soccer

wpcli:
	bash scripts/run-wpcli.sh

logs:
	kubectl logs -n soccer deployment/wordpress -f

port-forward:
	bash scripts/port-forward.sh
```

---

## Player Profile Pages — How They Work

### Recommended approach: BuddyPress + User Role Editor

1. **BuddyPress** (installed by `wp-setup.sh`) gives every registered user:
   - A profile page at `http://soccer.local/members/<username>/`
   - Fields for: name, position, bio, avatar/photo, social links
   - An edit page at `http://soccer.local/members/<username>/profile/edit/`

2. **Admin creates an account for each player:**
   - Go to `wp-admin → Users → Add New`
   - Set role to `Subscriber`
   - Send the player their login credentials

3. **Each player can then:**
   - Log in at `http://soccer.local/wp-login.php`
   - Go to their profile and click Edit Profile
   - Update their name, position, bio, profile photo
   - They CANNOT edit other players' profiles or site content

4. **Admin can add custom BuddyPress profile fields:**
   - Go to `wp-admin → Users → Profile Fields`
   - Add fields: Position (e.g. Goalkeeper, Defender), Jersey Number, Goals Scored

5. **Link each player from the Team page:**
   - Edit the Team page in WordPress
   - Add each player's name as a link to `http://soccer.local/members/<their-username>/`

---

## Suggested Pages and Content Structure

| Page | Content |
|---|---|
| Home | Team name, hero image, next match date, recent results |
| Team | Grid of player cards, each linking to their BuddyPress profile |
| Fixtures | Upcoming matches (use The Events Calendar plugin) |
| Results | Past match scores and scorers (use SportsPress plugin) |
| Gallery | Match day photos (use Envira Gallery plugin) |
| Contact | Contact form (use Contact Form 7 plugin) |

---

## What I Suggested You May Have Missed

These are important items not explicitly mentioned but necessary for a complete setup:

### Security
- **Change the default admin password** before going live — use a strong unique password
- **Wordfence** (included in plugins) provides a firewall and blocks brute-force login attempts
- Consider adding `fail2ban` on the host if exposing to the internet

### Backups
- **UpdraftPlus** (included) should be configured to back up to Google Drive or Dropbox weekly
- Also back up the Kubernetes PersistentVolumes:
  ```bash
  kubectl exec -n soccer deployment/mysql -- \
    mysqldump -u root -p<password> wordpress > backup-$(date +%Y%m%d).sql
  ```

### Email (important for player registration and password resets)
- WordPress needs an SMTP server to send emails (registration confirmations, password resets)
- Install the **WP Mail SMTP** plugin and configure with Gmail, SendGrid, or Mailgun
- Without this, player registration emails will not be delivered

### Domain name (if you want it accessible beyond your laptop)
- Currently the site only works on your laptop at `soccer.local`
- To share with players on the same Wi-Fi: use your laptop's local IP instead of `soccer.local`
  in `/etc/hosts` on other devices, or use a tool like [ngrok](https://ngrok.com/) for temporary
  external access
- For a permanent public URL, you would need a cloud server and a real domain name

### SSL / HTTPS
- For local use, HTTP is fine
- For public access, use [cert-manager](https://cert-manager.io/) in Kubernetes to get free
  Let's Encrypt SSL certificates

### Laptop restarts
- Kubernetes pods restart automatically, but you may need to restart Docker Desktop
- All data is persisted in PersistentVolumes so no content is lost on restart

---

## How to Manage the Website Day-to-Day

### Adding a new player
1. `wp-admin → Users → Add New`
2. Enter their name and email, set role to **Subscriber**, tick "Send user notification"
3. They receive an email with a link to set their password
4. They then log in and fill out their BuddyPress profile
5. Add them to the Team page with a link to their profile

### Posting a match result
1. `wp-admin → SP → Events → Add New` (SportsPress)
2. Enter the match date, teams, score, and goalscorers
3. Publish — it automatically updates the league table and player stats

### Adding match photos
1. `wp-admin → Envira Gallery → Add New`
2. Upload photos, add a title (e.g. "vs City FC — 15 Mar 2026")
3. Publish and add the gallery to the Gallery page

### Adding a fixture / event
1. `wp-admin → Events → Add New` (The Events Calendar)
2. Enter date, time, venue, and opponent
3. Publish — it appears on the Fixtures page calendar

### Updating site settings
- `wp-admin → Settings → General` — change site title, tagline, timezone
- `wp-admin → Appearance → Customize` — change colours, logo, header image
- `wp-admin → Appearance → Menus` — add/remove pages from the navigation menu

### Checking the cluster is healthy
```bash
# See all running pods
kubectl get pods -n soccer

# Check pod logs if something is wrong
kubectl logs -n soccer deployment/wordpress
kubectl logs -n soccer deployment/mysql

# Restart WordPress if it becomes unresponsive
kubectl rollout restart deployment/wordpress -n soccer
```

---

## How to Deploy (Step-by-Step Summary)

```bash
# 1. Clone or create the project folder
mkdir soccer-team-site && cd soccer-team-site

# 2. Create all files from this CLAUDE.md specification
#    (Claude Code will do this automatically)

# 3. Edit kubernetes/secrets.yaml with your chosen passwords

# 4. Install Nginx Ingress Controller
helm repo add ingress-nginx https://kubernetes.github.io/ingress-nginx
helm repo update
helm install ingress-nginx ingress-nginx/ingress-nginx \
  --namespace ingress-nginx --create-namespace

# 5. Deploy everything
make deploy

# 6. Add soccer.local to /etc/hosts
echo "127.0.0.1 soccer.local" | sudo tee -a /etc/hosts

# 7. Open http://soccer.local — complete the WordPress 5-minute setup wizard

# 8. Run the automated plugin and page setup
make wpcli

# 9. Log in to wp-admin and configure your team name, logo, and colours
```

---

## Architecture Diagram

```
Your Laptop
│
├── Docker Desktop / minikube (Kubernetes)
│   │
│   └── Namespace: soccer
│       │
│       ├── Deployment: wordpress (port 80)
│       │   └── PersistentVolume: wp-content (uploads, themes, plugins)
│       │
│       ├── Deployment: mysql (port 3306)
│       │   └── PersistentVolume: mysql data
│       │
│       ├── Secret: mysql-secret (passwords)
│       ├── ConfigMap: wordpress-config (DB host, name)
│       │
│       └── Ingress: soccer.local → wordpress:80
│
├── Namespace: ingress-nginx
│   └── Nginx Ingress Controller (NodePort/LoadBalancer)
│
└── /etc/hosts: 127.0.0.1 soccer.local
        │
        └── Browser: http://soccer.local
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| `soccer.local` does not load | Check `/etc/hosts` has `127.0.0.1 soccer.local`. Run `kubectl get pods -n soccer` to check pods are Running. |
| WordPress shows database error | MySQL pod may still be starting. Wait 60s and retry. Check `kubectl logs -n soccer deployment/mysql`. |
| Pod stuck in `CrashLoopBackOff` | Run `kubectl describe pod <podname> -n soccer` for details. |
| Uploads not saving | Check the WordPress PVC is bound: `kubectl get pvc -n soccer`. |
| Players can't register | Check `wp-admin → Settings → General → Membership` — "Anyone can register" must be ticked. |
| Emails not sending | Install and configure WP Mail SMTP plugin with an external SMTP provider. |

---

*Generated by Claude — Claude Code will implement all files and folders described above.*
