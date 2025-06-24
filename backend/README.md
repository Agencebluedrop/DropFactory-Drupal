
# Dropfactory - Backend

## Setup

TODO : A multi step setup for the backend part + link to the frontend part : 

1. Create a unix account for the backend, a SQL database & database account
2. Deploy the code & inject the database
3. Generate a ssh key for the backend to configure the node (create platform, sites...) + allow it to connect to root locally
4. Generate a ssh key for platform deployment (ie: git clone via SSH of the platform code)
5. Create the dropfactory configs files (use the example as scaffolding)

## Configuration

The backend part can be configured in the following files:

* `src/conf/config.ini` (ref: `src/conf/config.ini.example`) - Dropfactory main settings (like database)
* `src/ansible/vars/main.yml` (ref: `src/ansible/vars/main.yml.example`) - Additionnal config

In the YAML file used by Ansible, you can setup the SSH Keys that will be allowed to connect to the accounts created by the system.

Example: 
```yaml
admin_ssh_keys: 
- ssh-ed25519 AAAA....
```

## How It works

### Overview 

A SQL database is used to store the status of the hosting platform (platform list, site list...). 
This SQL database is consumed by the web interface (or frontend) for users.

The web interface will add tasks in the queue (table `TaskBuffer`). Those tasks will be processed by the backend.
The backend works with a frequent execution of the file `src/cron.php` (with cron).

The script `src/cron.php` will, each time, process the pending tasks in the `TaskBuffer` table and update status of the elements manipulated.
Some tasks may do operations on the platforms/sites but still not change their status (like cache cleaning, git pull, etc...)

### Tasks

Tasks are added by the web interface into the `TaskBuffer` table.

When `src/cron.php` is executed (manually, or by cron) it will sequencially handle the tasks pending in the `TaskBuffer` table.
The tasks will be read, validated and moved to the `Task` table before being executed (all by instanciating a `Task` object)

New tasks will be then moved to the `Task` table and then executed. 
The task status is updated to a RUNNING state before execution and to a final state (either SUCCESS, WARNING, FAILED) when it ends.
As of now, a failed/crash task that was moved into the `Task` table won't be finised/cleaned


#### Adding a new task type

In `src/lib/task.php`, in the `do()` function. Adding a task is adding a new case in the `swich` (+ the associated code)
The action *should* correspond to an action defined in the frontend side of the application 

See: [dropfactory-frontend:src/branch/develop/src/Entity/Remote/Task.php#L12](https://gitea-ebizproduction.evolix.org/ebiz/bluedrop-factory/src/branch/develop/src/Entity/Remote/Task.php#L12) for the list of tasks

### Ansible

Ansible is manipulated by the `Ansible` class.

A new class is instanciated to prepare a playbook run. Variables can be added by the `add_var` method.
The variables will be json encoded into a file that will be added as an extra-vars file for the playbook execution

```php
$ansible = new Ansible('my_playbook.yml'); // The playbook has to be located in ./src/ansible/

$ansible->add_var('foo', 'bar'); // Add variables that will be submitted as an extra-vars file 

$ansible->run(); // Execute the playbook
```

### Platform

A platform is a Drupal codebase that will hold one or more websites.

It will be downloaded via git in `~platform_<PLATFORM_ID>/platorm`. Drush will be installed along-side for easy administration.

Each platform is hosted inside it's own UNIX account.


### Sites

A site, is a Drupal website in a given platform.

Each site has a dedicated database, and Nginx vhost


### Naming scheme

* Platform name : What the user choses => It's only used by the web interface.
* Unix account (for the platform) `platform_<PLATFORM_ID>`
  * This same name is used for the PHP-FPM pool name
* Vhost file (for the site) `platform_<PLATFORM_ID>_site_<SITE_ID>.conf`
  * This same name is used for the database & sql user

## Tips

### (CLI) Easy ansible log access

```bash
task_id=42
mysql --skip-column-names --batch -o forkaegir -e "select logs from Task where id = '$task_id';" | jq
```

### (CLI) Test data / Quickly add platforms/websites

You can use the files in the folder `src/test/` as a quick way to add tasks in the TaskBuffer table.

* `test_add_platform.php` - This will create a foo<TIMESTAMP> platform
* `test_add_site_ok.php` - This will create a bar<TIMESTAMP> website (in Platform B)
* `test_add_site_fail.php` - This will try to create a bar<TIMESTAMP> website in an unknown platform. Leading to the task failure
