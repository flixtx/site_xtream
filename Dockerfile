FROM php:8.2-fpm

# Instala pacotes necessários
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite

# Clona o repositório
RUN git clone https://github.com/zoreu/site_xtream.git /var/www/html

# Copia configuração customizada do nginx
COPY default.conf /etc/nginx/sites-available/default

# Cria diretório de log
RUN mkdir -p /var/log/nginx

# Expondo a porta 7860
EXPOSE 7860

# Inicia PHP-FPM e Nginx
CMD service php8.2-fpm start && nginx -g "daemon off;"
