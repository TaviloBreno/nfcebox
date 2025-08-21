<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyConfig>
 */
class CompanyConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cnpj' => '11222333000181',
            'ie' => '123.456.789.012',
            'im' => '12345678',
            'corporate_name' => 'NFCEBOX TECNOLOGIA LTDA',
            'trade_name' => 'NFCeBox',
            'address_json' => json_encode([
                'street' => 'Rua das Tecnologias',
                'number' => '123',
                'complement' => 'Sala 456',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zipcode' => '01234-567',
            ]),
            'environment' => 'homologacao',
            'nfce_series' => 1,
            'nfce_number' => 1,
            'csc_id' => '000001',
            'csc_token' => 'ABCDEF123456789ABCDEF123456789ABCDEF12',
        ];
    }
}
