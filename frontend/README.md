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

* Configuring multiple database connexions :
https://symfony.com/doc/6.4/doctrine/multiple_entity_managers.html

## Local development

### Installation

```
lando start
lando composer i
lando php bin/console importmap:install
lando php bin/console tailwind:build
lando php bin/console asset-map:compile
```

```
lando db-import databases/symfony.gz
lando db-import databases/remote.gz -h remote_database
```

### Clear cache

```
lando php bin/console cache:clear
```

### DB Migrations

Si on ajoute une entité ou qu'on ajoute ou modifie ou supprime un champ d'une entité, il faut générer un fichier de migration pour mettre à jour la DB SYmfony.
Ensuite on exécute la migration en local puis sur les serveurs. 

```
# DB Symfony
lando php bin/console make:migration --formatted
lando php bin/console doctrine:migrations:migrate

# DB Evolix (seulement pour le dev)
lando php bin/console doctrine:migrations:diff --em=remote --formatted --namespace=DoctrineMigrationsRemote
lando php bin/console doctrine:migrations:migrate --em=remote "DoctrineMigrationsRemote\Version20241213161522"
```

### Frontend

**Asset Mapper**

https://symfony.com/doc/current/frontend/asset_mapper.html

All files inside of assets/ are made available publicly and versioned. You can reference the file assets/images/product.jpg in a Twig template with ```{{ asset('images/product.jpg') }}```. The final URL will include a version hash, like /assets/images/product-3c16d9220694c0e56d8648f25e6035e9.jpg.

* ```assets/app.js``` Your main JavaScript file;
* ```assets/styles/app.css``` Your main CSS file;
* ```config/packages/asset_mapper.yaml``` Where you define your asset "paths";
* ```importmap.php``` Your importmap config file.

**Déploiement en production**

For the prod environment, before deploy, you should run:

```
php bin/console asset-map:compile
```

This will physically copy all the files from your mapped directories to public/assets/ so that they're served directly by your web server.

**Importmaps & Writing JavaScript**

https://symfony.com/doc/current/frontend/asset_mapper.html#importmaps-writing-javascript

**Importing 3rd Party JavaScript Packages**

https://symfony.com/doc/current/frontend/asset_mapper.html#importing-3rd-party-javascript-packages

Exemple :

```
lando php bin/console importmap:require bootstrap
```

All packages in importmap.php are downloaded into an assets/vendor/ directory, which should be ignored by git (the Flex recipe adds it to .gitignore for you). You'll need to run the following command to download the files on other computers if some are missing:

```
php bin/console importmap:install
```

**Tailwind**

https://symfony.com/bundles/TailwindBundle/current/index.html

```
lando php bin/console tailwind:build --watch
```

** Formulaires **

https://symfony.com/doc/6.4/forms.html
https://symfony.com/doc/6.4/form/form_themes.html


## Dev/Staging Server

### Installation

```
composer i
php bin/console importmap:install
php bin/console tailwind:build --minify
php bin/console asset-map:compile
```

### Migrations DB Symfony

```
php bin/console doctrine:migrations:status
php bin/console doctrine:migrations:migrate
```

### Clear cache

```
php bin/console cache:clear
```

# Docs

* Symfony écrit seulement dans la table ```task_buffer``` de la DB d'Evolix.

* Il faut avoir le rôle ADMIN pour accéder aux méthodes du controller AdminController.

## Remote Tasks

### Platforms

- PLATFORM_ADD : Ajouter une plateforme
- PLATFORM_PULL : Git pull
- PLATFORM_VERIFY : Vérifier la plateforme
- PLATFORM_DISABLE : Désactiver la plateforme
- PLATFORM_ENABLE : Activer la plateforme

### Sites

- SITE_ADD : Ajouter un site
- SITE_VERIFY : Vérifier le site
- SITE_CLEAR_CACHE : Vider le cache Drupal
- SITE_RUN_CRON : Exécuter le Cron
- SITE_DB_UPDATES : Mettre à jour la base de données
- SITE_BACKUP : Backup
- SITE_CLONE : Cloner
- SITE_RESET_PASSWORD : Reset password
- SITE_DISABLE : Désactiver le site
- SITE_ENABLE : Activer le site (disponible uniquement si le site est désactivé)
- SITE_DELETE : Supprimer le site (disponible uniquement si le site est désactivé)

Tâches à faire apparaître dans le dropdown en bas de la liste des sites :
SITE_CLEAR_CACHE / SITE_RUN_CRON / SITE_DB_UPDATES

## Entities Structure

### Platform

- name [string]
- gitRepositoryURL [string]
- gitRepositoryBranch [string]
- status [string]
    * ENABLED
    * DISABLED

### Alias

- domain [string]
- site [ref : Site]

### Profile

- name [string]
- platform [ref : Platform]

### Site

- platform [ref : Platform]
- name [string]
- domain [string]
- aliases [ref : Alias]
- language [string]
    * FR
    * EN
    * ES
- install_profile [ref : Profile]
    * valeurs selon la plateforme
- image [string]
- status [string]
    * ENABLED
    * DISABLED

### Task

- created_at [date]
- started_at [date]
- ended_at [date]
- source_entity [site/platform id]
- action [task_name]
- parameters [json]
- results [json]
- logs [text]
- status [string]
    * PENDING
    * RUNNING
    * SUCCESS
    * WARNING
    * FAILED

### Task Buffer

- created_at [date]
- action [task_name]
- parameters [json]



