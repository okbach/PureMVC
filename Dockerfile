# استخدام صورة PHP مع Apache
FROM php:8.2-apache

# تثبيت الامتدادات المطلوبة لـ MySQL و Composer
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# نسخ ملفات المشروع إلى مجلد Apache
COPY . /var/www/html

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html

# تعيين مجلد العمل
WORKDIR /var/www/html

# فتح المنفذ 80 لخادم الويب
EXPOSE 80

# تشغيل Apache
CMD ["apache2-foreground"]
