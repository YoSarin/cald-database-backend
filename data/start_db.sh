#! /bin/bash
cd $(dirname $0)
MYSQL_ROOT_PASSWORD=cald
docker build -t cald-db .
CID=$(docker run --rm -d --expose=3306 -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -e MYSQL_USER=cald -e MYSQL_PASSWORD=cald --name=cald-db cald-db)
trap "echo ' -> stopping docker'; docker stop $CID; exit" SIGHUP SIGINT SIGTERM

IP=$(docker inspect $CID | jq '.[0].NetworkSettings.IPAddress' -r)

echo "Waiting for server at $IP (Container ID: $CID) to spin up..."
until (mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -e 'show databases;' > /dev/null 2>&1)
do
    echo -n "."
    sleep 1
done
echo " OK"
(\
    echo "dropping databases" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -e "DROP DATABASE IF EXISTS cald" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -e "DROP DATABASE IF EXISTS cald_original" && \
    echo "creating databases and users" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -e "CREATE DATABASE cald_original;" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -e "CREATE DATABASE cald;" && \
    echo "granting privileges" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -D cald -e "GRANT ALL ON cald to 'cald'@'%';" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -D cald -e "GRANT ALL ON cald.* to 'cald'@'%';" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -D cald_original -e 'GRANT ALL ON cald_original to "cald"@"%";' && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -D cald_original -e "GRANT ALL ON cald_original.* to 'cald'@'%';" && \
    mysql -u root -p$MYSQL_ROOT_PASSWORD -h$IP -D cald_original < src/caldMembersRecord.mysql && \
    source_db=cald_original target_db=cald user=root pass=$MYSQL_ROOT_PASSWORD host=$IP ./migrate.sh && \
    docker logs -f $CID \
) || (\
    echo -n "FAILED - stopping container: " && docker stop $CID \
)
