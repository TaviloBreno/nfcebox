<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\CompanyConfig;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class ConfigurationController extends Controller
{
    public function __construct()
    {
        // Middleware aplicado nas rotas em web.php
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
            'corporate_name' => 'required|string|max:255',
            'trade_name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:18',
            'ie' => 'required|string|max:50',
            'im' => 'nullable|string|max:50',
            'address_json' => 'required|string|max:1000',
            'environment' => 'required|in:homologacao,producao',
            'nfce_series' => 'required|integer|min:1|max:999',
            'nfce_number' => 'required|integer|min:1',
            'csc_id' => 'required|string|max:6',
            'csc_token' => 'required|string|max:255',
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
     * Exibir formulário de criação de usuário
     */
    public function createUser()
    {
        return view('configurations.create-user');
    }

    /**
     * Criar novo usuário
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'is_admin' => 'boolean'
        ], [
            'password.regex' => 'A senha deve conter pelo menos: 1 letra minúscula, 1 maiúscula, 1 número e 1 caractere especial.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        event(new Registered($user));

        return redirect()->route('configurations.users')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Atualizar permissões de usuário
     */
    public function updateUserPermissions(Request $request, $userId)
    {
        $request->validate([
            'is_admin' => 'boolean'
        ]);

        $user = User::findOrFail($userId);
        $user->is_admin = $request->has('is_admin');
        $user->save();

        return redirect()->route('configurations.users')->with('success', 'Permissões do usuário atualizadas com sucesso!');
    }

    /**
     * Exibir página de gerenciamento de certificados
     */
    public function certificates()
    {
        $certificates = Certificate::orderBy('is_default', 'desc')
                                 ->orderBy('created_at', 'desc')
                                 ->get();
        
        return view('configurations.certificates', compact('certificates'));
    }

    /**
     * Upload de certificado A1
     */
    public function uploadCertificate(Request $request)
    {
        $request->validate([
            'certificate_file' => 'required|file|mimes:pfx,p12|max:5120', // 5MB
            'certificate_password' => 'required|string|min:1',
            'certificate_alias' => 'required|string|max:100',
            'set_as_default' => 'nullable|boolean'
        ]);

        try {
            $file = $request->file('certificate_file');
            $alias = $request->certificate_alias;
            $password = $request->certificate_password;
            $setAsDefault = $request->boolean('set_as_default');

            // Criar diretório seguro se não existir
            $secureDir = 'secure/certs';
            if (!Storage::exists($secureDir)) {
                Storage::makeDirectory($secureDir, 0700, true);
            }

            // Gerar nome único para o arquivo
            $fileName = uniqid('cert_') . '.' . $file->getClientOriginalExtension();
            $filePath = $secureDir . '/' . $fileName;

            // Salvar arquivo
            Storage::put($filePath, file_get_contents($file->getRealPath()));

            // Tentar ler informações do certificado
            $certInfo = $this->extractCertificateInfo($file->getRealPath(), $password);

            // Se deve ser padrão, remover padrão dos outros
            if ($setAsDefault) {
                Certificate::where('is_default', true)->update(['is_default' => false]);
            }

            // Criar registro no banco
            $certificate = Certificate::create([
                'alias' => $alias,
                'file_path' => $filePath,
                'password' => Crypt::encryptString($password),
                'subject' => $certInfo['subject'] ?? null,
                'issuer' => $certInfo['issuer'] ?? null,
                'expires_at' => $certInfo['expires_at'] ?? null,
                'is_valid' => $certInfo['is_valid'] ?? false,
                'is_default' => $setAsDefault || Certificate::count() === 0, // Primeiro certificado é sempre padrão
            ]);

            Log::info('Certificado A1 carregado com sucesso', [
                'certificate_id' => $certificate->id,
                'alias' => $alias,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('configurations.certificates')
                           ->with('success', 'Certificado carregado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Erro ao carregar certificado A1', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                           ->with('error', 'Erro ao carregar certificado: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Definir certificado como padrão
     */
    public function setDefaultCertificate(Certificate $certificate)
    {
        try {
            // Remover padrão de todos os certificados
            Certificate::where('is_default', true)->update(['is_default' => false]);
            
            // Definir este como padrão
            $certificate->update(['is_default' => true]);

            Log::info('Certificado definido como padrão', [
                'certificate_id' => $certificate->id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certificado definido como padrão com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao definir certificado como padrão', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao definir certificado como padrão: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalhes do certificado
     */
    public function certificateDetails(Certificate $certificate)
    {
        try {
            $details = [
                'alias' => $certificate->alias,
                'subject' => $certificate->subject,
                'issuer' => $certificate->issuer,
                'expires_at' => $certificate->expires_at ? $certificate->expires_at->format('d/m/Y H:i:s') : 'N/A',
                'is_valid' => $certificate->is_valid,
                'is_default' => $certificate->is_default,
                'created_at' => $certificate->created_at->format('d/m/Y H:i:s'),
                'file_size' => Storage::exists($certificate->file_path) ? 
                              number_format(Storage::size($certificate->file_path) / 1024, 2) . ' KB' : 'N/A'
            ];

            $html = view('configurations.partials.certificate-details', compact('details'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar detalhes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir certificado
     */
    public function deleteCertificate(Certificate $certificate)
    {
        try {
            // Não permitir excluir o certificado padrão se for o único
            if ($certificate->is_default && Certificate::count() === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir o único certificado do sistema.'
                ], 400);
            }

            // Excluir arquivo físico
            if (Storage::exists($certificate->file_path)) {
                Storage::delete($certificate->file_path);
            }

            // Se era o padrão, definir outro como padrão
            if ($certificate->is_default) {
                $nextCertificate = Certificate::where('id', '!=', $certificate->id)->first();
                if ($nextCertificate) {
                    $nextCertificate->update(['is_default' => true]);
                }
            }

            Log::info('Certificado A1 excluído', [
                'certificate_id' => $certificate->id,
                'alias' => $certificate->alias,
                'user_id' => Auth::id()
            ]);

            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Certificado excluído com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir certificado A1', [
                'certificate_id' => $certificate->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir certificado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrair informações do certificado
     */
    private function extractCertificateInfo($filePath, $password)
    {
        try {
            $certData = [];
            
            if (openssl_pkcs12_read(file_get_contents($filePath), $certData, $password)) {
                $certInfo = openssl_x509_parse($certData['cert']);
                
                return [
                    'subject' => $certInfo['subject']['CN'] ?? 'N/A',
                    'issuer' => $certInfo['issuer']['CN'] ?? 'N/A',
                    'expires_at' => isset($certInfo['validTo_time_t']) ? 
                                   \Carbon\Carbon::createFromTimestamp($certInfo['validTo_time_t']) : null,
                    'is_valid' => isset($certInfo['validTo_time_t']) ? 
                                 $certInfo['validTo_time_t'] > time() : false
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Não foi possível extrair informações do certificado', [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'subject' => null,
            'issuer' => null,
            'expires_at' => null,
            'is_valid' => false
        ];
    }
}
