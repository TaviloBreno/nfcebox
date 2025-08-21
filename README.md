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
- ✅ Controle de estoque
- ✅ SKU único por produto

### 🔐 Sistema de Autenticação
- ✅ Login e registro de usuários
- ✅ Verificação de email
- ✅ Recuperação de senha
- ✅ Middleware de proteção de rotas

### 🎨 Interface do Usuário
- ✅ Design moderno e responsivo
- ✅ Componentes Bootstrap 5
- ✅ Máscaras de input automáticas
- ✅ Validação em tempo real
- ✅ Mensagens de feedback intuitivas

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 11.x
- **Frontend**: Blade Templates + Bootstrap 5
- **Banco de Dados**: MySQL 8.0+
- **PHP**: 8.2+
- **JavaScript**: Vanilla JS + jQuery
- **CSS**: Bootstrap 5 + Custom CSS

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

6. **Execute os seeders (opcional)**
   ```bash
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
│   │   └── Auth/                     # Controladores de autenticação
│   └── Requests/
│       ├── StoreCustomerRequest.php  # Validação de criação
│       └── UpdateCustomerRequest.php # Validação de atualização
├── Models/
│   ├── Customer.php                  # Modelo de cliente
│   ├── Product.php                   # Modelo de produto
│   └── User.php                      # Modelo de usuário
└── Traits/
    └── DocumentHelper.php            # Helper para validação de documentos

resources/
├── views/
│   ├── customers/                    # Views de clientes
│   ├── auth/                         # Views de autenticação
│   └── layouts/                      # Layouts base
└── lang/
    └── pt_BR/                        # Traduções em português
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
3. **Gerencie clientes**: Acesse `/customers` para CRUD completo
4. **Cadastre produtos**: Configure produtos com informações fiscais
5. **Emita NFCe**: (Funcionalidade em desenvolvimento)

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
