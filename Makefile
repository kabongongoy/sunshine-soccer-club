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
