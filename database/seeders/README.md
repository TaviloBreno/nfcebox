# Seeders do NFCeBox

Este diretório contém os seeders para popular o banco de dados com diferentes tipos de dados para desenvolvimento, teste e demonstração.

## Seeders Disponíveis

### 1. Seeders Básicos

#### `CompanyConfigSeeder`
- Cria a configuração básica da empresa
- Necessário para o funcionamento da aplicação
- Executa: `php artisan db:seed --class=CompanyConfigSeeder`

#### `CustomerSeeder`
- Cria 20 clientes fictícios usando factories
- Inclui clientes pessoa física e jurídica
- Executa: `php artisan db:seed --class=CustomerSeeder`

#### `ProductSeeder`
- Cria 30 produtos fictícios usando factories
- Produtos de diferentes categorias e preços
- Executa: `php artisan db:seed --class=ProductSeeder`

#### `SaleSeeder`
- Cria vendas fictícias dos últimos 30 dias
- Inclui vendas finalizadas, pendentes e canceladas
- Executa: `php artisan db:seed --class=SaleSeeder`

### 2. Seeders Especializados

#### `TestScenarioSeeder`
- **Propósito**: Dados específicos para testes automatizados
- **Conteúdo**:
  - Usuários com credenciais conhecidas
  - Clientes com documentos específicos
  - Produtos com códigos padronizados
  - Vendas com cenários controlados
- **Quando usar**: Ambiente de teste (testing)
- **Executa**: `php artisan db:seed --class=TestScenarioSeeder`

#### `DevelopmentSeeder`
- **Propósito**: Dados realistas para desenvolvimento
- **Conteúdo**:
  - Usuários com diferentes perfis (admin, gerente, operador)
  - Clientes variados (PF e PJ)
  - Produtos organizados por categoria
  - Histórico de vendas dos últimos 6 meses
- **Quando usar**: Ambiente de desenvolvimento local
- **Executa**: `php artisan db:seed --class=DevelopmentSeeder`

#### `PerformanceSeeder`
- **Propósito**: Grande volume de dados para testes de performance
- **Conteúdo**:
  - 500+ clientes
  - 200+ produtos
  - Milhares de vendas dos últimos 2 anos
- **Quando usar**: Testes de performance e stress
- **Executa**: `php artisan db:seed --class=PerformanceSeeder`
- **⚠️ Atenção**: Pode demorar vários minutos para executar

## Comando Personalizado

Use o comando personalizado `nfce:seed` para facilitar a execução:

```bash
# Seeds básicos (padrão)
php artisan nfce:seed
php artisan nfce:seed basic

# Seeds para teste
php artisan nfce:seed test

# Seeds para desenvolvimento
php artisan nfce:seed dev

# Seeds para performance (cuidado!)
php artisan nfce:seed performance

# Todos os seeds
php artisan nfce:seed all
```

## Estrutura de Dados Criados

### Usuários
- **Básico**: 9 usuários (4 específicos + 5 aleatórios)
- **Teste**: Usuários com credenciais conhecidas
- **Desenvolvimento**: Usuários com diferentes perfis
- **Performance**: +20 usuários adicionais

### Clientes
- **Básico**: 20 clientes aleatórios
- **Teste**: 2 clientes específicos (PF e PJ)
- **Desenvolvimento**: 4 clientes específicos + 25 aleatórios
- **Performance**: +500 clientes adicionais

### Produtos
- **Básico**: 30 produtos aleatórios
- **Teste**: 3 produtos específicos
- **Desenvolvimento**: 8 produtos categorizados + 40 aleatórios
- **Performance**: +200 produtos adicionais

### Vendas
- **Básico**: 23 vendas (15 finalizadas, 5 pendentes, 3 canceladas)
- **Teste**: 5 vendas específicas para cenários de teste
- **Desenvolvimento**: Vendas dos últimos 6 meses
- **Performance**: Vendas dos últimos 2 anos (milhares)

## Ambientes e Execução Automática

O `DatabaseSeeder` executa automaticamente baseado no ambiente:

- **Todos os ambientes**: Seeders básicos
- **Local + Testing**: + TestScenarioSeeder
- **Local**: + DevelopmentSeeder

```bash
# Executa seeders baseado no ambiente
php artisan db:seed
```

## Dicas de Uso

### Para Desenvolvimento
1. Execute `php artisan nfce:seed dev` para ter dados realistas
2. Use os usuários criados para testar diferentes perfis
3. Os produtos estão organizados por categoria

### Para Testes
1. Execute `php artisan nfce:seed test` antes dos testes
2. Use as credenciais conhecidas dos usuários de teste
3. Os dados são controlados e previsíveis

### Para Performance
1. Execute apenas quando necessário
2. Use um banco de dados separado
3. Monitore o espaço em disco

### Limpeza
```bash
# Limpar e recriar banco
php artisan migrate:fresh

# Limpar e executar seeds básicos
php artisan migrate:fresh --seed

# Limpar e executar seeds específicos
php artisan migrate:fresh
php artisan nfce:seed dev
```

## Credenciais de Teste

### Usuários Padrão (DatabaseSeeder)
- **admin@nfcebox.com** - Administrador
- **teste@nfcebox.com** - Usuário Teste
- **joao@nfcebox.com** - João Silva
- **maria@nfcebox.com** - Maria Santos

### Usuários de Teste (TestScenarioSeeder)
- **admin.test@nfcebox.com** - Admin Test (password123)
- **operator.test@nfcebox.com** - Operator Test (password123)

### Usuários de Desenvolvimento (DevelopmentSeeder)
- **carlos.admin@nfcebox.com** - Carlos Administrador
- **ana.gerente@nfcebox.com** - Ana Gerente
- **pedro.operador@nfcebox.com** - Pedro Operador
- **lucia.vendedora@nfcebox.com** - Lucia Vendedora

**Senha padrão para todos**: `password123`

## Troubleshooting

### Erro de Memória
```bash
php -d memory_limit=512M artisan nfce:seed performance
```

### Timeout
```bash
php -d max_execution_time=300 artisan nfce:seed performance
```

### Dependências
Certifique-se de que as migrations foram executadas:
```bash
php artisan migrate
```

### Ordem de Execução
Os seeders têm dependências. Use sempre o comando personalizado ou o DatabaseSeeder para garantir a ordem correta.