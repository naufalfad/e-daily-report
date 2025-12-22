# map-renderer-service/Dockerfile
# Menggunakan Node.js sebagai base image
FROM node:20-slim

# Instalasi dependencies sistem untuk Puppeteer
# Chromium membutuhkan beberapa library umum.
RUN apt-get update && apt-get install -y \
    chromium \
    libnss3 \
    libgbm-dev \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgtk-3-0 \
    libasound2 \
    libxshmfence-dev \
    && rm -rf /var/lib/apt/lists/*

# Atur Puppeteer agar menggunakan instalasi Chromium dari sistem
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

# Set working directory
WORKDIR /usr/src/app

# Salin package.json dan instal dependencies
COPY package*.json ./
RUN npm install

# Salin kode service
COPY renderer.js ./

# Jalankan service Node.js
EXPOSE 3000
CMD [ "npm", "start" ]
