#!/bin/bash

mkdir -p /var/www/buslab_backend/httpdocs/var/jwt
openssl genpkey -out /var/www/buslab_backend/httpdocs/var/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:$JWT_PASSPHRASE
openssl pkey -in /var/www/buslab_backend/httpdocs/var/jwt/private.pem -out /var/www/buslab_backend/httpdocs/var/jwt/public.pem -pubout -passin pass:$JWT_PASSPHRASE