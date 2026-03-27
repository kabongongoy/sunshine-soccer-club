#!/bin/bash
# Copies plugins.txt and wp-setup.sh into the WordPress pod, then runs the setup.
set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$SCRIPT_DIR/.."

POD=$(kubectl get pods -n soccer -l app=wordpress -o jsonpath='{.items[0].metadata.name}')
echo "==> Running WP-CLI setup in pod: $POD"

kubectl cp "$ROOT/wordpress-config/plugins.txt" "soccer/$POD:/plugins.txt"
kubectl cp "$ROOT/wordpress-config/wp-setup.sh"  "soccer/$POD:/wp-setup.sh"
kubectl exec -n soccer "$POD" -- bash /wp-setup.sh
