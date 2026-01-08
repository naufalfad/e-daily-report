# map-renderer-service/Dockerfile
FROM node:20-slim

# Jangan download chromium dari puppeteer
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium

# Install RUNTIME deps SAJA (no -dev)
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    chromium \
    libnss3 \
    libgbm1 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libasound2 \
    libpangocairo-1.0-0 \
    libxshmfence1 \
    fonts-liberation \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src/app

# Copy package files dulu biar layer cache kepake
COPY package*.json ./

# Install production deps ONLY
RUN npm install --omit=dev

# Copy source
COPY renderer.js .

EXPOSE 3000

CMD ["node", "renderer.js"]
