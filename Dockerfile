FROM php:8.1-apache

# Установка необходимых расширений PHP
RUN docker-php-ext-install pdo pdo_mysql

# Включение модуля mod_rewrite для Apache
RUN a2enmod rewrite

# Копирование файлов проекта веб-директорию
COPY . /var/www/html/

# Установка прав на директории
RUN chown -R www-data:www-data /var/www/html/

# config-local.php больше не используется, все конфигурации в config.php

# Установка разрешений для конфигурационных файлов
RUN chmod 644 /var/www/html/config.php

# Установка порта по умолчанию
EXPOSE 80

# Команда запуска Apache в foreground
CMD ["apache2-foreground"]