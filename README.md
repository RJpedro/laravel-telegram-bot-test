# Configuração do Ngrok e Webhook do Telegram

## 1️⃣ Instalar Ngrok
Adicione o repositório e instale o Ngrok:

```bash
wget https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-amd64.tgz && sudo tar xvzf ./ngrok-v3-stable-linux-amd64.tgz -C /usr/local/bin
```

## 2️⃣ Verificar instalação

Confira a versão instalada:

```bash
ngrok --version
```

## 3️⃣ Configurar Authtoken

Adicione seu token de autenticação do Ngrok:

```bash
ngrok config add-authtoken SEU_AUTHTOKEN
```

Substitua SEU_AUTHTOKEN pelo token fornecido no painel do Ngrok.

## 4️⃣ Rodar Ngrok

Inicie o túnel HTTP para a sua aplicação local (porta 8080):

```bash
ngrok http 8080
```

O Ngrok fornecerá uma URL pública que redireciona para o seu servidor local.

## 5️⃣ Definir Webhook do Telegram

### 5.1 Obter o token do bot
Para configurar o webhook, você precisará do token do seu bot:

#### 1. Abra o Telegram e busque pelo usuário **BotFather**.
#### 2. Envie o comando `/newbot` e siga os passos para criar um novo bot.
#### 3. Ao final, o BotFather fornecerá um token no formato: '123456789:AAEehxOePCEagYlW2ETSFfRp8QeVopjY8AI'

> Esse é o token que será usado para configurar o webhook.

### 5.2 Configurar o webhook
Use a URL pública fornecida pelo Ngrok e o token do bot:

```bash
curl -F "url=<NGROK-URL>/webhooks/telegram" https://api.telegram.org/bot<TELEGRAM-BOT-TOKEN>/setWebhook

Substitua <NGROK-URL> e <TELEGRAM-BOT-TOKEN> conforme necessário.
```

## 6️⃣ Configurar variáveis de ambiente

Para que a aplicação consiga se comunicar com o Telegram e receber os webhooks corretamente, defina as seguintes variáveis de ambiente no seu arquivo `.env`:

```dotenv
# Token do bot fornecido pelo BotFather
TELEGRAM_BOT_TOKEN="8277961765:AAEehxOePCEagYlW2ETSFfRp8QeVopjY8AI"

# URL do webhook para reembolsos (refund)
WEBHOOK_REFUND_URL="<URL-PUBLICA-NGROK>/webhooks/refund"

# URL do webhook para pagamentos (payment)
WEBHOOK_PAYMENT_URL="<URL-PUBLICA-NGROK>/webhooks/payment"
```

## 7️⃣ Levantar o sistema Docker

Com todas as configurações prontas (Ngrok, webhook e variáveis de ambiente), você pode iniciar o sistema utilizando Docker. Execute o script de setup:

```bash
./setup.sh
```

## 8️⃣ Processar jobs em background

Alguns jobs, como o reembolso via Telegram, precisam ser processados em background. Para isso:

#### 1. Abra um terminal separado.
#### 2. Acesse o container do PHP-FPM:

```bash
docker exec -it php-fpm bash
```

#### 3. Dentro do container, execute o worker do Laravel para processar os jobs:


```bash
php artisan queue:work
```

🔹 O comando queue:work ficará rodando, processando as filas em tempo real.

🔹 Para parar o worker, pressione CTRL + C.