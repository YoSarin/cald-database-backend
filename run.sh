#! /usr/bin/env bash

dbContainerName="cald-db"
apiContainerName="cald-api"

if ! docker ps | grep $dbContainerName > /dev/null 2>&1 ; then
  echo "Starting up database"
  docker build -t $dbContainerName docker/develop/database/
  docker run -d --rm -p 3306:3306 -e MYSQL_ROOT_PASSWORD=cald -e MYSQL_USER=cald -e MYSQL_PASSWORD=cald --name=$dbContainerName $dbContainerName
fi
if ! docker ps | grep $dbContainerName > /dev/null 2>&1 ; then
  echo "Database not running"
  exit 1
fi

IP=$(docker inspect $dbContainerName --format '{{ .NetworkSettings.IPAddress }}')

if ! docker ps | grep $apiContainerName > /dev/null 2>&1 ; then
  docker build -t $apiContainerName docker/develop/
  docker run -d --rm -p8080:80 -v $(pwd):/var/www/cald-database-backend:Z -e DB_HOST=$IP --name=$apiContainerName $apiContainerName
  docker exec $apiContainerName /bin/init.sh
fi
if ! docker ps | grep $apiContainerName > /dev/null 2>&1 ; then
  echo "Api not running"
  exit 1
fi