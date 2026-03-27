#!/bin/bash
set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$SCRIPT_DIR/.."

echo "==> Deploying Sunday Soccer Site to Kubernetes..."

echo "  Creating namespace..."
kubectl apply -f "$ROOT/kubernetes/namespace.yaml"

echo "  Creating secrets..."
kubectl apply -f "$ROOT/kubernetes/secrets.yaml"

echo "  Deploying MySQL..."
kubectl apply -f "$ROOT/kubernetes/mysql/pvc.yaml"
kubectl apply -f "$ROOT/kubernetes/mysql/deployment.yaml"
kubectl apply -f "$ROOT/kubernetes/mysql/service.yaml"

echo "  Waiting for MySQL to be ready..."
kubectl rollout status deployment/mysql -n soccer --timeout=120s

echo "  Deploying WordPress..."
kubectl apply -f "$ROOT/kubernetes/wordpress/pvc.yaml"
kubectl apply -f "$ROOT/kubernetes/wordpress/configmap.yaml"
kubectl apply -f "$ROOT/kubernetes/wordpress/deployment.yaml"
kubectl apply -f "$ROOT/kubernetes/wordpress/service.yaml"

echo "  Waiting for WordPress to be ready..."
kubectl rollout status deployment/wordpress -n soccer --timeout=120s

echo "  Applying ingress..."
kubectl apply -f "$ROOT/kubernetes/ingress/ingress.yaml"

echo ""
echo "==> Deployment complete!"
echo ""
echo "    The ingress controller runs on NodePort 32300."
echo "    Add this to your hosts file:  192.168.1.80  soccer.local"
echo "    Then open: http://soccer.local:32300"
echo ""
echo "    Next step: make wpcli  (installs plugins + configures WordPress)"
