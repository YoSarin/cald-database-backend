# Dostupná API:
## `POST /user`
**Params**: `email`, `password`, `login`  
**Auth**: -  
Vytvoření nového uživatele, mělo by poslat mail s hashem na ověření mailu, ale maily ještě neposílá

## `POST /user/login`
**Params**: `login`, `password`  
**Auth**: -  
Přihlášení uživatele - vrátí token pomocí kterého se pak autentifikují requesty

## `GET /user/verify/{hash}`
**Params**: `hash`  
**Auth**: -  
Ověří hash (pro potvrzování uživatelů etc)

## `GET /list/{type}`
**Params**: `type`, `[filter]`, `[extend]`, `[limit]`, `[offset]`  
**Auth**: token  
Vrací seznam objektů daného typu.  
**`type`**: list čeho chceme získat, momentálně podporované jsou: player, team, player_at_team, roster, player_at_roster, tournament, season, tournament_belongs_to_league_and_division,
division, league a user (odfiltrované sloupečky salt a password)  
**`filter`**: je asociativní pole podle kterého se mají výsledky filtrovat, pro detaily jak se to dělá - zkoukni dokumentaci k DB frameworku medoo  
**`extend`**: je li předán s hodnotou 1, tak aplikace vrátí rozšířené výsledky (místo referencovaných ID načte odkazované záznamy a rovnou je vrátí - používat s rozmyslem)  
**`limit`**: počet výsledků které se mají vrátit  
**`offset`**: kolik výsledků se má přeskočit

## `POST /team`
**Params**: `name`, `[city]`, `[www]`, `[email]`, `[founded_at]`  
**Auth**: token  
Vytvoří nový tým a dá aktuálně přihlášenému uživateli práva k jeho editaci

## `POST /admin/tournament`
**Params**: `name`, `date`, `location`, `duration`, `season_id`, `league_ids`, `division_ids`  
**Auth**: token (musí být admin)  
Vytvoří nový turnaj se zadanými parametry  
**`name`**: jméno turnaje  
**`date`**: datum turnaje (YYYY-MM-DD)  
**`location`**: název lokace kde se turnaj koná  
**`duration`**: počet dní kolik bude turnaj probíhat  
**`season_id`**: ID sezóny do které turnaj patří (pro výběr sezóny použij `GET /list/season`)  
**`league_ids`**: pole ID lig (hala|venek|středoškolka|U23|...) do kterých turnaj patří (pro výběr sezóny použij `GET /list/league`)  
**`division_ids`**: pole ID divizí (open|woman|mix) do kterých turnaj patří (pro výběr divize použij `GET /list/division`)

## `DELETE /admin/tournament/{id}`
**Params**: `id`  
**Auth**: token (musí být admin)  
Označí daný turnaj za smazaný (jen nastavuje příznak v DB, reálně nic nemaže)
