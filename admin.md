# Admin Api - dostupná jen pro uživatele s Admin oprávněním
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

## `GET /admin/fee`
**Params**: `season_id`  
**Auth**: token (musí být admin)  
Vrátí výši příspěvků pro jednotlivé týmy za danou sezónu. Součástí odpovědi je i seznam hráčů, kteří tuto sezónu hráli za více než 1 tým.

## `POST /admin/fee/pardon`
**Params**: `player_id`, `season_id`  
**Auth**: token (musí být admin)  
Odpustí danému hráči poplatky za danou sezónu  
**`player_id`**: ID hráče, kterému budou příspěvky odpuštěny  
**`season_id`**: ID sezóny pro kterou odpustek platí

## `DELETE /admin/fee/pardon`
**Params**:`pardon_id`  
**Auth**: token (musí být admin)  
Smaže konkrétní 'odpustek' poplatků pro hráče

## `PUT /admin/user/{user_id}`
**Params**: `email`, `login`, `password`, `state`  
**Auth**: token, musí být admin  
Upraví data pro daného uživatele  
**`state`**: změní stav, může být jeden z 'waiting_for_confirmation', 'confirmed', 'blocked', 'password_reset'  
