#!/bin/bash

#Inserir variaveis ambientes dentro dos scripts .sh
envsubst < /var/www/buslab_backend/httpdocs/.container/docker/create-jwt.sh
envsubst < /var/www/buslab_backend/httpdocs/.container/docker/create-env.sh
envsubst < /var/www/buslab_backend/httpdocs/.container/docker/create-site.sh

bash /var/www/buslab_backend/httpdocs/.container/docker/create-jwt.sh

bash /var/www/buslab_backend/httpdocs/.container/docker/create-env.sh

bash /var/www/buslab_backend/httpdocs/.container/docker/create-site.sh

chown -R www-data:www-data /var/www/buslab_backend/httpdocs/var

service php7.4-fpm start

apachectl -DFOREGROUND

