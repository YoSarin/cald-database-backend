FROM mysql
COPY my.cnf /etc/mysql/conf.d/my.cnf

COPY src/live.data.sql /tmp/data.sql
COPY sql/add_admin.sql /tmp/add_admin.sql

COPY scripts/init.sh /docker-entrypoint-initdb.d/init.sh
RUN chmod +x /docker-entrypoint-initdb.d/init.sh

EXPOSE 3306:3306