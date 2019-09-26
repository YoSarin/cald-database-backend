# test
```
mysql -u root -D cald_test -p < data/create.sql
mysql -u root -D cald_test -p < data/test.sql

curl -H "X-Auth-Token: token" 172.17.0.2/admin/fee?season_id=2

```

# DB container pro testy
Spustit `./start_db.sh`, vytvoří DB, naplní ji daty (je nutný mít soubor pro import) a tailuje log

# Skripty v tomhle adresáři:
- `migrate.sh` - one-tim skript pro migraci dat z původní čald DB do nové struktury
- `start_db.sh` - spustí docker container s databází a naplní ho daty - nepouštět na ostré DB, je to pro testy (naplní to DB daty z lokální SQL zálohy (`./src/caldMembersRecord.sql`))
- `update.py` - updatuje DB na poslední verzi/rollbackuje na předchozí (použije skripty v `./migrations/deploy`) (see `./update.py -h` for details). Potřebné moduly jsou sepsané v `update.py.requirements.txt`

# Logy:
docker logs -f cald-api
