# Sunday Soccer Team Site

WordPress + MySQL on Kubernetes. Player profile pages, fixtures, results, photo galleries.

## Quick Start

```bash
# 1. Deploy MySQL + WordPress + Ingress to the 'soccer' namespace
make deploy

# 2. Add to your hosts file (192.168.1.80 is the cluster control-plane)
#    Windows: C:\Windows\System32\drivers\etc\hosts
#    Linux/macOS: /etc/hosts
192.168.1.80   soccer.local

# 3. Open http://soccer.local:32300 — skip the setup wizard (step 4 handles it)

# 4. Install plugins + configure WordPress automatically
make wpcli
```

Admin login after step 4: `http://soccer.local:32300/wp-admin`
Default credentials: `admin` / `changeme123` — **change this immediately**.

## Commands

| Command | What it does |
|---|---|
| `make deploy` | Apply all manifests; wait for rollouts |
| `make teardown` | Delete everything in the `soccer` namespace |
| `make status` | Show all pods/services/ingresses in `soccer` |
| `make wpcli` | Run WP-CLI setup (plugins, pages, theme) |
| `make logs` | Stream WordPress pod logs |
| `make port-forward` | Access at http://localhost:8080 (ingress bypass) |

## Cluster Notes

- Storage: `nfs-readynas` (NFS, ReadWriteMany)
- Ingress: NodePort — HTTP on port **32300**, HTTPS on **31381**
- Namespace: `soccer`

## Passwords

Set in `kubernetes/secrets.yaml` (base64-encoded).
Defaults: root=`SoccerRoot2026!`, wordpress=`SoccerWP2026!`
To change: `echo -n "newpassword" | base64` and update the file before `make deploy`.
