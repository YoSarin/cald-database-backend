#!/usr/bin/env bash
cd /var/www/cald-database-backend/data/
pip3 install -r update.py.requirements.txt

echo "Waiting for DB container to show up"
until nc -z $DB_HOST 3306; do   
  echo -n ". "
  sleep 1
done
echo ""
echo "DB is up, running update"

python3 update.py --host $DB_HOST --password cald