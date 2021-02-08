mysql -u root -p$MYSQL_ROOT_PASSWORD -e "DROP DATABASE IF EXISTS cald"
mysql -u root -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE cald;"
mysql -u root -p$MYSQL_ROOT_PASSWORD -D cald -e "GRANT ALL ON cald to 'cald'@'%';"
mysql -u root -p$MYSQL_ROOT_PASSWORD -D cald -e "GRANT ALL ON cald.* to 'cald'@'%';"

mysql -u root -p$MYSQL_ROOT_PASSWORD cald < /tmp/data.sql

mysql -u root -p$MYSQL_ROOT_PASSWORD cald < /tmp/add_admin.sql