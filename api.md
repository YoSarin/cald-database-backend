# Dostupná API:
## `POST /user`
**Params**: `email`, `password`, `login`
**Auth**: -
Vytvoření nového uživatele, mělo by poslat mail s hashem na ověření mailu, ale ještě není implementovaný

## `POST /user/login`
**Params**: `login`, `password`
**Auth**: -
Přihlášení uživatele - vrátí token pomocí kterého se pak autenifikují requesty

## `GET /user/verify/{hash}`
**Params**: `hash`
**Auth**: -
Ověří hash (pro potvrzování uživatelů etc)

## `GET /list/{type}`
**Params**: `type`, `[filter]`, `[extend]`
Auth: token
Vrací seznam objektů daného typu.
**`type`**: list čeho chceme získat, momentálně podporované jsou: team, player, player_at_team, tournament a season
**`filter`**: je asociativní pole podle kterého se mají výsledky filtrovat, pro detaily jak se to dělá - zkoukni dokumentaci k DB frameworku medoo
**`extend`**: je li předán s hodnotou 1, tak aplikace vrátí rozšířené výsledky (místo referencovaných ID načte odkazované záznamy a rovnou je vrátí - používat s rozmyslem)

## `POST /team`
**Params**: `name`, `[city]`, `[www]`, `[email]`, `[founded_at]`
**Auth**: token
Vytvoří nový tým a dá aktuálně přihlášenému uživateli práva k jeho editaci
