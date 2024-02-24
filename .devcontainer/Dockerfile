# Imagem Base Ubuntu 18.04
FROM ubuntu:18.04 as base_ubuntu_image

# Variaveis Ambiente
ENV SITE=localhost
ENV FOLDER=./.ubuntu/.devcontainer

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
WORKDIR /var/www/$SITE/httpdocs

# Instalando Composer 1.10
RUN curl -sS https://getcomposer.org/installer | php -- --version=1.10.0 --install-dir=/usr/local/bin --filename=composer

COPY --chown=www-data:www-data composer.json /var/www/$SITE/httpdocs/
COPY --chown=www-data:www-data composer.lock /var/www/$SITE/httpdocs/
COPY --chown=www-data:www-data package.json /var/www/$SITE/httpdocs/

# Instalando Dependências PHP do projeto
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --no-scripts --optimize-autoloader

# Instalando Dependências JS do projeto
RUN npm install --omit=dev

###############################################################################################
FROM depencies_ubuntu_image as symfony_ubuntu_image

# Pasta raiz do projeto
WORKDIR /var/www/$SITE/httpdocs

# Copiando Projeto para dentro do container e setando permissões
COPY --chown=www-data:www-data . /var/www/$SITE/httpdocs/

# Dando permissões ao Apache
RUN chown -R www-data:www-data /etc/apache2/sites-available
RUN chown -R www-data:www-data /etc/apache2/sites-enabled

# Inserindo apache2.conf no container
COPY --chown=www-data:www-data $FOLDER/docker/apache2.conf /etc/apache2/

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
COPY --chown=www-data:www-data $FOLDER/docker/fpm/ /etc/php/7.4/fpm/
COPY --chown=www-data:www-data $FOLDER/docker/php7.4-fpm.conf /etc/apache2/conf-available
COPY --chown=www-data:www-data $FOLDER/docker/serve-cgi-bin.conf /etc/apache2/conf-available

################################################################################################

# Permissao para rodar o script de criar httpd.conf
RUN chmod +x /var/www/$SITE/httpdocs/$FOLDER/docker/create-site.sh

# Script para criar o httpd.conf do apache2
RUN /bin/bash /var/www/$SITE/httpdocs/$FOLDER/docker/create-site.sh

# Instalando JWT Token
RUN mkdir -p var/jwt
RUN openssl genpkey -out var/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:top2503
RUN openssl pkey -in var/jwt/private.pem -out var/jwt/public.pem -pubout -passin pass:top2503

# Permissao para ler as JWT Keys
RUN chown -R www-data:www-data /var/www/$SITE/httpdocs/var/
RUN chown -R www-data:www-data /var/www/$SITE/httpdocs/var/jwt/

# Expondo portas
EXPOSE 80 3306 9000

# Iniciando Apache como Foreground
RUN service php7.4-fpm stop
RUN service apache2 stop

CMD ["/bin/bash", "-c", "service php7.4-fpm start && apachectl -DFOREGROUND"]