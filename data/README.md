# test
```
mysql -u root -D cald_test -p < data/create.sql
mysql -u root -D cald_test -p < data/test.sql

curl -H "X-Auth-Token: token" 172.17.0.2/admin/fee?season_id=2

```

# DB container pro testy
Spustit `./start_db.sh`, vytvoří DB, naplní ji daty (je nutný mít soubor pro import) a tailuje log
