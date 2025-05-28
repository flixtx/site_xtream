FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    git \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite

RUN git clone https://github.com/zoreu/site_xtream.git /tmp/site_xtream && \
    rm -rf /var/www/html/* && \
    mv /tmp/site_xtream/* /var/www/html/

RUN sed -i '/^user/d' /etc/nginx/nginx.conf && \
    sed -i '/access_log/d' /etc/nginx/nginx.conf && \
    sed -i '/error_log/d' /etc/nginx/nginx.conf

RUN mkdir -p /var/www/html/nginx_temp/body \
             /var/www/html/nginx_temp/proxy \
             /var/www/html/nginx_temp/fastcgi \
             /var/www/html/nginx_temp/uwsgi \
             /var/www/html/nginx_temp/scgi \
             /var/www/html/nginx_logs && \
    chmod -R 777 /var/www/html/nginx_temp /var/www/html/nginx_logs

RUN sed -i '/http {/a \
    client_body_temp_path /var/www/html/nginx_temp/body;\
    proxy_temp_path /var/www/html/nginx_temp/proxy;\
    fastcgi_temp_path /var/www/html/nginx_temp/fastcgi;\
    uwsgi_temp_path /var/www/html/nginx_temp/uwsgi;\
    scgi_temp_path /var/www/html/nginx_temp/scgi;\
    access_log /var/www/html/nginx_logs/access.log;\
    error_log /var/www/html/nginx_logs/error.log warn;' /etc/nginx/nginx.conf

RUN printf 'server {\n\
    listen 7860;\n\
    server_name localhost;\n\
    root /var/www/html;\n\
    index index.php index.html index.htm;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \\.php$ {\n\
        include fastcgi_params;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
    }\n\
\n\
    location ~ /\\.ht {\n\
        deny all;\n\
    }\n\
}\n' > /etc/nginx/sites-available/default


# Corrigir permissão e diretório /run para nginx pid
RUN mkdir -p /run && chmod 755 /run
RUN sed -i 's|^pid .*|pid /var/www/html/nginx.pid;|' /etc/nginx/nginx.conf || echo 'pid /var/www/html/nginx.pid;' >> /etc/nginx/nginx.conf

EXPOSE 7860

CMD ["sh", "-c", "php-fpm & nginx -g 'daemon off;'"]
#CMD sh -c "ps aux | grep php-fpm; cat /usr/local/etc/php-fpm.d/www.conf | grep listen; exec php-fpm & exec nginx -g 'daemon off;'"
