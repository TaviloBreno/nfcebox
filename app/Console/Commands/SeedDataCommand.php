<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\TestScenarioSeeder;
use Database\Seeders\DevelopmentSeeder;
use Database\Seeders\PerformanceSeeder;

class SeedDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfce:seed {type=basic : Tipo de seed (basic, test, dev, performance, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa diferentes tipos de seeds para o NFCeBox';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');

        $this->info("Executando seeds do tipo: {$type}");

        switch ($type) {
            case 'basic':
                $this->seedBasic();
                break;
            case 'test':
                $this->seedTest();
                break;
            case 'dev':
                $this->seedDevelopment();
                break;
            case 'performance':
                $this->seedPerformance();
                break;
            case 'all':
                $this->seedAll();
                break;
            default:
                $this->error("Tipo de seed inválido: {$type}");
                $this->info('Tipos disponíveis: basic, test, dev, performance, all');
                return 1;
        }

        $this->info('Seeds executados com sucesso!');
        return 0;
    }

    private function seedBasic()
    {
        $this->info('Executando seeds básicos...');
        
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\CompanyConfigSeeder'
        ]);
        
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\CustomerSeeder'
        ]);
        
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\ProductSeeder'
        ]);
        
        $this->info('Seeds básicos concluídos.');
    }

    private function seedTest()
    {
        $this->info('Executando seeds de teste...');
        
        // Primeiro os seeds básicos
        $this->seedBasic();
        
        // Depois os seeds específicos de teste
        Artisan::call('db:seed', [
            '--class' => TestScenarioSeeder::class
        ]);
        
        $this->info('Seeds de teste concluídos.');
    }

    private function seedDevelopment()
    {
        $this->info('Executando seeds de desenvolvimento...');
        
        // Seeds básicos primeiro
        $this->seedBasic();
        
        // Seeds de vendas
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\SaleSeeder'
        ]);
        
        // Seeds de desenvolvimento
        Artisan::call('db:seed', [
            '--class' => DevelopmentSeeder::class
        ]);
        
        $this->info('Seeds de desenvolvimento concluídos.');
    }

    private function seedPerformance()
    {
        $this->info('Executando seeds de performance...');
        $this->warn('ATENÇÃO: Este processo pode demorar vários minutos e criar milhares de registros.');
        
        if (!$this->confirm('Deseja continuar?')) {
            $this->info('Operação cancelada.');
            return;
        }
        
        // Seeds básicos primeiro
        $this->seedBasic();
        
        // Seeds de performance
        Artisan::call('db:seed', [
            '--class' => PerformanceSeeder::class
        ]);
        
        $this->info('Seeds de performance concluídos.');
    }

    private function seedAll()
    {
        $this->info('Executando todos os seeds...');
        $this->warn('ATENÇÃO: Este processo criará uma grande quantidade de dados.');
        
        if (!$this->confirm('Deseja continuar?')) {
            $this->info('Operação cancelada.');
            return;
        }
        
        // Executar seeds básicos
        $this->seedBasic();
        
        // Seeds de vendas
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\SaleSeeder'
        ]);
        
        // Seeds de teste
        Artisan::call('db:seed', [
            '--class' => TestScenarioSeeder::class
        ]);
        
        // Seeds de desenvolvimento
        Artisan::call('db:seed', [
            '--class' => DevelopmentSeeder::class
        ]);
        
        // Perguntar se quer incluir seeds de performance
        if ($this->confirm('Incluir seeds de performance? (pode demorar muito)')) {
            Artisan::call('db:seed', [
                '--class' => PerformanceSeeder::class
            ]);
        }
        
        $this->info('Todos os seeds concluídos.');
    }
}