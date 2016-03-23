# slim3-wine-api

# Installation

`git clone https://github.com/sferey/slim3-wine-api.git`

`cd slim3-wine-api`

Pour s'informer si le module pdo_sqlite est installé

`php -m | grep pdo_sqlite`

Installation de php5-sqlite

`sudo apt-get install php5-sqlite`

Démarrage de l'application

`php -S localhost:8080 -t api api/index.php`

Installation de la BDD

[http://localhost:8080/v1/install](http://localhost:8080/v1/install)

## URL disponnible dans l'api
| Method | URL | SQL |
| ----- | --------- | --- |
| GET | http://localhost:8080/ping |
| GET | http://localhost:8080/v1/wines | SELECT * FROM wine ORDER BY name |
| GET | http://localhost:8080/v1/wines/1 |	SELECT * FROM wine WHERE id=:id |
| PUT | http://localhost:8080/v1/wines/1 | UPDATE wine SET name=:name, grapes=:grapes, country=:country, region=:region, year=:year, description=:description WHERE id=:id |
| POST | http://localhost:8080/v1/wines	| INSERT INTO wine (name, grapes, country, region, year, description) VALUES (:name, :grapes, :country, :region, :year, :description) |
| DELETE | http://localhost:8080/v1/wines/1 | DELETE FROM wine WHERE id=:id |
| GET | http://localhost:8080/v1/wines/search?name=BODEGA |	SELECT * FROM wine WHERE name LIKE %BODEGA% ORDER BY name |

## Article sur les API

[Top 12 Best PHP RESTful Micro Frameworks (Pro/Con)](http://www.gajotres.net/best-available-php-restful-micro-frameworks/)

[Top 8 Java RESTful Micro Frameworks](http://www.gajotres.net/best-available-java-restful-micro-frameworks/)

