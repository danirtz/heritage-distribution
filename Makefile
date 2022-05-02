#!/bin/bash

UID = $(shell id -u)
APP_NAME = heritage


start:
	USER_ID=${UID} CONTAINER_NAME=${APP_NAME} docker-compose up -d --build

stop:
	USER_ID=${UID} CONTAINER_NAME=${APP_NAME} docker-compose stop

serve:
	docker exec -it --user ${UID} ${APP_NAME} symfony server:start

logs:
	docker exec -it --user ${UID} ${APP_NAME} symfony server:log

bash:
	docker exec -it --user ${UID} ${APP_NAME} bash

clean:
	$(MAKE) stop
	docker rm ${APP_NAME}
	

