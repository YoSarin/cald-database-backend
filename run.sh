#! /bin/bash
IP=172.17.0.2
if ! mysql -u cald -pcald -h$IP -e 'show databases;' > /dev/null 2>&1 ; then
  data/start_db.sh
fi
docker build -t cald-api docker/develop/
docker run -v $(pwd):/var/www/cald-database-backend:Z --rm -d -e DB_HOST=$IP --name=cald-api cald-api
