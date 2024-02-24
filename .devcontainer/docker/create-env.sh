#!/bin/bash

touch /var/www/$ROOT_NAME/httpdocs/.env

echo "APP_ENV=$APP_ENV" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "APP_SECRET=$APP_SECRET" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "LOCALE_LANG=$LOCALE_LANG" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "DATABASE_URL=$DATABASE_URL" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "MAILER_URL=$MAILER_URL" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "DELIVERY_ADDRESS=$DELIVERY_ADDRESS" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "COMPANY_NAME='Cliente'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_SLOGAN='Slogan'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_DESCRIPTION='Slogan'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_DEFAULT_LOGO='build/images/logo.png'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_SMALL_LOGO='build/images/logo-small.png'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_MENU_LOGO='build/images/logo-menu.png'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_LOGIN_LOGO='build/images/logo-menu.png'" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "COMPANY_FRONT_ENABLED=true" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_FRONT_OFFLINE_ENABLED='false'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_FRONT_TILECOLOR='#2db0d0'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_FRONT_THEME_COLOR='#0d3394'" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "COMPANY_FRONT_BACKGROUND_COLOR='#2db0d0'" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "RECAPTCHA_SITEKEY=$RECAPTCHA_SITEKEY" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "RECAPTCHA_SECRETKEY=$RECAPTCHA_SECRETKEY" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "MAILER_DEFAULT_FROM_EMAIL=$MAILER_DEFAULT_FROM_EMAIL" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "MAILER_DEFAULT_FROM_NAME=$MAILER_DEFAULT_FROM_NAME" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "DEFAULT_HOST=$DEFAULT_HOST" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "DEFAULT_HOST_SCHEMA=$DEFAULT_HOST_SCHEMA" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "JWT_SECRET_KEY=$JWT_SECRET_KEY" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "JWT_PUBLIC_KEY=$JWT_PUBLIC_KEY" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "JWT_PASSPHRASE=$JWT_PASSPHRASE" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "JWT_TTL=$JWT_TTL" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "CORS_ALLOW_ORIGIN=$CORS_ALLOW_ORIGIN" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "GOOGLE_MAPS_APIKEY=$GOOGLE_MAPS_APIKEY" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "USER_DEV=$USER_DEV" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "EMAIL_DEV=$EMAIL_DEV" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "ROLE_ID_ROOT_DEV=$ROLE_ID_ROOT_DEV" >> /var/www/$ROOT_NAME/httpdocs/.env
echo "ROLE_ID_SYSTEM_DEV=$ROLE_ID_SYSTEM_DEV" >> /var/www/$ROOT_NAME/httpdocs/.env

echo "TRUSTED_PROXIES=$TRUSTED_PROXIES" >> /var/www/$ROOT_NAME/httpdocs/.env

