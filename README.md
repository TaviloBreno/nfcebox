# NFCeBox - Sistema de Gestão de NFCe

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

## 📋 Sobre o Projeto

O **NFCeBox** é um sistema completo de gestão de Nota Fiscal de Consumidor Eletrônica (NFCe) desenvolvido em Laravel. O sistema oferece uma solução robusta e intuitiva para empresas que precisam gerenciar clientes, produtos e emitir NFCe de forma eficiente e em conformidade com a legislação brasileira.

## ✨ Funcionalidades Principais

### 👥 Gestão de Clientes
- ✅ CRUD completo de clientes
- ✅ Validação de CPF/CNPJ com algoritmo brasileiro
- ✅ Busca avançada por nome, documento ou email
- ✅ Endereços estruturados com integração ViaCEP
- ✅ Formatação automática de documentos e telefones
- ✅ Interface responsiva com Bootstrap 5

### 📦 Gestão de Produtos
- ✅ Cadastro de produtos com informações fiscais
- ✅ Validação de campos fiscais brasileiros (NCM, CFOP, CEST)
- ✅ Controle de estoque com transações e locks
- ✅ SKU único por produto
- ✅ Upload de imagens de produtos
- ✅ Alertas de estoque baixo
- ✅ Busca e filtros avançados

### 🔐 Sistema de Autenticação
- ✅ Login e registro de usuários
- ✅ Verificação de email
- ✅ Recuperação de senha
- ✅ Middleware de proteção de rotas

### 💰 Gestão de Vendas
- ✅ CRUD completo de vendas
- ✅ Controle de status (pendente, finalizada, cancelada)
- ✅ Múltiplas formas de pagamento
- ✅ Estorno automático de estoque no cancelamento
- ✅ Histórico completo de vendas
- ✅ Busca e filtros por período e status

### 📊 Relatórios Gerenciais
- ✅ Relatório de vendas por período
- ✅ Relatório de vendas por forma de pagamento
- ✅ Relatório de vendas por cliente
- ✅ Relatório de produtos mais vendidos
- ✅ Exportação em CSV e PDF
- ✅ Gráficos interativos

### 🎨 Interface do Usuário
- ✅ Design moderno e responsivo
- ✅ Componentes Bootstrap 5
- ✅ Máscaras de input automáticas
- ✅ Validação em tempo real
- ✅ Mensagens de feedback intuitivas
- ✅ Dashboard com métricas principais

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 11.x
- **Frontend**: Blade Templates + Bootstrap 5
- **Banco de Dados**: MySQL 8.0+
- **PHP**: 8.2+
- **JavaScript**: Vanilla JS + jQuery + Chart.js
- **CSS**: Bootstrap 5 + Custom CSS
- **Testes**: PHPUnit + Laravel Testing
- **PDF**: DomPDF para relatórios

## 📋 Pré-requisitos

- PHP 8.2 ou superior
- Composer
- MySQL 8.0 ou superior
- Node.js e NPM (para assets)
- Extensões PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## 🚀 Instalação

1. **Clone o repositório**
   ```bash
   git clone https://github.com/seu-usuario/nfcebox.git
   cd nfcebox
   ```

2. **Instale as dependências PHP**
   ```bash
   composer install
   ```

3. **Instale as dependências Node.js**
   ```bash
   npm install
   ```

4. **Configure o ambiente**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure o banco de dados**
   - Edite o arquivo `.env` com suas credenciais do banco
   - Execute as migrations:
   ```bash
   php artisan migrate
   ```

6. **Execute os seeders**
   ```bash
   # Seeds básicos
   php artisan nfce:seed basic
   
   # Seeds para desenvolvimento (recomendado)
   php artisan nfce:seed dev
   
   # Ou use o seeder padrão
   php artisan db:seed
   ```

7. **Compile os assets**
   ```bash
   npm run build
   ```

8. **Inicie o servidor**
   ```bash
   php artisan serve
   ```

## 📚 Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── CustomerController.php    # Gestão de clientes
│   │   ├── ProductController.php     # Gestão de produtos
│   │   ├── SaleController.php        # Gestão de vendas
│   │   ├── ReportController.php      # Relatórios gerenciais
│   │   └── Auth/                     # Controladores de autenticação
│   └── Requests/
│       ├── StoreCustomerRequest.php  # Validação de criação
│       └── UpdateCustomerRequest.php # Validação de atualização
├── Models/
│   ├── Customer.php                  # Modelo de cliente
│   ├── Product.php                   # Modelo de produto
│   ├── Sale.php                      # Modelo de venda
│   ├── SaleItem.php                  # Itens de venda
│   ├── CompanyConfig.php             # Configuração da empresa
│   └── User.php                      # Modelo de usuário
├── Services/
│   ├── NfceBuilderService.php        # Construção de NFCe
│   ├── DigitalSignatureService.php   # Assinatura digital
│   ├── SefazClientService.php        # Cliente SEFAZ
│   └── XmlBuilderService.php         # Construção de XML
└── Traits/
    └── DocumentHelper.php            # Helper para validação de documentos

resources/
├── views/
│   ├── customers/                    # Views de clientes
│   ├── products/                     # Views de produtos
│   ├── sales/                        # Views de vendas
│   ├── reports/                      # Views de relatórios
│   ├── auth/                         # Views de autenticação
│   └── layouts/                      # Layouts base
└── lang/
    └── pt_BR/                        # Traduções em português

tests/
├── Feature/                          # Testes de integração
│   ├── CustomerControllerTest.php
│   ├── ProductControllerTest.php
│   ├── SaleControllerTest.php
│   └── ReportControllerTest.php
└── Unit/                             # Testes unitários
    └── Services/                     # Testes de serviços
        ├── NfceBuilderServiceTest.php
        ├── DigitalSignatureServiceTest.php
        ├── SefazClientServiceTest.php
        └── XmlBuilderServiceTest.php

database/
└── seeders/
    ├── TestScenarioSeeder.php        # Seeds para testes
    ├── DevelopmentSeeder.php         # Seeds para desenvolvimento
    ├── PerformanceSeeder.php         # Seeds para performance
    └── README.md                     # Documentação dos seeders
```

## 🔧 Configuração

### Variáveis de Ambiente

Principais configurações no arquivo `.env`:

```env
APP_NAME="NFCeBox"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost
APP_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nfcebox
DB_USERNAME=root
DB_PASSWORD=
```

## 📖 Como Usar

1. **Acesse o sistema**: `http://localhost:8000`
2. **Registre-se** ou faça login
3. **Configure a empresa**: Acesse as configurações para definir dados da empresa
4. **Gerencie clientes**: Acesse `/customers` para CRUD completo
5. **Cadastre produtos**: Configure produtos com informações fiscais
6. **Registre vendas**: Crie e gerencie vendas com controle de estoque
7. **Visualize relatórios**: Acesse `/reports` para análises gerenciais
8. **Emita NFCe**: (Funcionalidade em desenvolvimento)

### 🧪 Executando Testes

```bash
# Executar todos os testes
php artisan test

# Executar testes com coverage
php artisan test --coverage

# Executar testes específicos
php artisan test --filter CustomerControllerTest
```

### 📊 Seeds e Dados de Teste

```bash
# Seeds básicos (produção)
php artisan nfce:seed basic

# Seeds para desenvolvimento
php artisan nfce:seed dev

# Seeds para testes automatizados
php artisan nfce:seed test

# Seeds para testes de performance
php artisan nfce:seed performance
```

Veja mais detalhes em `database/seeders/README.md`

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 👨‍💻 Autor

Desenvolvido com ❤️ para facilitar a gestão de NFCe no Brasil.

---

**NFCeBox** - Simplificando a gestão fiscal brasileira.
