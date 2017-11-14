#!/bin/bash

usage="Usage:
    source_db={source_db_name} target_db={target_db_name} user={db_user_name} [dev=1] ./migrate.sh"

dir="$(dirname "$0")"

: "${source_db:?Need to set source_db: $usage}"
: "${target_db:?Need to set target_db: $usage}"
: "${user:?Need to set user variable (user has to have read access to $source_db and write access to $target_db): $usage}"

(cat $dir/migrate.sql | sed s/\:new_schema_name\:/$target_db/g) > $dir/specific_migrate.sql
if [ $dev ]; then
   sed -i 's/\/\*\* DEV-ONLY://g' $dir/specific_migrate.sql
   sed -i 's/:DEV-ONLY \*\*\///g' $dir/specific_migrate.sql
fi

if [ ! $host ]; then
   export host="localhost"
fi
mysql -D $source_db -u $user -p -h $host< $dir/specific_migrate.sql && echo "migration done"
# rm $dir/specific_migrate.sql
