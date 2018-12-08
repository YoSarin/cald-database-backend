Včechna create/modify API by měla vracet na výstupu json, obsahující pole `data`, s daty o vytvořeném/měněném objektu.  
  
# Akce  
+ [Vytvoření nového hráče](#post-player)  
+ [Změna dat hráče](#post-playerid)  
+ [Vytvoření nového týmu](#post-team)  
+ [Změna dat týmu](#post-teamid)  
+ [Přidání hráče do týmu](#post-teamteam_idplayerplayer_id)  
+ [Odebrání hráče z týmu](#delete-teamteam_idplayerplayer_id)  
+ [Přiřazení týmu agentovi](#post-teamteam_iduseruser_id)  
+ [Odebrání týmu agentovi](#delete-teamteam_iduseruser_id)  
+ [Historie hráče](#get-playerplayer_idhistory)  
+ [Zobrazení poplatků týmu za sezónu](#)  
+ [Zobrazení uživatelů majících přístup  týmu](#get-teamteam_idprivileges)  
+ [Admin API](admin.md)  
  
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
  
## `GET /user/me`  
**Auth**: token  
Vratí údaje o přihlášeném uživateli  
  
## `PUT /user/me`  
**Params**: `[login]`, `[email]`, `[password]`  
**Auth**: token  
Upraví údaje přihlášeného uživatele  
**`login`**: změnit přihlašovací jméno  
**`email`**: změnit kontaktní email  
**`password`**: změnít heslo  
  
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
  
## `POST /team/{id}`  
**Params**: `[name]`, `[city]`, `[www]`, `[email]`  
**Auth**: token, musí být správce týmu  
Upravuje data týmu  
  
## `POST /player`  
**Params**: `first_name`, `last_name`, `birth_date`, `sex`, `[email]`, `[phone]`, `[gdpr_consent]`, `[nationality_id]`  
**Auth**: token  
Vytvoří nového hráče  
**`first_name`**: Křestní jméno nového hráče  
**`last_name`**: Příjmení hráče  
**`birth_date`**: Datum narození, formát YYYY-MM-DD  
**`sex`**: Pohlaví (male/female)  
**`email`**: Mail hráče (nepovinný)  
**`phone`**: Telefon na hráče (nepovinné)  
**`gdpr_consent`**: Flag zda hráč poskytl souhlas se zpracováním osobních údajů (nepovinné)  
**`nationality_id`**: ID národnosti hráče (nepovinné)  
  
## `POST /player/{id}`  
**Params**: `[first_name]`, `[last_name]`, `[birth_date]`, `[sex]`, `[email]`, `[phone]`, `[gdpr_consent]`, `[nationality_id]`  
**Auth**: token, musí být správce týmu  
Upraví data hráče  
**`first_name`**: Křestní jméno nového hráče  
**`last_name`**: Příjmení hráče  
**`birth_date`**: Datum narození, formát YYYY-MM-DD  
**`sex`**: Pohlaví (male/female)  
**`email`**: Mail hráče (nepovinný)  
**`phone`**: Telefon na hráče (nepovinné)  
**`gdpr_consent`**: Flag zda hráč poskytl souhlas se zpracováním osobních údajů (nepovinné)  
**`nationality_id`**: ID národnosti hráče (nepovinné)  
  
## `GET /player/{player_id}/address`  
**Auth**: token, musí být správce týmu  
Zobrazí adresy asociované s hráčem  
  
## `POST /player/{player_id}/address`  
**Params**: `type`, `country`, `city`, `[street]`, `[zip_code]`, `[district]`, `[orientation_number]`, `[descriptive_number]`  
**Auth**: token, musí být správce týmu  
Přidá ke hráči novou adresu  
**`type`**: typ adresy: [`'permanent residence'`, `'residence in czechia'`]  
  
## `POST /player/{player_id}/address/{address_id}`  
**Params**: `[country]`, `[city]`, `[street]`, `[zip_code]`, `[district]`, `[orientation_number]`, `[descriptive_number]`  
**Auth**: token, musí být správce týmu  
Upraví hráčovu adresu  
  
## `DELETE /player/{player_id}/address/{address_id}`  
**Auth**: token, musí být správce týmu  
Smaže hráči adresu  
  
  
## `POST /team/{team_id}/player/{player_id}`  
**Params**: `team_id`, `player_id`, `season_id`  
**Auth**: token, musí být správce týmu  
Přidá existujícího hráče do týmu jako člena. Hráč nemůže být členem dvou týmů současně.  
**`season_id`**: ID první sezóny kdy hráč za tým hrál  
  
## `DELETE /team/{team_id}/player/{player_id}`  
**Params**: `team_id`, `player_id`, `season_id`  
**Auth**: token, musí být správce týmu  
Odstraní existujícího hráče z týmu  
**`season-id`**: ID poslední sezóny, kdy hráč byl členem daného týmu  

## `GET /team/{team_id}/privileges`  
**Params**: `team_id`  
**Auth**: token, musí být správce týmu  
Zobrazí kdo má jaké oprávnění k danému týmu  
  
## `POST /team/{team_id}/user/{user_id}`  
**Params**: `team_id`, `player_id`, `privilege`  
**Auth**: token, musí být správce týmu  
Dá uživateli práva k týmu  
**`privilege`**: právo které chceme dát uživateli (view|edit)  
  
## `DELETE /team/{team_id}/user/{user_id}`  
**Params**: `team_id`, `player_id`, `privilege`  
**Auth**: token, musí být správce týmu  
Odebere uživateli práva k týmu  
**`privilege`**: právo které chceme odebrat uživateli (view|edit)  
  
## `GET /player/{player_id}/history`  
**Params**: `player_id`  
**Auth**: token  
Vrátí historii uživatele - sezóny kdy byl členem týmu a ČALD turnaje, kterých se v těchto sezónách účastnil  
  
## `GET /team/{team_id}/season/{season_id}/fee`  
**Params**: `team_id`, `season_id`  
**Auth**: token, musí být správce týmu  
Zobrazí poplatky které by měl tým za danou sezonu zaplatit ČALDu  

## `POST /roster/{roster_id}/finalize`  
**Params**: `roster_id`  
**Auth**: token, musí být správce týmu pořádajícího turnaj  
Označí danou soupisku za finální - nebude možné do ní provádět další změny  

## `POST /roster/{roster_id}/open`  
**Params**: `roster_id`  
**Auth**: token, musí být správce týmu pořádajícího turnaj  
Označí danou soupisku za nefinální - bude možné do ní provádět změny  
