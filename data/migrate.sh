#! /usr/bin/env bash

usage="Usage:
    source_db={source_db_name} target_db={target_db_name} user={db_user_name} [dev=1] [host=localhost] [pass=<password>] ./migrate.sh"

dir="$(dirname "$0")"

: "${source_db:?Need to set source_db: $usage}"
: "${target_db:?Need to set target_db: $usage}"
: "${user:?Need to set user variable (user has to have read access to $source_db and write access to $target_db): $usage}"

(cat $dir/sql/migrate.sql | sed s/\:new_schema_name\:/$target_db/g) > $dir/sql/specific_migrate.sql
if [ $dev ]; then
   sed -i 's/\/\*\* DEV-ONLY://g' $dir/sql/specific_migrate.sql
   sed -i 's/:DEV-ONLY \*\*\///g' $dir/sql/specific_migrate.sql
fi

if [ ! $host ]; then
   host="localhost"
fi

if [ $pass ]; then
   mysql -D $source_db -u $user -p$pass -h $host< $dir/sql/specific_migrate.sql && echo "migration done"
else
   mysql -D $source_db -u $user -p$pass -h $host< $dir/sql/specific_migrate.sql && echo "migration done"
fi
rm $dir/sql/specific_migrate.sql
