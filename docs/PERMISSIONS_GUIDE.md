# Guia de Permissões - NFCeBox

Este documento descreve o sistema de permissões e controle de acesso do NFCeBox, incluindo níveis de usuário, políticas de acesso e configuração de segurança.

## 📋 Índice

1. [Visão Geral](#-visão-geral)
2. [Níveis de Usuário](#-níveis-de-usuário)
3. [Matriz de Permissões](#-matriz-de-permissões)
4. [Implementação Técnica](#-implementação-técnica)
5. [Configuração de Usuários](#-configuração-de-usuários)
6. [Políticas de Segurança](#-políticas-de-segurança)
7. [Auditoria e Logs](#-auditoria-e-logs)
8. [Troubleshooting](#-troubleshooting)

## 🔐 Visão Geral

### Sistema de Controle de Acesso
O NFCeBox utiliza um sistema de controle de acesso baseado em:
- **Autenticação:** Verificação de identidade do usuário
- **Autorização:** Verificação de permissões para ações específicas
- **Auditoria:** Registro de todas as ações realizadas

### Princípios de Segurança
- **Menor Privilégio:** Usuários recebem apenas as permissões mínimas necessárias
- **Separação de Responsabilidades:** Diferentes níveis para diferentes funções
- **Auditabilidade:** Todas as ações são registradas
- **Defesa em Profundidade:** Múltiplas camadas de segurança

## 👥 Níveis de Usuário

### 1. Administrador (admin)
**Descrição:** Acesso completo ao sistema

**Responsabilidades:**
- Configuração geral do sistema
- Gestão de usuários e permissões
- Configuração de certificados digitais
- Backup e manutenção
- Acesso a todos os relatórios

**Características:**
- Único usuário com acesso total
- Pode criar/editar/excluir outros usuários
- Acesso a configurações sensíveis
- Não pode ser excluído pelo sistema

### 2. Gerente (manager)
**Descrição:** Supervisão de operações e relatórios

**Responsabilidades:**
- Supervisão de vendas
- Gestão de produtos e clientes
- Acesso a relatórios gerenciais
- Configuração de parâmetros operacionais

**Limitações:**
- Não pode gerenciar usuários
- Não acessa configurações de sistema
- Não pode alterar certificados

### 3. Operador (operator)
**Descrição:** Operação diária de vendas

**Responsabilidades:**
- Realizar vendas
- Cadastrar clientes básicos
- Consultar produtos
- Emitir NFCe e DANFE

**Limitações:**
- Não pode alterar produtos
- Acesso limitado a relatórios
- Não pode cancelar vendas de outros usuários
- Não acessa configurações

### 4. Visualizador (viewer)
**Descrição:** Acesso somente leitura

**Responsabilidades:**
- Consultar vendas
- Visualizar relatórios básicos
- Consultar produtos e clientes

**Limitações:**
- Não pode criar/editar/excluir registros
- Não pode realizar vendas
- Acesso muito limitado a relatórios

## 📊 Matriz de Permissões

### Legenda
- ✅ **Permitido:** Acesso total à funcionalidade
- 🔸 **Limitado:** Acesso com restrições
- ❌ **Negado:** Sem acesso à funcionalidade
- 👁️ **Somente Leitura:** Apenas visualização

### Funcionalidades do Sistema

| Funcionalidade | Admin | Gerente | Operador | Visualizador |
|----------------|-------|---------|----------|-------------|
| **USUÁRIOS** |
| Listar usuários | ✅ | ❌ | ❌ | ❌ |
| Criar usuário | ✅ | ❌ | ❌ | ❌ |
| Editar usuário | ✅ | ❌ | ❌ | ❌ |
| Excluir usuário | ✅ | ❌ | ❌ | ❌ |
| Alterar própria senha | ✅ | ✅ | ✅ | ✅ |
| **VENDAS** |
| Listar vendas | ✅ | ✅ | 🔸¹ | 👁️² |
| Criar venda | ✅ | ✅ | ✅ | ❌ |
| Editar venda | ✅ | ✅ | 🔸³ | ❌ |
| Cancelar venda | ✅ | ✅ | 🔸³ | ❌ |
| Visualizar detalhes | ✅ | ✅ | ✅ | 👁️ |
| Emitir NFCe | ✅ | ✅ | ✅ | ❌ |
| Gerar DANFE | ✅ | ✅ | ✅ | 👁️ |
| **PRODUTOS** |
| Listar produtos | ✅ | ✅ | 👁️ | 👁️ |
| Criar produto | ✅ | ✅ | ❌ | ❌ |
| Editar produto | ✅ | ✅ | ❌ | ❌ |
| Excluir produto | ✅ | ✅ | ❌ | ❌ |
| Ajustar estoque | ✅ | ✅ | ❌ | ❌ |
| Upload de imagem | ✅ | ✅ | ❌ | ❌ |
| **CLIENTES** |
| Listar clientes | ✅ | ✅ | 👁️ | 👁️ |
| Criar cliente | ✅ | ✅ | 🔸⁴ | ❌ |
| Editar cliente | ✅ | ✅ | 🔸⁴ | ❌ |
| Excluir cliente | ✅ | ✅ | ❌ | ❌ |
| Histórico de vendas | ✅ | ✅ | 👁️ | 👁️ |
| **RELATÓRIOS** |
| Vendas por período | ✅ | ✅ | 🔸⁵ | 🔸⁵ |
| Produtos mais vendidos | ✅ | ✅ | 👁️ | 👁️ |
| Relatório de estoque | ✅ | ✅ | 👁️ | 👁️ |
| Relatório financeiro | ✅ | ✅ | ❌ | ❌ |
| Exportar CSV/PDF | ✅ | ✅ | 🔸⁶ | ❌ |
| **CONFIGURAÇÕES** |
| Dados da empresa | ✅ | ❌ | ❌ | ❌ |
| Certificados digitais | ✅ | ❌ | ❌ | ❌ |
| Configurações NFCe | ✅ | ❌ | ❌ | ❌ |
| Backup/Restore | ✅ | ❌ | ❌ | ❌ |
| Logs do sistema | ✅ | 🔸⁷ | ❌ | ❌ |

### Notas das Restrições

1. **🔸¹ Operador - Listar vendas:** Apenas vendas próprias ou do dia atual
2. **👁️² Visualizador - Vendas:** Apenas últimos 30 dias, sem dados financeiros sensíveis
3. **🔸³ Operador - Editar/Cancelar:** Apenas vendas próprias e dentro de 24h
4. **🔸⁴ Operador - Clientes:** Apenas cadastro básico durante vendas
5. **🔸⁵ Relatórios limitados:** Apenas últimos 30 dias
6. **🔸⁶ Exportação limitada:** Apenas relatórios básicos
7. **🔸⁷ Gerente - Logs:** Apenas logs operacionais, não de sistema

## ⚙️ Implementação Técnica

### Middleware de Autenticação

```php
<?php
// app/Http/Middleware/CheckUserRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        if (!in_array($user->role, $roles)) {
            abort(403, 'Acesso negado. Permissão insuficiente.');
        }
        
        return $next($request);
    }
}
```

### Políticas de Autorização (Policies)

#### Policy de Vendas
```php
<?php
// app/Policies/SalePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'manager', 'operator', 'viewer']);
    }
    
    public function view(User $user, Sale $sale)
    {
        // Admin e gerente podem ver todas
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // Operador pode ver apenas suas vendas
        if ($user->role === 'operator') {
            return $sale->user_id === $user->id;
        }
        
        // Visualizador pode ver vendas dos últimos 30 dias
        if ($user->role === 'viewer') {
            return $sale->created_at->diffInDays(now()) <= 30;
        }
        
        return false;
    }
    
    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'manager', 'operator']);
    }
    
    public function update(User $user, Sale $sale)
    {
        // Admin e gerente podem editar todas
        if (in_array($user->role, ['admin', 'manager'])) {
            return true;
        }
        
        // Operador pode editar apenas suas vendas dentro de 24h
        if ($user->role === 'operator') {
            return $sale->user_id === $user->id && 
                   $sale->created_at->diffInHours(now()) <= 24;
        }
        
        return false;
    }
    
    public function delete(User $user, Sale $sale)
    {
        return $this->update($user, $sale);
    }
}
```

#### Policy de Produtos
```php
<?php
// app/Policies/ProductPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(User $user)
    {
        return true; // Todos podem listar produtos
    }
    
    public function view(User $user, Product $product)
    {
        return true; // Todos podem ver detalhes
    }
    
    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
    
    public function update(User $user, Product $product)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
    
    public function delete(User $user, Product $product)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
    
    public function adjustStock(User $user, Product $product)
    {
        return in_array($user->role, ['admin', 'manager']);
    }
}
```

### Gates Personalizados

```php
<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Sale::class => SalePolicy::class,
        Product::class => ProductPolicy::class,
        Customer::class => CustomerPolicy::class,
    ];
    
    public function boot()
    {
        $this->registerPolicies();
        
        // Gate para administradores
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });
        
        // Gate para gerentes e acima
        Gate::define('manager-level', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });
        
        // Gate para operadores e acima
        Gate::define('operator-level', function (User $user) {
            return in_array($user->role, ['admin', 'manager', 'operator']);
        });
        
        // Gate para relatórios financeiros
        Gate::define('financial-reports', function (User $user) {
            return in_array($user->role, ['admin', 'manager']);
        });
        
        // Gate para configurações do sistema
        Gate::define('system-config', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
```

### Uso nos Controllers

```php
<?php
// app/Http/Controllers/SaleController.php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SaleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Sale::class);
        
        $query = Sale::query();
        
        // Aplicar filtros baseados no papel do usuário
        if (auth()->user()->role === 'operator') {
            $query->where('user_id', auth()->id());
        } elseif (auth()->user()->role === 'viewer') {
            $query->where('created_at', '>=', now()->subDays(30));
        }
        
        $sales = $query->paginate(20);
        
        return view('sales.index', compact('sales'));
    }
    
    public function create()
    {
        $this->authorize('create', Sale::class);
        
        return view('sales.create');
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Sale::class);
        
        // Lógica de criação...
    }
    
    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);
        
        return view('sales.show', compact('sale'));
    }
    
    public function edit(Sale $sale)
    {
        $this->authorize('update', $sale);
        
        return view('sales.edit', compact('sale'));
    }
    
    public function destroy(Sale $sale)
    {
        $this->authorize('delete', $sale);
        
        // Lógica de exclusão...
    }
}
```

### Proteção de Rotas

```php
<?php
// routes/web.php

use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;

// Rotas que requerem autenticação
Route::middleware(['auth'])->group(function () {
    
    // Dashboard - todos os usuários autenticados
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Vendas - operadores e acima
    Route::middleware(['role:admin,manager,operator'])->group(function () {
        Route::resource('sales', SaleController::class);
        Route::post('sales/{sale}/nfce', [SaleController::class, 'generateNfce'])->name('sales.nfce');
    });
    
    // Produtos - gerentes e acima para modificações
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
    
    // Relatórios - acesso baseado em permissões
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::middleware(['can:financial-reports'])->group(function () {
        Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    });
    
    // Administração - apenas administradores
    Route::middleware(['can:admin-only'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
```

## 👤 Configuração de Usuários

### Criação de Usuários

#### Via Interface Web
1. **Login como Administrador**
2. **Acessar:** Menu → Usuários → Novo Usuário
3. **Preencher dados:**
   - Nome completo
   - Email (único)
   - Senha (mínimo 8 caracteres)
   - Nível de acesso
   - Status (ativo/inativo)

#### Via Comando Artisan
```bash
# Criar usuário administrador
php artisan make:user --admin

# Criar usuário com nível específico
php artisan make:user --role=manager

# Criar usuário interativo
php artisan make:user
```

#### Comando Personalizado
```php
<?php
// app/Console/Commands/CreateUserCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    protected $signature = 'make:user {--admin} {--role=operator}';
    protected $description = 'Criar novo usuário do sistema';
    
    public function handle()
    {
        $name = $this->ask('Nome do usuário');
        $email = $this->ask('Email');
        $password = $this->secret('Senha');
        
        $role = $this->option('admin') ? 'admin' : $this->option('role');
        
        // Validar dados
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,manager,operator,viewer'
        ]);
        
        if ($validator->fails()) {
            $this->error('Dados inválidos:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - {$error}");
            }
            return 1;
        }
        
        // Criar usuário
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'active' => true
        ]);
        
        $this->info("Usuário {$user->name} criado com sucesso!");
        $this->line("ID: {$user->id}");
        $this->line("Email: {$user->email}");
        $this->line("Nível: {$user->role}");
        
        return 0;
    }
}
```

### Alteração de Permissões

#### Interface Web
```php
// app/Http/Controllers/UserController.php

public function updateRole(Request $request, User $user)
{
    $this->authorize('admin-only');
    
    $request->validate([
        'role' => 'required|in:admin,manager,operator,viewer'
    ]);
    
    // Não permitir alterar o próprio nível
    if ($user->id === auth()->id()) {
        return back()->with('error', 'Não é possível alterar seu próprio nível de acesso.');
    }
    
    // Não permitir remover o último admin
    if ($user->role === 'admin' && User::where('role', 'admin')->count() === 1) {
        return back()->with('error', 'Não é possível alterar o último administrador.');
    }
    
    $oldRole = $user->role;
    $user->update(['role' => $request->role]);
    
    // Log da alteração
    activity()
        ->performedOn($user)
        ->withProperties([
            'old_role' => $oldRole,
            'new_role' => $request->role
        ])
        ->log('Nível de acesso alterado');
    
    return back()->with('success', 'Nível de acesso atualizado com sucesso.');
}
```

#### Via Comando
```bash
# Alterar nível de usuário
php artisan user:role user@example.com manager

# Ativar/desativar usuário
php artisan user:toggle user@example.com
```

## 🔒 Políticas de Segurança

### Senhas

#### Requisitos
- **Comprimento mínimo:** 8 caracteres
- **Complexidade:** Letras, números e símbolos (recomendado)
- **Histórico:** Não reutilizar últimas 5 senhas
- **Expiração:** 90 dias (configurável)

#### Implementação
```php
<?php
// app/Rules/StrongPassword.php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $value);
    }
    
    public function message()
    {
        return 'A senha deve ter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e símbolo.';
    }
}
```

### Tentativas de Login

#### Configuração
```php
// config/auth.php

'throttle' => [
    'max_attempts' => 5,
    'decay_minutes' => 15,
    'lockout_duration' => 30, // minutos
],
```

#### Middleware Personalizado
```php
<?php
// app/Http/Middleware/LoginThrottle.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginThrottle
{
    public function handle(Request $request, Closure $next)
    {
        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos."
            ], 429);
        }
        
        $response = $next($request);
        
        // Se login falhou, incrementar contador
        if ($response->getStatusCode() === 422) {
            RateLimiter::hit($key, 900); // 15 minutos
        } else {
            RateLimiter::clear($key);
        }
        
        return $response;
    }
}
```

### Sessões

#### Configuração Segura
```env
# .env
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_SECURE_COOKIE=true  # apenas HTTPS
```

#### Timeout de Inatividade
```php
<?php
// app/Http/Middleware/SessionTimeout.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity', time());
            $timeout = config('session.lifetime') * 60; // converter para segundos
            
            if (time() - $lastActivity > $timeout) {
                Auth::logout();
                session()->flush();
                
                return redirect()->route('login')
                    ->with('message', 'Sessão expirada por inatividade.');
            }
            
            session(['last_activity' => time()]);
        }
        
        return $next($request);
    }
}
```

## 📝 Auditoria e Logs

### Log de Atividades

#### Instalação do Spatie Activity Log
```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

#### Configuração no Model
```php
<?php
// app/Models/Sale.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Sale extends Model
{
    use LogsActivity;
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'total', 'status', 'payment_method'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    
    public function getDescriptionForEvent(string $eventName): string
    {
        return match($eventName) {
            'created' => 'Venda criada',
            'updated' => 'Venda atualizada',
            'deleted' => 'Venda cancelada',
            default => "Venda {$eventName}"
        };
    }
}
```

#### Logs Personalizados
```php
// Em qualquer lugar do código

use Spatie\Activitylog\Facades\Activity;

// Log simples
activity()->log('Usuário acessou relatório financeiro');

// Log com propriedades
activity()
    ->performedOn($sale)
    ->withProperties([
        'old_status' => 'pending',
        'new_status' => 'completed',
        'nfce_number' => '123456'
    ])
    ->log('Status da venda alterado');

// Log com usuário específico
activity()
    ->causedBy($user)
    ->performedOn($product)
    ->log('Estoque ajustado manualmente');
```

### Visualização de Logs

#### Controller de Auditoria
```php
<?php
// app/Http/Controllers/AuditController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin-only');
        
        $query = Activity::with(['causer', 'subject'])
            ->latest();
        
        // Filtros
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }
        
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $activities = $query->paginate(50);
        
        return view('audit.index', compact('activities'));
    }
}
```

### Eventos de Segurança

#### Listener para Login
```php
<?php
// app/Listeners/LogSuccessfulLogin.php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Spatie\Activitylog\Facades\Activity;

class LogSuccessfulLogin
{
    public function handle(Login $event)
    {
        Activity::create([
            'log_name' => 'security',
            'description' => 'Login realizado',
            'subject_type' => get_class($event->user),
            'subject_id' => $event->user->id,
            'causer_type' => get_class($event->user),
            'causer_id' => $event->user->id,
            'properties' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard
            ]
        ]);
    }
}
```

## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Erro 403 - Acesso Negado
**Sintomas:**
- Página de erro 403
- Mensagem "Acesso negado"
- Redirecionamento inesperado

**Diagnóstico:**
```bash
# Verificar nível do usuário
php artisan tinker
>>> $user = User::find(1);
>>> dd($user->role);

# Verificar políticas
>>> Gate::allows('view', $sale);
>>> Gate::denies('create', Product::class);
```

**Soluções:**
- Verificar nível de acesso do usuário
- Revisar políticas de autorização
- Verificar middleware nas rotas

#### 2. Usuário Não Consegue Fazer Login
**Possíveis Causas:**
- Conta desativada
- Muitas tentativas de login
- Senha incorreta
- Email não verificado

**Verificações:**
```sql
-- Verificar status do usuário
SELECT id, name, email, active, email_verified_at FROM users WHERE email = 'user@example.com';

-- Verificar tentativas de login
SELECT * FROM failed_jobs WHERE payload LIKE '%LoginThrottle%';
```

#### 3. Permissões Não Funcionam
**Verificar:**
1. Políticas registradas no `AuthServiceProvider`
2. Middleware aplicado nas rotas
3. Gates definidos corretamente
4. Cache de configuração limpo

```bash
# Limpar cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Comandos de Diagnóstico

#### Verificar Permissões de Usuário
```bash
php artisan user:permissions user@example.com
```

#### Listar Usuários por Nível
```bash
php artisan user:list --role=admin
php artisan user:list --inactive
```

#### Resetar Senha
```bash
php artisan user:reset-password user@example.com
```

### Logs de Debug

#### Habilitar Log de Autorização
```php
// app/Providers/AuthServiceProvider.php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

public function boot()
{
    $this->registerPolicies();
    
    // Log de tentativas de autorização
    Gate::after(function ($user, $ability, $result, $arguments) {
        Log::debug('Authorization check', [
            'user_id' => $user->id,
            'ability' => $ability,
            'result' => $result,
            'arguments' => $arguments
        ]);
    });
}
```

## 📚 Referências

### Documentação Laravel
- [Authentication](https://laravel.com/docs/authentication)
- [Authorization](https://laravel.com/docs/authorization)
- [Middleware](https://laravel.com/docs/middleware)
- [Policies](https://laravel.com/docs/authorization#creating-policies)

### Pacotes Utilizados
- [Spatie Laravel Activity Log](https://spatie.be/docs/laravel-activitylog)
- [Laravel Permission](https://spatie.be/docs/laravel-permission) (alternativa)

### Boas Práticas de Segurança
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Guide](https://phpsec.org/)