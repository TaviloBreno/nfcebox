<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyConfig;

class ConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->is_admin) {
                abort(403, 'Acesso negado. Apenas administradores podem acessar esta área.');
            }
            return $next($request);
        });
    }

    /**
     * Exibir as configurações do sistema
     */
    public function index()
    {
        $config = CompanyConfig::first();
        return view('configurations.index', compact('config'));
    }

    /**
     * Exibir o formulário de edição das configurações
     */
    public function edit()
    {
        $config = CompanyConfig::first();
        return view('configurations.edit', compact('config'));
    }

    /**
     * Atualizar as configurações do sistema
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18',
            'address' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'certificate_path' => 'nullable|string|max:255',
            'certificate_password' => 'nullable|string|max:255',
        ]);

        $config = CompanyConfig::first();
        
        if (!$config) {
            $config = new CompanyConfig();
        }

        $config->fill($request->all());
        $config->save();

        return redirect()->route('configurations.index')->with('success', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Gerenciar usuários do sistema
     */
    public function users()
    {
        $users = \App\Models\User::paginate(10);
        return view('configurations.users', compact('users'));
    }

    /**
     * Atualizar permissões de usuário
     */
    public function updateUserPermissions(Request $request, $userId)
    {
        $request->validate([
            'is_admin' => 'boolean'
        ]);

        $user = \App\Models\User::findOrFail($userId);
        $user->is_admin = $request->has('is_admin');
        $user->save();

        return redirect()->route('configurations.users')->with('success', 'Permissões do usuário atualizadas com sucesso!');
    }
}
