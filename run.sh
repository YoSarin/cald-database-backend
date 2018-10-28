#! /bin/bash
data/start_db.sh
docker build -t cald-database-backend .
pwd
CID=$(docker run --rm -d -e DB_HOST=172.17.0.2 --name=cald-database-backend cald-database-backend)
