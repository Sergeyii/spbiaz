#!/usr/bin/env bash

source /app/vagrant/provision/common.sh

#== Provision script ==

info "Provision-script user: `whoami`"

info "Restart web-stack"
sudo service php7.1-fpm restart
sudo service nginx restart
#sudo service mysql restart
sudo service postgresql restart

sudo service redis-server restart