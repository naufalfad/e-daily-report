# map-renderer-service/Dockerfile
FROM node:20-slim

# Install dependencies sistem untuk Chromium (Wajib untuk Puppeteer di Linux)
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
    fonts-liberation \
    && rm -rf /var/lib/apt/lists/*

# Config Environment untuk Puppeteer
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

WORKDIR /usr/src/app

# Copy dependency definition
COPY package*.json ./

# Install dependency Node.js
RUN npm install

# Copy source code
COPY renderer.js ./

# Expose port
EXPOSE 3000

# Start command
CMD [ "node", "renderer.js" ]
