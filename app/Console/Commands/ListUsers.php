<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista todos os usuários criados no sistema NFCeBox';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Usuários Criados no Sistema NFCeBox ===');
        $this->newLine();

        $users = User::all(['name', 'email', 'email_verified_at', 'created_at']);

        $this->info('Total de usuários: ' . $users->count());
        $this->newLine();

        $headers = ['Nome', 'Email', 'Status', 'Criado em'];
        $rows = [];

        foreach ($users as $user) {
            $verified = $user->email_verified_at ? 'Verificado' : 'Não verificado';
            $rows[] = [
                $user->name,
                $user->email,
                $verified,
                $user->created_at->format('d/m/Y H:i')
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('=== Credenciais de Acesso ===');
        $this->warn('Senha padrão para todos os usuários: password');
        $this->newLine();
        
        $this->info('Usuários principais:');
        $this->line('• admin@nfcebox.com (Administrador)');
        $this->line('• teste@nfcebox.com (Usuário Teste)');
        $this->line('• joao@nfcebox.com (João Silva)');
        $this->line('• maria@nfcebox.com (Maria Santos)');

        return Command::SUCCESS;
    }
}
