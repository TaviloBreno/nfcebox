<?php

namespace App\Http\Controllers;

use App\Models\CompanyConfig;
use App\Models\Inutilization;
use App\Services\Fiscal\SefazClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class InutilizationController extends Controller
{
    /**
     * Exibe a lista de inutilizações
     */
    public function index(Request $request)
    {
        $query = Inutilization::with('user')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('series')) {
            $query->where('series', $request->series);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        $inutilizations = $query->paginate(15)->withQueryString();

        return view('inutilizations.index', compact('inutilizations'));
    }

    /**
     * Exibe o formulário de criação de inutilização
     */
    public function create()
    {
        return view('inutilizations.create');
    }

    /**
     * Processa a criação de uma nova inutilização
     */
    public function store(Request $request)
    {
        $request->validate([
            'series' => 'required|string|max:3',
            'numero_inicial' => 'required|integer|min:1|max:999999999',
            'numero_final' => 'required|integer|min:1|max:999999999|gte:numero_inicial',
            'justificativa' => 'required|string|min:15|max:255',
        ], [
            'series.required' => 'A série é obrigatória.',
            'series.max' => 'A série deve ter no máximo 3 caracteres.',
            'numero_inicial.required' => 'O número inicial é obrigatório.',
            'numero_inicial.integer' => 'O número inicial deve ser um número inteiro.',
            'numero_inicial.min' => 'O número inicial deve ser maior que zero.',
            'numero_inicial.max' => 'O número inicial deve ter no máximo 9 dígitos.',
            'numero_final.required' => 'O número final é obrigatório.',
            'numero_final.integer' => 'O número final deve ser um número inteiro.',
            'numero_final.min' => 'O número final deve ser maior que zero.',
            'numero_final.max' => 'O número final deve ter no máximo 9 dígitos.',
            'numero_final.gte' => 'O número final deve ser maior ou igual ao número inicial.',
            'justificativa.required' => 'A justificativa é obrigatória.',
            'justificativa.min' => 'A justificativa deve ter pelo menos 15 caracteres.',
            'justificativa.max' => 'A justificativa deve ter no máximo 255 caracteres.',
        ]);

        // Verifica se já existe inutilização para esta faixa
        $existingInutilization = Inutilization::where('series', $request->series)
            ->where(function ($query) use ($request) {
                $query->whereBetween('numero_inicial', [$request->numero_inicial, $request->numero_final])
                    ->orWhereBetween('numero_final', [$request->numero_inicial, $request->numero_final])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('numero_inicial', '<=', $request->numero_inicial)
                          ->where('numero_final', '>=', $request->numero_final);
                    });
            })
            ->where('status', '!=', 'error')
            ->first();

        if ($existingInutilization) {
            return back()->withErrors([
                'numero_inicial' => 'Já existe uma inutilização que sobrepõe esta faixa de números.'
            ])->withInput();
        }

        try {
            // Cria o registro de inutilização
            $inutilization = Inutilization::create([
                'user_id' => Auth::id(),
                'series' => $request->series,
                'numero_inicial' => $request->numero_inicial,
                'numero_final' => $request->numero_final,
                'justificativa' => $request->justificativa,
                'status' => 'pending',
            ]);

            // Tenta enviar para a SEFAZ
            $this->processInutilization($inutilization);

            return redirect()->route('inutilizations.index')
                ->with('success', 'Inutilização criada com sucesso. Status: ' . $inutilization->fresh()->status_label);

        } catch (\Exception $e) {
            Log::error('Erro ao criar inutilização', [
                'user_id' => Auth::id(),
                'series' => $request->series,
                'numero_inicial' => $request->numero_inicial,
                'numero_final' => $request->numero_final,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'general' => 'Erro interno ao processar inutilização: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Exibe os detalhes de uma inutilização
     */
    public function show(Inutilization $inutilization)
    {
        return view('inutilizations.show', compact('inutilization'));
    }

    /**
     * Reprocessa uma inutilização com erro
     */
    public function retry(Inutilization $inutilization)
    {
        if (!$inutilization->hasError() && !$inutilization->isPending()) {
            return back()->withErrors([
                'general' => 'Apenas inutilizações com erro ou pendentes podem ser reprocessadas.'
            ]);
        }

        if ($inutilization->retry_count >= 5) {
            return back()->withErrors([
                'general' => 'Número máximo de tentativas excedido.'
            ]);
        }

        try {
            $this->processInutilization($inutilization);

            return back()->with('success', 'Inutilização reprocessada. Status: ' . $inutilization->fresh()->status_label);

        } catch (\Exception $e) {
            Log::error('Erro ao reprocessar inutilização', [
                'inutilization_id' => $inutilization->id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'general' => 'Erro ao reprocessar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Baixa o XML da inutilização
     */
    public function downloadXml(Inutilization $inutilization)
    {
        if (!$inutilization->xml_path || !Storage::disk('local')->exists($inutilization->xml_path)) {
            return back()->withErrors([
                'general' => 'XML não encontrado para esta inutilização.'
            ]);
        }

        $filename = "inutilizacao_{$inutilization->series}_{$inutilization->faixa}.xml";
        
        return Storage::disk('local')->download($inutilization->xml_path, $filename);
    }

    /**
     * Processa a inutilização na SEFAZ
     */
    private function processInutilization(Inutilization $inutilization)
    {
        // Busca a configuração da empresa
        $companyConfig = CompanyConfig::first();
        if (!$companyConfig) {
            $inutilization->markAsError(null, 'Configuração da empresa não encontrada');
            throw new \Exception('Configuração da empresa não encontrada');
        }

        // Incrementa contador de tentativas
        $inutilization->incrementRetryCount();

        // Cria o cliente SEFAZ
        $sefazClient = new SefazClient($companyConfig);

        // Envia para a SEFAZ
        $response = $sefazClient->inutilizeRange(
            $inutilization->series,
            $inutilization->numero_inicial,
            $inutilization->numero_final,
            $inutilization->justificativa
        );

        // Atualiza o registro com a resposta
        $inutilization->update([
            'sefaz_response' => json_encode($response)
        ]);

        if ($response['success']) {
            $inutilization->markAsAuthorized(
                $response['protocol'],
                $response['xml_path'] ?? null
            );
        } else {
            $errorCode = $response['code'] ?? null;
            $errorMessage = $response['message'] ?? 'Erro desconhecido';
            
            // Se for erro temporário, mantém como pending para retry
            if ($this->isTemporaryError($errorCode)) {
                $inutilization->update([
                    'sefaz_error_code' => $errorCode,
                    'sefaz_error_message' => $errorMessage,
                ]);
            } else {
                $inutilization->markAsRejected($errorCode, $errorMessage);
            }
        }
    }

    /**
     * Verifica se é um erro temporário que permite retry
     */
    private function isTemporaryError(?string $errorCode): bool
    {
        $temporaryErrors = [
            '108', // Serviço paralisado momentaneamente
            '109', // Serviço paralisado sem previsão
            '999', // Erro interno do servidor
        ];

        return in_array($errorCode, $temporaryErrors);
    }

    /**
     * API endpoint para buscar inutilizações via AJAX
     */
    public function api(Request $request)
    {
        $query = Inutilization::with('user')
            ->orderBy('created_at', 'desc');

        // Aplicar filtros se fornecidos
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('series')) {
            $query->where('series', $request->series);
        }

        $inutilizations = $query->paginate(10);

        return response()->json([
            'data' => $inutilizations->items(),
            'pagination' => [
                'current_page' => $inutilizations->currentPage(),
                'last_page' => $inutilizations->lastPage(),
                'per_page' => $inutilizations->perPage(),
                'total' => $inutilizations->total(),
            ]
        ]);
    }
}
