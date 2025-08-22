# Configuração do Ambiente (.env)

Este documento descreve todas as variáveis de ambiente necessárias para configurar o NFCeBox corretamente.

## 📋 Arquivo .env Completo

```env
# =============================================================================
# CONFIGURAÇÕES BÁSICAS DA APLICAÇÃO
# =============================================================================

# Nome da aplicação
APP_NAME="NFCeBox"

# Ambiente (local, testing, staging, production)
APP_ENV=local

# Chave de criptografia da aplicação (gerada com: php artisan key:generate)
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Modo debug (true apenas em desenvolvimento)
APP_DEBUG=true

# URL base da aplicação
APP_URL=http://localhost:8000

# Timezone da aplicação
APP_TIMEZONE=America/Sao_Paulo

# Locale da aplicação
APP_LOCALE=pt_BR

# Locale de fallback
APP_FALLBACK_LOCALE=en

# Faker locale para seeds
APP_FAKER_LOCALE=pt_BR

# =============================================================================
# CONFIGURAÇÕES DO BANCO DE DADOS
# =============================================================================

# Conexão do banco de dados
DB_CONNECTION=mysql

# Host do banco de dados
DB_HOST=127.0.0.1

# Porta do banco de dados
DB_PORT=3306

# Nome do banco de dados
DB_DATABASE=nfcebox

# Usuário do banco de dados
DB_USERNAME=root

# Senha do banco de dados
DB_PASSWORD=

# =============================================================================
# CONFIGURAÇÕES DE SESSÃO
# =============================================================================

# Driver de sessão (file, cookie, database, apc, memcached, redis, array)
SESSION_DRIVER=database

# Tempo de vida da sessão em minutos
SESSION_LIFETIME=120

# Criptografar cookies de sessão
SESSION_ENCRYPT=false

# Path dos cookies de sessão
SESSION_PATH=/

# Domínio dos cookies de sessão
SESSION_DOMAIN=null

# Cookies apenas via HTTPS
SESSION_SECURE_COOKIE=false

# Cookies apenas via HTTP (não JavaScript)
SESSION_HTTP_ONLY=true

# SameSite cookie attribute
SESSION_SAME_SITE=lax

# =============================================================================
# CONFIGURAÇÕES DE CACHE
# =============================================================================

# Driver de cache (array, database, file, memcached, redis, dynamodb)
CACHE_DRIVER=database

# Prefixo das chaves de cache
CACHE_PREFIX=nfcebox_cache

# =============================================================================
# CONFIGURAÇÕES DE FILA
# =============================================================================

# Driver de fila (sync, database, beanstalkd, sqs, redis)
QUEUE_CONNECTION=database

# =============================================================================
# CONFIGURAÇÕES DE EMAIL
# =============================================================================

# Driver de email (smtp, ses, mailgun, postmark, log, array)
MAIL_MAILER=log

# Host do servidor SMTP
MAIL_HOST=127.0.0.1

# Porta do servidor SMTP
MAIL_PORT=2525

# Usuário do servidor SMTP
MAIL_USERNAME=null

# Senha do servidor SMTP
MAIL_PASSWORD=null

# Criptografia do email (tls, ssl)
MAIL_ENCRYPTION=null

# Email remetente padrão
MAIL_FROM_ADDRESS="noreply@nfcebox.com"

# Nome do remetente padrão
MAIL_FROM_NAME="${APP_NAME}"

# =============================================================================
# CONFIGURAÇÕES DE LOG
# =============================================================================

# Canal de log (stack, single, daily, slack, syslog, errorlog)
LOG_CHANNEL=daily

# Nível de log (emergency, alert, critical, error, warning, notice, info, debug)
LOG_LEVEL=debug

# Dias para manter logs diários
LOG_DAILY_DAYS=14

# =============================================================================
# CONFIGURAÇÕES ESPECÍFICAS DO NFCEBOX
# =============================================================================

# Ambiente da SEFAZ (homologacao, producao)
NFCE_ENVIRONMENT=homologacao

# Diretório para armazenar certificados digitais
NFCE_CERTIFICATES_PATH=storage/certificates

# Diretório para armazenar XMLs gerados
NFCE_XML_PATH=storage/xml

# Diretório para armazenar PDFs (DANFE)
NFCE_PDF_PATH=storage/pdf

# Timeout para requisições à SEFAZ (em segundos)
NFCE_SEFAZ_TIMEOUT=30

# Número máximo de tentativas para envio à SEFAZ
NFCE_MAX_RETRY_ATTEMPTS=3

# Intervalo entre tentativas (em segundos)
NFCE_RETRY_DELAY=5

# =============================================================================
# CONFIGURAÇÕES DE UPLOAD DE ARQUIVOS
# =============================================================================

# Tamanho máximo de upload em KB (2MB = 2048KB)
MAX_UPLOAD_SIZE=2048

# Tipos de arquivo permitidos para imagens de produtos
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp

# Diretório para armazenar imagens de produtos
PRODUCT_IMAGES_PATH=storage/products

# =============================================================================
# CONFIGURAÇÕES DE RELATÓRIOS
# =============================================================================

# Número máximo de registros por página em relatórios
REPORT_MAX_RECORDS_PER_PAGE=1000

# Timeout para geração de relatórios PDF (em segundos)
REPORT_PDF_TIMEOUT=60

# Diretório temporário para relatórios
REPORT_TEMP_PATH=storage/temp/reports

# =============================================================================
# CONFIGURAÇÕES DE SEGURANÇA
# =============================================================================

# Número máximo de tentativas de login
LOGIN_MAX_ATTEMPTS=5

# Tempo de bloqueio após exceder tentativas (em minutos)
LOGIN_LOCKOUT_DURATION=15

# Força HTTPS em produção
FORCE_HTTPS=false

# =============================================================================
# CONFIGURAÇÕES DE DESENVOLVIMENTO (apenas para APP_ENV=local)
# =============================================================================

# Habilitar Telescope (ferramenta de debug do Laravel)
TELESCOPE_ENABLED=true

# Habilitar query log
DB_LOG_QUERIES=false

# Mostrar erros de SQL
DB_SHOW_SQL_ERRORS=true

# =============================================================================
# CONFIGURAÇÕES DE TESTE (apenas para APP_ENV=testing)
# =============================================================================

# Banco de dados para testes
DB_TEST_DATABASE=nfcebox_test

# Usar banco em memória para testes
DB_TEST_IN_MEMORY=false

# =============================================================================
# CONFIGURAÇÕES OPCIONAIS DE TERCEIROS
# =============================================================================

# Chave da API do ViaCEP (opcional, para consulta de CEP)
VIACEP_API_KEY=null

# Chave da API de validação de CPF/CNPJ (opcional)
DOCUMENT_VALIDATION_API_KEY=null

# Configurações do Redis (se usar)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Configurações do Memcached (se usar)
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211
```

## 🔧 Configurações por Ambiente

### Desenvolvimento Local

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
NFCE_ENVIRONMENT=homologacao
TELESCOPE_ENABLED=true
MAIL_MAILER=log
```

### Testes

```env
APP_ENV=testing
APP_DEBUG=false
LOG_LEVEL=error
DB_DATABASE=nfcebox_test
MAIL_MAILER=array
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
```

### Produção

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
NFCE_ENVIRONMENT=producao
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
MAIL_MAILER=smtp
# Configure SMTP real
MAIL_HOST=seu-servidor-smtp.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@dominio.com
MAIL_PASSWORD=sua-senha-smtp
MAIL_ENCRYPTION=tls
```

## 📝 Variáveis Obrigatórias

Estas variáveis **DEVEM** ser configuradas:

- `APP_KEY` - Gerada com `php artisan key:generate`
- `DB_DATABASE` - Nome do banco de dados
- `DB_USERNAME` - Usuário do banco
- `DB_PASSWORD` - Senha do banco

## 🔐 Variáveis Sensíveis

Estas variáveis contêm informações sensíveis e **NUNCA** devem ser commitadas:

- `APP_KEY`
- `DB_PASSWORD`
- `MAIL_PASSWORD`
- Chaves de API de terceiros
- Senhas de certificados digitais

## 🚀 Configuração Rápida

1. **Copie o arquivo de exemplo:**
   ```bash
   cp .env.example .env
   ```

2. **Gere a chave da aplicação:**
   ```bash
   php artisan key:generate
   ```

3. **Configure o banco de dados:**
   ```bash
   # Edite as variáveis DB_* no arquivo .env
   nano .env
   ```

4. **Execute as migrations:**
   ```bash
   php artisan migrate
   ```

5. **Execute os seeders:**
   ```bash
   php artisan nfce:seed dev
   ```

## 🔍 Validação da Configuração

Use estes comandos para validar sua configuração:

```bash
# Verificar configuração geral
php artisan config:show

# Verificar conexão com banco
php artisan migrate:status

# Verificar configuração de email
php artisan tinker
>>> Mail::raw('Teste', function($msg) { $msg->to('test@example.com')->subject('Teste'); });

# Verificar logs
tail -f storage/logs/laravel.log
```

## ⚠️ Problemas Comuns

### Erro de Chave da Aplicação
```
The only supported ciphers are AES-128-CBC and AES-256-CBC
```
**Solução:** Execute `php artisan key:generate`

### Erro de Conexão com Banco
```
SQLSTATE[HY000] [1045] Access denied
```
**Solução:** Verifique as credenciais do banco no `.env`

### Erro de Permissão de Arquivos
```
The stream or file could not be opened
```
**Solução:** Configure permissões:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Erro de Timezone
```
Invalid timezone
```
**Solução:** Use um timezone válido como `America/Sao_Paulo`

## 📚 Referências

- [Documentação do Laravel - Configuration](https://laravel.com/docs/configuration)
- [Lista de Timezones PHP](https://www.php.net/manual/en/timezones.php)
- [Configuração de Email no Laravel](https://laravel.com/docs/mail#configuration)
- [Configuração de Cache no Laravel](https://laravel.com/docs/cache#configuration)