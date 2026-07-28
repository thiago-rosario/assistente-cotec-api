#!/bin/bash
# INSTALADOR EDITACODIGO API PUBLICA (BAILEYS) - macOS

set +e

INSTALL_PATH="$HOME/editacodigo-publica"
PROCESS_NAME="EDITACODIGO PUBLICA"

echo "==============================================="
echo "   INSTALADOR EDITACODIGO API PUBLICA (BAILEYS)"
echo "   (macOS)"
echo "==============================================="

###########################################
# 0. VERIFICA HOMEBREW
###########################################
if ! command -v brew >/dev/null 2>&1; then
  echo "📦 Homebrew não encontrado — instalando..."
  /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
  eval "$(/opt/homebrew/bin/brew shellenv 2>/dev/null || /usr/local/bin/brew shellenv)"
fi

###########################################
# 1. REMOVE SOMENTE ESTE PROCESSO (SE EXISTIR)
###########################################
echo "🔎 Verificando processo PM2..."

if command -v pm2 >/dev/null 2>&1; then
  if pm2 list | grep -q "$PROCESS_NAME"; then
    echo "Removendo processo antigo $PROCESS_NAME..."
    pm2 delete "$PROCESS_NAME" || true
    pm2 save || true
  else
    echo "Processo não existe, continuando..."
  fi
else
  echo "PM2 ainda não instalado, continuando..."
fi

###########################################
# 2. REMOVE SOMENTE A PASTA DESTA INSTALAÇÃO
###########################################
echo "🔍 Verificando pasta antiga..."

if [ -d "$INSTALL_PATH" ]; then
  echo "🗑️  Removendo pasta antiga..."
  rm -rf "$INSTALL_PATH"
else
  echo "✅ Pasta não existe, continuando..."
fi

###########################################
# 3. COLETA VARIÁVEIS (SE NÃO VIEREM POR ENV)
###########################################
if [ -z "$PORTA" ]; then
  read -p "Digite a PORTA (ex: 443): " PORTA
fi

if [ -z "$TOKEN" ]; then
  read -p "Digite o TOKEN (chave_api do cliente): " TOKEN
fi

if [ -z "$WEBHOOK_FUNCOES" ]; then
  read -p "Digite o WEBHOOK_FUNCOES: " WEBHOOK_FUNCOES
fi

if [ -z "$WEBHOOK_MENSAGENS" ]; then
  read -p "Digite o WEBHOOK_MENSAGENS: " WEBHOOK_MENSAGENS
fi

if [ -z "$WEBHOOK_VALIDATE" ]; then
  read -p "Digite o WEBHOOK_VALIDATE: " WEBHOOK_VALIDATE
fi

###########################################
# 4. INSTALA / ATUALIZA NODE 20+ E PM2
###########################################
NODE_MAJOR=0
if command -v node >/dev/null 2>&1; then
  NODE_MAJOR=$(node -e "process.stdout.write(process.versions.node.split('.')[0])" 2>/dev/null || echo "0")
fi

if [ "$NODE_MAJOR" -lt 20 ] 2>/dev/null; then
  echo "📦 Node.js $NODE_MAJOR detectado — instalando Node.js 20 via Homebrew..."
  brew install node@20
  brew link --overwrite --force node@20
else
  echo "✅ Node.js $NODE_MAJOR já compatível"
fi

if ! command -v pm2 >/dev/null 2>&1; then
  echo "📦 Instalando PM2..."
  npm install -g pm2
fi

echo "✅ Node.js versão: $(node -v)"
echo "✅ NPM versão: $(npm -v)"

###########################################
# 5. CRIA ESTRUTURA DE PASTAS
###########################################
echo "📁 Criando estrutura..."
SSL_PATH="$INSTALL_PATH/ssl"
mkdir -p "$SSL_PATH"
mkdir -p "$INSTALL_PATH/sessions"

###########################################
# 6. BAIXA INDEX.JS
###########################################
echo "⬇️  Baixando index.js..."
curl -fsSL -o "$INSTALL_PATH/index.js" "https://raw.githubusercontent.com/edita-codigo/editacodigo-api-publica/master/VPS/index.js"

if [ ! -f "$INSTALL_PATH/index.js" ]; then
  echo "❌ Erro ao baixar index.js"
  exit 1
fi

echo "✅ index.js baixado com sucesso"

###########################################
# 7. GERA CERTIFICADO SSL
###########################################
echo "🔐 Gerando SSL..."
openssl req -x509 -nodes -days 365 \
  -newkey rsa:2048 \
  -keyout "$SSL_PATH/key.pem" \
  -out "$SSL_PATH/cert.pem" \
  -subj "/CN=localhost"

echo "✅ Certificado SSL gerado"

###########################################
# 8. CRIA PACKAGE.JSON (BAILEYS — SEM PUPPETEER)
###########################################
cd "$INSTALL_PATH"

cat <<EOF > package.json
{
  "name": "editacodigo-publica-baileys",
  "version": "1.0.0",
  "main": "index.js",
  "dependencies": {
    "express": "^4.18.2",
    "axios": "^1.6.0",
    "dotenv": "^16.3.1",
    "@whiskeysockets/baileys": "latest",
    "qrcode-terminal": "^0.12.0",
    "pino": "^8.21.0"
  }
}
EOF

echo "✅ package.json criado (Baileys)"

###########################################
# 9. INSTALA DEPENDÊNCIAS NODE
###########################################
echo "📦 Instalando dependências Node..."
npm install --legacy-peer-deps

if [ $? -ne 0 ]; then
  echo "❌ Erro ao instalar dependências"
  exit 1
fi

echo "✅ Dependências Node instaladas com sucesso"

###########################################
# 10. CRIA .ENV
###########################################
cat <<EOF > .env
PORTA=$PORTA
TOKEN=$TOKEN
WEBHOOK_FUNCOES=$WEBHOOK_FUNCOES
WEBHOOK_MENSAGENS=$WEBHOOK_MENSAGENS
WEBHOOK_VALIDATE=$WEBHOOK_VALIDATE
EOF

echo "✅ Arquivo .env criado"

###########################################
# 11. INICIA COM PM2 (RESTART A CADA 3H)
###########################################
echo "🚀 Iniciando processo..."

pm2 start index.js \
  --name "$PROCESS_NAME" \
  --cron-restart="0 */3 * * *"

if [ $? -ne 0 ]; then
  echo "❌ Erro ao iniciar PM2"
  exit 1
fi

echo "✅ Processo iniciado com sucesso"

###########################################
# 12. SAVE + STARTUP (autostart via launchd)
###########################################
pm2 save

STARTUP_CMD=$(pm2 startup launchd -u "$USER" --hp "$HOME" | grep sudo)
if [ ! -z "$STARTUP_CMD" ]; then
  eval "$STARTUP_CMD" 2>/dev/null || true
fi

pm2 status

###########################################
# FINAL
###########################################
echo ""
echo "==============================================="
echo "✅ INSTALAÇÃO CONCLUÍDA — EDITACODIGO API PÚBLICA"
echo "⚙️  Engine         : @whiskeysockets/baileys"
echo "🔄 Restart auto   : a cada 3 horas"
echo "🟢 Processo ativo : $PROCESS_NAME"
echo "🔐 SSL em         : $SSL_PATH"
echo "📁 Instalação em  : $INSTALL_PATH"
echo "📂 Sessões em     : $INSTALL_PATH/sessions"
echo "==============================================="
echo ""
echo "Para verificar o status:"
echo "  pm2 status"
echo ""
echo "Para ver logs:"
echo "  pm2 logs '$PROCESS_NAME'"
echo ""
