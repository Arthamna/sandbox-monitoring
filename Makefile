up:
	docker compose -p ctf-sandbox up -d

down:
	docker compose -p ctf-sandbox down

build:
	docker compose -p ctf-sandbox build --no-cache

ex-php:
	docker exec -it --user sail ctf-sandbox /bin/bash