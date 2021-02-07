#! /usr/bin/env bash
CONTAINER_NAME=cald-db
cd $(dirname $0)
MYSQL_ROOT_PASSWORD=cald
docker build -t $CONTAINER_NAME .
CID=$(docker run -d --rm --expose=3306 -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -e MYSQL_USER=cald -e MYSQL_PASSWORD=cald --name=$CONTAINER_NAME $CONTAINER_NAME)
trap "echo ' -> stopping docker'; docker stop $CID; exit" SIGHUP SIGINT SIGTERM
