#!/bin/bash
# WARNING: This permanently deletes all data including the database and uploads.

read -p "This will DELETE all data in the 'soccer' namespace. Are you sure? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
  echo "Aborted."
  exit 1
fi

kubectl delete namespace soccer
echo "==> All resources deleted."
