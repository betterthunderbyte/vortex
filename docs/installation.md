### Anforderungen

* Debian 10 (Buster) Server
* WAN-Zugang

### Rechnerinformationen

* Rechnername: vortex
* Standart Benutzer: api
* Speicherplatz: 16 GB
* RAM: 1 GB
* CPU-Cores: 2

### Installation

```text

adduser vortex

```

```text
apt update
apt upgrade
``` 

### Git

```text

apt install zip

```

```text
apt install git

```

#### Apache 2

```text
apt install apache2
``` 

#### Datei /etc/apache2/envvars öffnen

* export 'APACHE_RUN_USER=www-data' in 'export APACHE_RUN_USER=vortex' umbenennen
* export 'APACHE_RUN_GROUP=www-data' in 'export APACHE_RUN_GROUP=vortex' umbenennen
 
#### Datei /etc/apache2/apache2.conf öffnen

```apacheconfig
<Directory />
       Options FollowSymLinks
       AllowOverride None
       Require all denied
</Directory>

<Directory /usr/share>
       AllowOverride None
       Require all granted
</Directory>
```

auskommentieren.

```apacheconfig
<Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>
```

änderrn in 

```apacheconfig

<Directory /home/vortex/www>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
</Directory>

```

#### Datei /etc/apache2/sites-available/000-default.conf öffnen

```apacheconfig

DocumentRoot /var/www/html

```

in 
 
 ```apacheconfig
 
DocumentRoot /home/vortex/www
 
 ```

ändern.

Als letztes den Ordner /home/api/www erstellen als User vortex

```text
mkdir /home/api/www
```

#### Rewrite Engine aktivieren

```text
a2enmod rewrite
```
 
### Apache2 befehle

```text
systemctl start apache2

systemctl restart apache2

systemctl stop apache2

systemctl reload apache2

systemctl status apache2
```

### Mariadb installieren

```text
apt install mariadb-server
```

#### Nutzer anlegen

```text
mariadb 
```

```sql

CREATE USER 'vortex'@'localhost' IDENTIFIED BY '123asd!';
CREATE USER 'vortex'@'%' IDENTIFIED BY '123asd!';

GRANT ALL ON *.* TO 'vortex'@'localhost';
GRANT ALL ON *.* TO 'vortex'@'%';

flush privileges;

update mysql.user set plugin='' where user='vortex'; 
flush privileges;

grant all on *.* to vortex@localhost identified by '123asd!' with grant option;
flush privileges;

exit();

```

### PHP installieren

```text
apt install php libapache2-mod-php php-cli php-fpm php-json php-pdo php-mysql php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath
apt install php-intl  php-imap php-xml php-mbstring php-curl
```

### Composer Installieren

```text
apt install curl
```

```text
curl -sS https://getcomposer.org/installer | php
```

```text
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### SSL aktivieren

ToDo weitermachen
https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-debian-10
https://www.digitalocean.com/community/tutorials/how-to-create-a-ssl-certificate-on-apache-for-debian-8
https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-debian-9
````text

openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /home/vortex/vortex.key -out /home/vortex/vortex.crt

Country Name (2 letter code) [AU]:DE
State or Province Name (full name) [Some-State]:Niedersachsen
Locality Name (eg, city) []:Oldenburg
Organization Name (eg, company) [Internet Widgits Pty Ltd]:Vortex Framework
Organizational Unit Name (eg, section) []:MVC
Common Name (e.g. server FQDN or YOUR name) []:192.168.1.1
Email Address []:vortex@vortex.de

a2enmod ssl
a2ensite default-ssl

````

So muss die Datei /etc/apache2/conf-available/ssl-params.conf aussehen
```text
SSLCipherSuite EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH
SSLProtocol All -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
SSLHonorCipherOrder On
# Disable preloading HSTS for now.  You can use the commented out header line that includes
# the "preload" directive if you understand the implications.
# Header always set Strict-Transport-Security "max-age=63072000; includeSubDomains; preload"
Header always set X-Frame-Options DENY
Header always set X-Content-Type-Options nosniff
# Requires Apache >= 2.4
SSLCompression off
SSLUseStapling on
SSLStaplingCache "shmcb:logs/stapling-cache(150000)"
# Requires Apache >= 2.4.11
SSLSessionTickets Off
```

Die Datei /etc/apache2/sites-available/ssl-default.conf muss so aussehen
```text
<IfModule mod_ssl.c>
        <VirtualHost _default_:443>
                ServerAdmin your_email@example.com
                ServerName server_domain_or_IP

                DocumentRoot /var/www/html

                ErrorLog ${APACHE_LOG_DIR}/error.log
                CustomLog ${APACHE_LOG_DIR}/access.log combined

                SSLEngine on

                SSLCertificateFile      /etc/ssl/certs/apache-selfsigned.crt
                SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key

                <FilesMatch "\.(cgi|shtml|phtml|php)$">
                                SSLOptions +StdEnvVars
                </FilesMatch>
                <Directory /usr/lib/cgi-bin>
                                SSLOptions +StdEnvVars
                </Directory>

        </VirtualHost>
</IfModule>


```


### Entwicklertools installieren

#### PHP-Storm Datenbank Verbindung

In der Datei '/etc/mysql/my.cnf' diese Zeilen am Ende einfügen

```apacheconfig

[mysqld]

skip-networking=0

skip-bind-address

```

#### XDebug Installieren

```text
apt install php-xdebug
```

#### XDebug Einrichten

```text

[xdebug]
xdebug.remote_autostart=1
xdebug.remote_enable=1
xdebug.remote_host="192.168.252.178"
xdebug.remote_port=9000
xdebug.idekey="PHPSTORM"
xdebug.remote_log=/home/vortex/www/log/xdebug.log
xdebug.remote_connect_back=1

```

bei 'xdebug.remote_host' die IP-Adresse eingeben der Debuggen soll, also eigener Rechner.

Achtung! Wenn der Ordner bei 'xdebug.remote_log' nicht existiert funktioniert XDebug nicht!



