FROM node:20

# Install PHP for Laravel Wayfinder plugin
RUN apt-get update && apt-get install -y \
    php8.2-cli \
    php8.2-xml \
    php8.2-mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy package files first for better caching
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application
COPY . .
