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
  
## `POST /admin/nationality`
**Params**: `name`, `country_name`  
**Auth**:token, musí být admin  
Přidá novou národnost  
**`name`**: jméno národnosti (Česká, Slovenská, ...)  
**`country_name`**: jméno státu ke kterému se národnost váže (Česko, Slovensko, ...); zatím k ničemu, ale pak to můžeme ještě provázat s adresama  
  
## `PUT /admin/nationality/{nationality_id}`
**Params**: `[name]`, `[country_name]`  
**Auth**:token, musí být admin  
Upraví národnost  
**`name`**: jméno národnosti (Česká, Slovenská, ...)  
**`country_name`**: jméno státu ke kterému se národnost váže (Česko, Slovensko, ...); zatím k ničemu, ale pak to můžeme ještě provázat s adresama  
  
## `DELETE /admin/nationality/{nationality_id}`
**Auth**:token, musí být admin  
Odstraní národnost  
  
## `POST /season`
**Params**: `name`, `start`  
**Auth**: token, musí být admin  
Vytvoří novou sezónu  
**`name`**: jméno sezóny (2018, 2019, ...)  
**`start`**: datum kdy sezóna začala  
  
## `PUT /season/{season_id}`
**Params**: `[name]`, `[start]`  
**Auth**: token, musí být admin  
Upraví údaje sezóny  
**`name`**: jméno sezóny (2018, 2019, ...)  
**`start`**: datum kdy sezóna začala  
  
## `POST /fee`
**Params**: `amount`, `name`, `type`  
**Auth**: token, musí být admin  
Vytvoří nový poplatek  
**`amount`**: částka  
**`name`**: jméno poplatku (poplatek čaldu za sezónu)
**`type`**: typ poplatku - jeden z [`player_per_season`, `player_per_tournament`, `team_per_season`, `team_per_tournament`]  
  - `player_per_season` - poplatek platí každý hráč za účast na alespoň jednom turnaji související ligy v dané sezóně  
  - `player_per_tournament` - poplatek platí každý hráč za účast na každém turnaji související ligy  
  - `team_per_season` - poplatek platí každý tým za sezónu ve které se účastní alespoň jednoho turanje související ligy  
  - `team_per_tournament` - poplatek platí každý tým za účast na každém turnaj související ligy  
  
## `PUT /fee/{fee_id}`
**Params**: `[name]`, `[amount]`, `[type]`  
**Auth**: token, musí být admin  
Upraví údaje poplatku  
**`amount`**: částka  
**`name`**: jméno poplatku (poplatek čaldu za sezónu)
**`type`**: typ poplatku - jeden z [`player_per_season`, `player_per_tournament`, `team_per_season`, `team_per_tournament`]  
  - `player_per_season` - poplatek platí každý hráč za účast na alespoň jednom turnaji související ligy v dané sezóně  
  - `player_per_tournament` - poplatek platí každý hráč za účast na každém turnaji související ligy  
  - `team_per_season` - poplatek platí každý tým za sezónu ve které se účastní alespoň jednoho turanje související ligy  
  - `team_per_tournament` - poplatek platí každý tým za účast na každém turnaj související ligy  

## `DELETE /fee/{fee_id}`
**Auth**: token, musí být admin  
Smaže poplatek  

## `POST /fee/{fee_id}/activate`
**Params**: `first_season_id`, `league_id`  
**Auth**: token, musí být admin  
Přiřadí poplatek - počáteční sezónu od které je aktivní a ligu které se týká
