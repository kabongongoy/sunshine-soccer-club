#!/bin/bash
# Fallback access via localhost:8080 without going through the ingress.
POD=$(kubectl get pods -n soccer -l app=wordpress -o jsonpath='{.items[0].metadata.name}')
echo "==> Port-forwarding WordPress to http://localhost:8080"
echo "    Press Ctrl+C to stop."
kubectl port-forward -n soccer "$POD" 8080:80
