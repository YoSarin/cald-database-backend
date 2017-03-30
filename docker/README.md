= Docker
== Build kontejneru
`sudo docker build -t cald-database-backend .`  

== Spuštění
`sudo docker run -d -e DB_HOST=172.17.0.1 cald-database-backend`  
Další env proměnný jsou:  
+ `DB_USER`  
+ `DB_NAME`  
+ `DB_PASS`  
+ `DB_TYPE`  
