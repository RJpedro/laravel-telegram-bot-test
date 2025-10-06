#!/bin/bash
set -e

# ========================
# 🐳 Instalação do Docker
# ========================
echo "🔍 Verificando Docker..."
if ! command -v docker &> /dev/null; then
  echo "⚙️  Instalando Docker..."
  sudo apt-get update
  sudo apt-get install -y ca-certificates curl gnupg lsb-release

  sudo mkdir -p /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/$(. /etc/os-release; echo "$ID")/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
    https://download.docker.com/linux/$(. /etc/os-release; echo "$ID") \
    $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

  sudo apt-get update
  sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  sudo systemctl enable docker
  sudo systemctl start docker
  echo "✅ Docker instalado com sucesso!"
else
  echo "✅ Docker já está instalado."
fi

# ==============================
# 🧩 Verificação do Docker Compose
# ==============================
echo "🔍 Verificando Docker Compose..."

# Função para detectar o comando Compose
detect_compose_command() {
  if docker compose version &> /dev/null; then
    echo "docker compose"
  elif docker-compose version &> /dev/null; then
    echo "docker-compose"
  else
    echo ""
  fi
}

COMPOSE_CMD=$(detect_compose_command)

if [ -z "$COMPOSE_CMD" ]; then
  echo "⚙️  Nenhum comando Compose encontrado. Instalando Docker Compose..."
  sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  COMPOSE_CMD="docker-compose"
  echo "✅ Docker Compose instalado com sucesso!"
else
  echo "✅ Comando Docker Compose detectado: '$COMPOSE_CMD'"
fi

# =============================
# 🚀 Build e subida dos containers
# =============================
echo "🧹 Limpando cache de build e reconstruindo containers..."
$COMPOSE_CMD build --no-cache

echo "🚀 Subindo containers com $COMPOSE_CMD..."
$COMPOSE_CMD up -d

echo "⏳ Aguardando container PHP iniciar..."
sleep 5

PHP_CONTAINER=$(docker ps --filter "name=php-fpm" --format "{{.ID}}")

if [ -z "$PHP_CONTAINER" ]; then
  echo "❌ Container PHP-FPM não encontrado! Verifique o nome no docker-compose.yml."
  exit 1
fi

# ==============================
# 📦 Instalando bibliotecas no PHP-FPM
# ==============================
echo "📦 Instalando bibliotecas no container ($PHP_CONTAINER)..."
docker exec "$PHP_CONTAINER" bash -c "
    apt-get update &&
    apt-get install -y libzip-dev zip unzip curl gnupg ca-certificates &&
    docker-php-ext-install pdo pdo_mysql &&
    php -m | grep pdo_mysql && 
    chmod -R 777 /var/www/html/storage && 
    chmod -R 777 /var/www/html/bootstrap/cache
"

echo "✅ Extensões PHP instaladas e permissões ajustadas!"

# =============================
# 📦 Instalando Composer se não existir
# =============================
echo "🔍 Verificando Composer..."
docker exec "$PHP_CONTAINER" bash -c "
  if ! command -v composer &> /dev/null; then
    echo '⚙️ Instalando Composer...'
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
  else
    echo '✅ Composer já está instalado.'
  fi
  composer --version
"

# =============================
# 🧠 Configuração do Artisan
# =============================
if ! docker exec "$PHP_CONTAINER" test -f /var/www/html/artisan; then
  echo "❌ arquivo /var/www/html/artisan não encontrado dentro do container $PHP_CONTAINER."
  exit 1
fi

# =============================
# 🧩 Instalando Laravel Telescope
# =============================
echo "🔍 Instalando Laravel Telescope..."
docker exec "$PHP_CONTAINER" bash -lc "cd /var/www/html && composer require laravel/telescope"

echo "🔐 Gerando APP_KEY do Laravel..."
docker exec "$PHP_CONTAINER" bash -lc "cd /var/www/html && php artisan key:generate --ansi --no-interaction"

echo "🗄️ Executando migrations do Laravel..."
docker exec "$PHP_CONTAINER" bash -lc "cd /var/www/html && php artisan migrate:fresh --force --no-interaction --seed"

echo "🎉 Setup completo!"