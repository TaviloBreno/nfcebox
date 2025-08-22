<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'is_admin',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se o usuário é operador de caixa
     */
    public function isOperador(): bool
    {
        return $this->role === 'operador';
    }

    /**
     * Verifica se o usuário é fiscal
     */
    public function isFiscal(): bool
    {
        return $this->role === 'fiscal';
    }

    /**
     * Verifica se o usuário tem permissão para acessar recursos administrativos
     */
    public function canAccessAdmin(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode acessar o PDV e vendas
     */
    public function canAccessPos(): bool
    {
        return $this->isAdmin() || $this->isOperador();
    }

    /**
     * Verifica se o usuário pode acessar recursos fiscais
     */
    public function canAccessFiscal(): bool
    {
        return $this->isAdmin() || $this->isFiscal();
    }

    /**
     * Verifica se o usuário pode gerenciar clientes
     */
    public function canManageCustomers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerenciar produtos
     */
    public function canManageProducts(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerenciar configurações
     */
    public function canManageConfig(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verifica se o usuário pode gerenciar vendas
     */
    public function canManageSales(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Obtém o nome formatado do role
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrador',
            'operador' => 'Operador de Caixa',
            'fiscal' => 'Fiscal',
            default => 'Usuário'
        };
    }
}
