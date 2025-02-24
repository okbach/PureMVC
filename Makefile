install:
	docker-compose up -d
	docker exec -it mymvc composer install
	docker exec -it mymvc php mymvc/start/install2.php

stop:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d

logs:
	docker-compose logs -f
