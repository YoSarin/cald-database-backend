# test
```
mysql -u root -D cald_test -p < data/create.sql
mysql -u root -D cald_test -p < data/test.sql

curl -H "X-Auth-Token: token" 172.17.0.2/admin/fee?season_id=2

```

# DB container pro testy
```
MYSQL_ROOT_PASSWORD=cald
sudo docker build -t cald-db.
sudo docker run --rm -d --expose=3306 -e MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD -e MYSQL_USER=cald -e MYSQL_PASSWORD=cald cald-db

mysql -u root -p$MYSQL_ROOT_PASSWORD - -e "CREATE DATABASE cald_original;"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE cald;"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE USER cald IDENTIFIED BY 'cald';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL ON cald to 'cald'@'%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL ON cald.* to 'cald'@'%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL ON cald_original to 'cald'@'%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL ON cald_original.* to 'cald'@'%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -D cald_original < original.sql

source_db=cald_original target_db=cald user=root pass=$MYSQL_ROOT_PASSWORD ./migrate.sh
```
