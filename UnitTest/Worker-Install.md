# Worker

Everything with --no-install-recommends

- ssh (for Putty Copy/Paste)
- nano (for Editing)

--------

## Install Worker

- apache2 (WebServer)
- vsftpd (FtpServer)
- libapache2-mod-php7.0 (PHP)

## Install Development

- memcached (MemcachedServer)
- mysql-server (MySqlServer)

## Install PHP-Modules

- php7.0-mysql
- php7.0-curl
- php7.0-xml
- php7.0-mbstring
- php7.0-gmp

- php5-apcu
- php5-memcached

## Configuration WebServer

- a2enmod rewrite
- a2enmod headers

- File: /etc/apache2/apache2.conf


    <Directory /var/www/>
            Options -Indexes +FollowSymLinks
            AllowOverride All
            Require all granted
    </Directory>

- File: /etc/apache2/sites-available/000-default.conf


    DocumentRoot /var/www


## Configuration FtpServer

- File: /etc/vsftpd.conf

    local_enabled=YES
    write_enabled=YES
    local_umask=002
    chroot_local_user=YES
    allow_writeable_chroot=YES
    local_root=/var/www


## Configuration FileSystem

- chown -R administrator:www-data /var/www
- chmod -R 0775 /var/www


## Install Application

- Connect via FTP (and remove /html/)
- Copy new Files
