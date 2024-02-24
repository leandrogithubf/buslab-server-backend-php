Buslab v3

# Docker Configurações

## Servidor Produção ou UHML
Para rodar este projeto no servidor de produção ou UHML, utilizar o Dockerfile contido na pasta .ubuntu/.container.

**importante:** 
- Pasta '.ubuntu/.container': O arquivo Dockerfile uma variável ambiente chamada **$SITE**, este variável deve conter o domínio da aplicação. Ex: api.buslab.com.br;
- Pasta '.ubuntu/.container': O arquivo Dockerfile uma variável ambiente chamada **$FOLDER**, este variável deve conter o caminho da pasta raiz até a pasta que contém o dockerfile da produção;
- Pasta '.ubuntu/.container': Dentro da pasta docker, no arquivo **create-site.sh** possuímos duas variaéveis de ambiente, a variável $SITE deve conter o dominio da aplicação e a variável $DOMINIO_CONF deve conter o nome httpd.conf.

O mesmo dito para produção se adequa para o Dockerfile de desenvolvimento.

## Servidor Localhost
Para rodar este projeto no servidor local (locahost), deve-se copiar o arquivo Dockerfile e .dockerignore contidos na pasta .ubuntu/.container para a pasta raiz do projeto. Neste caso as variaveis ambientes estarão contidas nos arquivos .env.

Não será necessário alterar nada no Dockerfile para rodar localmente.

**importante:** O sistema está configurado para acessar o banco de dados da UHML, portanto caso vá usá-lo, deve-se ligar a VPN.

<br>

# Configurações Antigas (Digital Ocean)

# Monolith

**Importante:** Este projeto usa Symfony Framework ([versão 4.1][1]).

## Pré-requisitos:
### Configuração do JWT
insira a senha "top2503"
```
mkdir var/jwt
openssl genpkey -out var/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in var/jwt/private.pem -out var/jwt/public.pem -pubout
```

### NodeJS
```
wget -qO- https://deb.nodesource.com/setup_8.x | sudo -E bash -
sudo apt-get install -y nodejs
```

Note que a versão acima do node é a 8: talvez seja interessante pegar uma [versão mais atual][2].

### Gerenciadores de pacotes
```
sudo apt install yarn
sudo apt install npm
sudo npm install yarn -g
```

Se tiver problemas nos passos acima, tente com o apitude:
```
sudo aptitude install npm
```

## Criação de tema:
Os sitemas que usam o monolith precisam de um tema. Para usá-lo, crie um na pasta ./assets/themes/

O nome da pasta (nome do tema) deve ser inserido no arquivo ./assets/.env

Este arquivo de .env pode ser commitado, pois precisa ser propagado para os demais desenvolvedores de forma idêntica, por isso não está no .gitignore.

### Estrutura
A estrutura do tema base deve ser:
- index.js
- images/logo.png
- images/logo-login.png
- images/logo-small.png
- images/logo-menu.png
- images/icons/{ícones gerados nos sites apontados abaixo}

Os arquivos de logo serão convertidos da seguinte forma:
- images/logo.png => build/images/logo.png
- images/logo-login.png => build/images/logo-login.png
- images/logo-small.png => build/images/logo-small.png
- images/logo-menu.png => build/images/logo-menu.png

Então terão de ser usados, no desenvolvimento como o exemplo abaixo:
{{ asset('build/images/logo-menu.png') }}

#### Geradores de ícones:
- https://realfavicongenerator.net/
- https://realfavicongenerator.net/social/

## Ambiente de desenvolvimento:

### Instalações de dependências

Para o Back:
```
composer install
```

Para o Front:
```
yarn install
```

### Alterando arquivo .env:
#### Base de dados
Alterar valores: db_user, db_password e db_name para os padrões utilizados em seu banco
```
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
```
#### Informações do cliente:
As variáveis no definidas com COMPANY_ devem ser alteradas para que sejam refletidas as informações do cliente, sendo elas nome, slogan, descrição e imagens.

#### Envio de e-mail
As variáveos MAILER_URL e DELIVERY_ADDRESS já estão configuradas com valores de desenvolvimento.

As variáveis MAILER_DEFAULT_FROM_NAME e MAILER_DEFAULT_FROM_EMAIL são responsáveis pela informação utilziada durante o disparo do e-mail e precisam ser alinhadas com o cliente e alteradas.

#### Recaptcha
As variáveis do recaptcha já estão configuradas para o ambiente local (funcionando em localhost e 127.0.0.1).

### Criando banco de dados:
```
php bin/console doctrine:database:create
```

### Executando Migrations:
```
php bin/console doctrine:migration:migrate
```

### Executando aplicação:
Criando servidor PHP local:
```
php bin/console server:run
```

Monitorando alterações no front:
```
yarn encore dev --watch

```

### Observações Importantes:
O comando update:schema não é executado nesse projeto, após a criação do banco, executar as migrations que já fazem a criação das tabelas.

### Problemas encontrados ao instalar/atualizar projeto:
Problema: Seguinte exception lançada após o composer (Exception: Environment variable not found: SOME_VAR_NAME)
Solução: Copiar variáveis do arquivo .env.dist para o seu arquivo .env.


### Commands:
Abaixo segue uma lista do commands que devem ser rodados e configurados em cronjobs e sua periodicidade:


[1]: https://symfony.com/doc/4.1/setup.html
[2]: https://github.com/nodesource/distributions/blob/master/README.md#debinstall
