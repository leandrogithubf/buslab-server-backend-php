# Imagem Base Ubuntu 18.04
FROM ubuntu:18.04 as base_ubuntu_image

# Instalando pacotes bases do Ubuntu
RUN apt-get update && export DEBIAN_FRONTEND=noninteractive
RUN apt-get install -y software-properties-common curl

# Configurar Timezone para America/Sao_Paulo
ENV TZ=America/Sao_Paulo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Instalando Node
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get -y install --no-install-recommends \
    zlib1g-dev libzip-dev zip libpng-dev libicu-dev \
    wget gpg gpg-agent unzip libxml2-dev nodejs \
    && npm install --global yarn

# Instalando Apache Server
RUN apt update 
RUN apt install -y apache2 
RUN apt install -y apache2-utils 

# Adicionando repositório PHP
RUN apt-get update && add-apt-repository ppa:ondrej/php

# Instalando Extensões do PHP
RUN apt-get update && \
    apt-get install -y --no-install-recommends \        
        php7.4 \
        php7.4-apcu \
        php7.4-bcmath \
        php7.4-cli \
        php7.4-common \
        php7.4-curl \
        php7.4-dev \
        php7.4-gd \
        php7.4-intl \
        php7.4-json \
        php7.4-mbstring \
        php7.4-mysql \
        php7.4-opcache \
        php7.4-pgsql \
        php7.4-readline \
        php7.4-sqlite3 \
        php7.4-xml \
        php7.4-zip \
        php7.4-redis \
        php7.4-soap \
        php7.4-pdo \
        libapache2-mod-php7.4 \
        libpq-dev        

# Instalando editor Nano
RUN apt-get update && \
    apt-get install -y nano

# Limpando dados
RUN apt clean
RUN rm -rf /var/lib/apt/lists/*

###############################################################################################
FROM base_ubuntu_image as depencies_ubuntu_image

# Pasta raiz do projeto
WORKDIR /var/www/buslab_backend/httpdocs

# Instalando Composer 1.10
RUN curl -sS https://getcomposer.org/installer | php -- --version=1.10.0 --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data composer.json /var/www/buslab_backend/httpdocs/
COPY --chown=www-data:www-data composer.lock /var/www/buslab_backend/httpdocs/
COPY --chown=www-data:www-data package.json /var/www/buslab_backend/httpdocs/

# Instalando Dependências PHP do projeto
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --no-scripts --optimize-autoloader

# Instalando Dependências JS do projeto
RUN npm install --omit=dev

###############################################################################################
FROM depencies_ubuntu_image as symfony_ubuntu_image

# Pasta raiz do projeto
WORKDIR /var/www/buslab_backend/httpdocs

# Permissão da pasta raiz do projeto
RUN chown -R www-data:www-data /var/www/buslab_backend/

# Copiando Projeto para dentro do container e setando permissões
COPY --chown=www-data:www-data . /var/www/buslab_backend/httpdocs/

# Dando permissões ao Apache
RUN chown -R www-data:www-data /etc/apache2/sites-available
RUN chown -R www-data:www-data /etc/apache2/sites-enabled

# Inserindo apache2.conf no container
COPY --chown=www-data:www-data .container/docker/apache2.conf /etc/apache2/

# Inserindo ports.conf no container
#COPY --chown=www-data:www-data $./.container/docker/ports.conf /etc/apache2/

# Desabilitando mpm_prefork module
RUN service apache2 stop
RUN a2dismod php7.4
RUN a2dismod mpm_prefork

# Habilitando apache modules
RUN a2enmod deflate
RUN a2enmod headers
RUN a2enmod rewrite
RUN a2enmod mpm_event
RUN a2enmod http2
RUN a2enmod proxy
RUN a2enmod socache_shmcb.load

###############################################################################################
RUN apt update && apt install -y libapache2-mod-fcgid php7.4-fpm

RUN a2enmod proxy_fcgi
RUN a2enconf php7.4-fpm

RUN chown -R www-data:www-data /etc/php/7.4/fpm
COPY --chown=www-data:www-data .container/docker/fpm/ /etc/php/7.4/fpm/
COPY --chown=www-data:www-data .container/docker/php7.4-fpm.conf /etc/apache2/conf-available
COPY --chown=www-data:www-data .container/docker/serve-cgi-bin.conf /etc/apache2/conf-available

################################################################################################

# Permissao para rodar o script de criar httpd.conf
RUN chmod +x /var/www/buslab_backend/httpdocs/.container/docker/create-site.sh
RUN chmod +x /var/www/buslab_backend/httpdocs/.container/docker/create-jwt.sh
RUN chmod +x /var/www/buslab_backend/httpdocs/.container/docker/create-env.sh
RUN chmod +x /var/www/buslab_backend/httpdocs/ENTRYPOINT.sh

# Aumenta limite de Memoria para o PHP
RUN sed -i 's/memory_limit = .*/memory_limit = 1024M/' /etc/php/7.4/apache2/php.ini
RUN sed -i 's/memory_limit = .*/memory_limit = 1024M/' /etc/php/7.4/cli/php.ini
RUN sed -i 's/memory_limit = .*/memory_limit = 1024M/' /etc/php/7.4/fpm/php.ini

RUN apt update && apt install -y gettext

# Permissão da pasta raiz do projeto
RUN chown -R www-data:www-data /var/www/buslab_backend/

RUN mkdir /var/www/buslab_backend/logs
RUN touch /var/www/buslab_backend/logs/access.log
RUN touch /var/www/buslab_backend/logs/error.log
RUN mkdir -p /var/www/buslab_backend/httpdocs/var
RUN mkdir -p /var/www/buslab_backend/httpdocs/var/cache

# Permissão da pasta raiz do projeto
RUN chown -R www-data:www-data /var/www/buslab_backend/

# Expondo portas
EXPOSE 80 3306 9000

# Iniciando Apache como Foreground
RUN service php7.4-fpm stop
RUN service apache2 stop

CMD ["/bin/bash", "-c", "/var/www/buslab_backend/httpdocs/ENTRYPOINT.sh"]
