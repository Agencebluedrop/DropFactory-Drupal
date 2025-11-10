# DropFactory

Dropfactory is a solution designed to simplify the deployment and management of multiple Drupal websites. It provides a unique and intuitive interface that allows you to manage your entire portfolio of sites without any coding required. This centralized approach enables teams to focus on content creation and user experience, rather than the technical complexities of managing multiple Drupal installations.

**Project Objective: DropFactory**
The DropFactory project aims to provide Drupal users with an installation and administration console for low-code site factories. Inspired by the Aegir Hosting System, which we previously used, this project seeks to offer a similarly streamlined experience.
It was born from the collaboration between a team specializing in the Drupal CMS and a team with expertise in hosting and managing open-source solutions.

**Spirit**
DropFactory is presented as a free and open-source solution. As the initiators of its first release, we invite our partners, friends, and Drupal users to get involved in its development and maintenance. We are recruiting both users and testers. Feel free to participate, contact us, or submit issues on this repository.

**Contact Information**
The DropFactory project is the result of a collaboration between two partners actively involved in open source communities:
>bluedrop.fr SAS
>Registered with the Marseille Trade Register, with its headquarters located at 18/20 avenue Robert Schuman, Marseille.
>https://bluedrop.fr

>Evolix SARL

>Registered with the Marseille Trade Register under number 451 952 295.

>https://evolix.com

## Install

On a brand new Debian 12 system.

Requirements : 

* GNU/Linux Debian 12 (Bookworm) system dedicated to DropFactory
* Nginx
* MariaDB
* PHP-FPM
* ansible (`apt install ansible`)




System settings :

~~~
## Ensure /home is `exec`
# vim /etc/fstab

# systemctl daemon-reload
# mount -o remount /home

## Ensure that created users will have their home directory in 0750
# echo "DIR_MODE=0750" >> /etc/adduser.conf 
~~~

Create UNIX accounts : 

~~~
# adduser dropfactory_frontend
# adduser dropfactory_backend

# adduser www-data dropfactory_frontend
~~~

Ensure the backend user will be able to SSH as root localy : 

~~~
# su - dropfactory_backend
$ ssh-keygen -t ed25519
$ ^D

# cat ~dropfactory_backend/.ssh/id_ed25519.pub > .ssh/authorized_keys
# cat << UNILIKELY_EOF > /etc/ssh/sshd_config.d/zzz-evolinux-custom.conf

Match User root Address 127.0.0.1,::1
    AllowGroups root
    PubkeyAuthentication yes
    PasswordAuthentication no
    PermitRootLogin without-password

UNILIKELY_EOF

# sshd -t
# systemctl reload ssh

# su - dropfactory_backend
$ ssh root@localhost true
$ if [[ $? -eq 0 ]]; then echo "SSH OK"; fi
SSH OK
~~~

Fetch the code :

~~~
# su - dropfactory_backend
$ wget/gunzip TODO

# su - dropfactory_frontend
$ wget/gunzip TODO
~~~

Create the databases, their users & inject them :

~~~
# mysqladmin create dropfactory_backend
# mysqladmin create dropfactory_frontend

# gunzip < ~dropfactory_frontend/dropfactory_frontend/databases/symfony.gz | mysql -o dropfactory_frontend 
# gunzip < ~dropfactory_frontend/dropfactory_frontend/databases/remote.gz | mysql -o dropfactory_backend


# mysql
MariaDB [(none)]> GRANT ALL PRIVILEGES ON dropfactory_backend.* TO 'dropfactory_backend'@'localhost' IDENTIFIED BY 'PASSWORD';
MariaDB [(none)]> GRANT ALL PRIVILEGES ON dropfactory_frontend.* TO 'dropfactory_frontend'@'localhost' IDENTIFIED BY 'PASSWORD';

MariaDB [(none)]> GRANT SELECT on dropfactory_backend.* TO 'dropfactory_frontend'@'localhost';
MariaDB [(none)]> GRANT INSERT on dropfactory_backend.TaskBuffer TO 'dropfactory_frontend'@'localhost';
~~~

Configure the DropFactory (SQL Credentials, SSH Keys...) :

~~~
# su - dropfactory_frontend
$ cd dropfactory_frontend
$ cp -a .env .env.local
$ vim .env.local

$ composer install
$ php bin/console tailwind:build


$ ^D
# su - dropfactory_backend
$ cd dropfactory_backend
$ cp -a conf/config.ini.example conf/config.ini
$ vim conf/config.ini

$ cp -a ansible/vars/main.yml.example ansible/vars/main.yml
$ vim ansible/vars/main.yml

## This key will be your "pull/deploy" key. 
$ ssh-keygen -t ed25519 -f dropfactory_backend/conf/deploy_key.ed25519
~~~


Nginx vhost :

~~~
# cat << UNILIKELY_EOF > /etc/nginx/sites-available/dropfactory_frontend

upstream php_dropfactory {
        server unix:/home/dropfactory_frontend/php8.2-fpm.sock;
}

server {
    listen [::]:80;
    listen      80;

    server_name dropfactory-sandbox.evolix.eu;

    root /home/dropfactory_frontend/dropfactory_frontend/public;
    index index.htm index.html index.php;

    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    # Very rarely should these ever be accessed outside of your lan
    location ~* \.(txt|log)$ {
        deny all;
    }

    location ~ \..*/.*\.php$ {
        return 403;
    }

    location ~ ^/sites/.*/private/ {
        return 403;
    }

    # Block access to scripts in site files directory
    location ~ ^/sites/[^/]+/files/.*\.php$ {
        deny all;
    }

    # Allow "Well-Known URIs" as per RFC 5785
    location ~* ^/.well-known/ {
        allow all;
    }

    # Block access to "hidden" files and directories whose names begin with a
    # period. This includes directories used by version control systems such
    # as Subversion or Git to store control files.
    location ~ (^|/)\. {
        return 403;
    }

    location / {
        # try_files $uri @rewrite; # For Drupal <= 6
        try_files $uri /index.php?$query_string; # For Drupal >= 7
    }

    location @rewrite {
        rewrite ^/(.*)$ /index.php?q=$1;
    }

    # Don't allow direct access to PHP files in the vendor directory.
    location ~ /vendor/.*\.php$ {
        deny all;
        return 404;
    }

    # In Drupal 8, we must also match new paths where the '.php' appears in
    # the middle, such as update.php/selection. The r
    # and only allows this pattern with the update.php front controller.
    # This allows legacy path aliases in the form of
    # blog/index.php/legacy-path to continue to route to Drupal nodes. If
    # you do not have any paths like that, then you might prefer to use a
    # laxer rule, such as:
    #   location ~ \.php(/|$) {
    # The laxer rule will continue to work if Drupal uses this new URL
    # pattern with front controllers other than update.php in a future
    # release.
    location ~ '\.php$|^/update.php' {
        # Ensure the php file exists. Mitigates CVE-2019-11043
        #try_files $uri =404;
        fastcgi_split_path_info ^(.+?\.php)(|/.*)$;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        # Security note: If you're running a version of PHP older than the
        # latest 5.3, you should have "cgi.fix_pathinfo = 0;" in php.ini.
        # See http://serverfault.com/q/627903/94922 for details.
        include snippets/fastcgi-php.conf;
        # Block httpoxy attacks. See https://httpoxy.org/.
        fastcgi_param HTTP_PROXY "";
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_intercept_errors on;

        fastcgi_pass php_dropfactory;
    }

    # Fighting with Styles? This little gem is amazing.
    # location ~ ^/sites/.*/files/imagecache/ { # For Drupal <= 6
    location ~ ^/sites/.*/files/styles/ { # For Drupal >= 7
        try_files $uri @rewrite;
    }

    # Handle private files through Drupal. Private file's path can come
    # with a language prefix.
    location ~ ^(/[a-z\-]+)?/system/files/ { # For Drupal >= 7
        try_files $uri /index.php?$query_string;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        try_files $uri @rewrite;
        expires max;
        log_not_found off;
    }
    # Enforce clean URLs
    # Removes index.php from urls like www.example.com/index.php/my-page --> www.example.com/my-page
    # Could be done with 301 for permanent or other redirect codes.
    if ($request_uri ~* "^(.*/)index\.php(.*)") {
        return 307 $1$2;
    }
}

UNILIKELY_EOF


# ln -s /etc/nginx/sites-available/dropfactory_frontend.conf /etc/nginx/sites-enabled/
# nginx -t 
# systemctl reload nginx
~~~


PHP-FPM : 

~~~
# cat << UNILIKELY_EOF > /etc/php/8.2/fpm/pool.d/dropfactory_frontend.conf
[dropfactory_frontend]
user = dropfactory_frontend
group = dropfactory_frontend


listen = /home/dropfactory_frontend/php8.2-fpm.sock
listen.owner = dropfactory_frontend
listen.group = www-data

pm = ondemand
pm.status_path = /fpm-status
pm.max_children = 20
pm.process_idle_timeout = 10s
pm.max_requests = 1000

UNILIKELY_EOF

# php-fpm8.2 -t
# systemctl reload php8.2-fpm
~~~

