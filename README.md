# Nová evidence členů ČALD  
[API dokumentace](api.md)  
[DB struktura](data/db.png)  

## Deployment
1. zbuildovat [Dockerfile](Dockerfile)
1. pustit (nevím jak přesně to kdo pouštíte)
## DB migrace
> Nepouští se automaticky při buildu, to by nebylo pěkné.
1. vlízt dovnitř cald-api containeru a pustit:
    ```bash
    $ cd /var/www/cald-database-backend/data
    $ pip3 install -r update.py.requirements.txt
    $ python3 update.py --username <DB_USER_NAME> --host <DB_HOST> # default cald a 127.0.0.1; na heslo se to zeptá
    ```
1. hotovo

## Development
> Momentálně funkční kombinace je WSL2 + Docker Desktop.  
> Pravděpodobně funguje i v čistě linuxovým env.  
> Na windows akorát není spouštěcí skript.

### Env setup
1. měj nainstalovanej docker
1. měj naklonovanej tenhle repozitář
1. měj [public/.env](public/.env) soubor obsahující tohle:
    ```conf
    DB_HOST=172.17.0.2
    ```
1. měj [data/src/dev.data.sql](data/src/dev.data.sql) a v něm schéma databáze + testovací data
1. to je myslím všechno

### Lokální server
1. [run.sh](./run.sh) (spustí jak dev mysql databázi, tak apache)
1. [http://localhost:8080](http://localhost:8080)
1. vesele si edituj zdrojáky, mělo by se to live měnit