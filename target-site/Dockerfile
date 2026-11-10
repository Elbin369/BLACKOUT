FROM php:8.2-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache DocumentRoot to point to public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure DirectoryIndex: prioritize index.html as root entry
RUN echo "DirectoryIndex index.html index.php" >> /etc/apache2/apache2.conf

# Security: Deny web access to challenge-secrets and application directories if placed anywhere
RUN echo '<Directory "/var/www/html/challenge-secrets">\n    Require all denied\n</Directory>' >> /etc/apache2/apache2.conf
RUN echo '<Directory "/var/www/html/application">\n    Require all denied\n</Directory>' >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/public/uploads

EXPOSE 80

CMD ["apache2-foreground"]
