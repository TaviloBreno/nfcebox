<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário administrador
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@nfcebox.com',
            'email_verified_at' => now(),
        ]);

        // Criar usuário de teste
        User::factory()->create([
            'name' => 'Usuário Teste',
            'email' => 'teste@nfcebox.com',
            'email_verified_at' => now(),
        ]);

        // Criar usuário operador
        User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@nfcebox.com',
            'email_verified_at' => now(),
        ]);

        // Criar usuário gerente
        User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@nfcebox.com',
            'email_verified_at' => now(),
        ]);

        // Criar mais usuários aleatórios para teste
        User::factory(5)->create([
            'email_verified_at' => now(),
        ]);

        // Chamar seeders específicos
        $this->call([
            CompanyConfigSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            SaleSeeder::class,
        ]);

        // Seeders condicionais baseados no ambiente
        if (app()->environment('local', 'testing')) {
            $this->call([
                TestScenarioSeeder::class,
            ]);
        }

        if (app()->environment('local')) {
            $this->call([
                DevelopmentSeeder::class,
            ]);
        }
    }
}
