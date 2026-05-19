FROM php:8.3-cli-bookworm

ENV APP_ENV=production \
    PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    EASYOCR_USE_API=false \
    EASYOCR_CLI_ENABLED=true \
    EASYOCR_TIMEOUT=300 \
    EASYOCR_PYTHON_PATH=python3

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        default-mysql-client \
        git \
        libfreetype6-dev \
        libglib2.0-0 \
        libgl1 \
        libgomp1 \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        python-is-python3 \
        python3 \
        python3-pip \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd mbstring pcntl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --optimize-autoloader

COPY scripts/requirements-ocr-railway.txt /app/scripts/requirements-ocr-railway.txt
RUN python3 -m pip install --break-system-packages --upgrade pip setuptools wheel \
    && python3 -m pip install --break-system-packages --no-cache-dir torch torchvision --index-url https://download.pytorch.org/whl/cpu \
    && python3 -m pip install --break-system-packages --no-cache-dir -r /app/scripts/requirements-ocr-railway.txt

COPY . .

RUN mkdir -p bootstrap/cache storage/framework/sessions storage/framework/views storage/framework/cache storage/framework/testing storage/logs \
    && chmod -R a+rw bootstrap/cache storage \
    && composer dump-autoload --no-dev --optimize --no-interaction

EXPOSE 8080

CMD ["sh", "railway/start.sh"]
