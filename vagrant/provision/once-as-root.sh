#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Import script args ==

timezone=$(echo "$1")

#== Provision script ==

info "Provision-script user: `whoami`"

export DEBIAN_FRONTEND=noninteractive

info "Configure timezone"
timedatectl set-timezone ${timezone} --no-ask-password

info "Prepare root password for MySQL"
debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password \"''\""
debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password \"''\""
echo "Done!"

info "Add PHp 7.1 repository"
sudo add-apt-repository ppa:ondrej/php -y

info "Add Postgresql 9.5 repository"
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" >> /etc/apt/sources.list.d/pgdg.list'
wget -q https://www.postgresql.org/media/keys/ACCC4CF8.asc -O - | sudo apt-key add -

#info "Add Postgresql 9.4 repository"
#sudo add-apt-repository "deb https://apt.postgresql.org/pub/repos/apt/ trusty-pgdg main"
#wget --quiet -O - https://postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -

info "Update OS software"
sudo apt-get update
sudo apt-get upgrade -y

info "Install additional software"
#apt-get install -y php7.0-curl php7.0-cli php7.0-intl php7.0-mysqlnd php7.0-gd php7.0-fpm php7.0-mbstring php7.0-xml unzip nginx mysql-server-5.7 php.xdebug
sudo apt-get install -y php7.1-curl php7.1-cli php7.1-intl php7.1-mysql php7.1-gd php7.1-fpm php7.1-mbstring php7.1-zip php7.1-xml php-memcached unzip nginx mysql-server-5.6 php-xdebug php7.1-pgsql postgresql-9.5 postgresql-contrib-9.5

info "Install Redis Server"
sudo apt-get install -y redis-server

info "Configure MySQL"
#sudo sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
sudo sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/my.cnf
mysql -uroot <<< "CREATE USER 'root'@'%' IDENTIFIED BY ''"
mysql -uroot <<< "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'"
mysql -uroot <<< "DROP USER 'root'@'localhost'"
mysql -uroot <<< "FLUSH PRIVILEGES"
echo "Done!"

info "Configure PostgreSQL"
echo "host all all 0.0.0.0/0 trust" | sudo tee -a /etc/postgresql/9.5/main/pg_hba.conf
echo "host all all 192.168.83.137/32 trust" | sudo tee -a /etc/postgresql/9.5/main/pg_hba.conf

sudo sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/9.5/main/postgresql.conf
sudo sed -i "s/host \+all \+all \+127.0.0.1\/32 \+md5/host    all         all         127.0.0.1\/32         trust/g" /etc/postgresql/9.5/main/pg_hba.conf

# creating user
#sudo -u postgres psql -c "CREATE USER postgres WITH PASSWORD '';"
##--working:start
##sudo su - postgres
##psql -d template1 <<< "ALTER USER postgres WITH PASSWORD 'root';"
##\q
##--working:end
#sudo -u postgres psql -c "CREATE USER test WITH PASSWORD 'testtest';"
echo "Done!"

info "Configure PHP-FPM"
sudo sed -i 's/user = www-data/user = vagrant/g' /etc/php/7.1/fpm/pool.d/www.conf
sudo sed -i 's/group = www-data/group = vagrant/g' /etc/php/7.1/fpm/pool.d/www.conf
sudo sed -i 's/owner = www-data/owner = vagrant/g' /etc/php/7.1/fpm/pool.d/www.conf

cat << EOF > /etc/php/7.1/mods-available/xdebug.ini
zend_extension=xdebug.so
xdebug.remote_enable=1
xdebug.remote_connect_back=1
xdebug.remote_port=9000
xdebug.remote_autostart=1
xdebug.idekey="PHPSTORM"
EOF
echo "Done!"

info "Configure NGINX"
sudo sed -i 's/user www-data/user vagrant/g' /etc/nginx/nginx.conf
echo "Done!"

info "Enabling site configuration"
sudo ln -s /app/vagrant/nginx/app.conf /etc/nginx/sites-enabled/app.conf
echo "Done!"

info "Initailize databases for MySQL"
sudo mysql -uroot <<< "CREATE DATABASE spbiaz"
sudo mysql -uroot <<< "CREATE DATABASE spbiaz_test"
echo "Done!"

info "Initailize databases for PostgreSQL"
sudo -u postgres psql -c 'create database spbiaz;'
sudo -u postgres psql -c 'create database spbiaz_test;'
echo "Done!"

info "Install composer"
sudo chown -R `whoami`:admin /usr/local/bin
sudo curl -lsS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer