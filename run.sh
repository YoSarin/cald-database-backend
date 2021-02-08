#! /usr/bin/env bash
IP=172.17.0.2
if ! docker ps | grep "cald-db" > /dev/null 2>&1 ; then
  echo "Starting up database"
  data/start_db.sh
fi
if ! docker ps | grep "cald-db" > /dev/null 2>&1 ; then
  echo "Database not running"
  exit 1
fi

if ! docker ps | grep "cald-api" > /dev/null 2>&1 ; then
  docker build -t cald-api docker/develop/
  docker run -p8080:80 -v $(pwd):/var/www/cald-database-backend:Z --rm -d -e DB_HOST=$IP --name=cald-api cald-api
  docker exec cald-api /bin/init.sh
fi
if ! docker ps | grep "cald-api" > /dev/null 2>&1 ; then
  echo "Api not running"
  exit 1
fi