# Bluedrop Factory

## Requirements

* PHP 8.3
* MySQL 5.7
* 2 databases :
    - symfony database
    - remote database (contains sites and tasks)

* The ```.env``` file contains the databases credentials for lando local development.
* On servers, the database credentials must be overridden in ```.env.local``` 
* override variables DATABASE_URL and REMOTE_DATABASE_URL 
