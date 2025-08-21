<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCompany = $this->faker->boolean(30); // 30% chance of being a company
        
        return [
            'name' => $isCompany ? $this->faker->company() : $this->faker->name(),
            'cpf_cnpj' => $isCompany ? $this->generateCnpj() : $this->generateCpf(),
            'ie' => $isCompany ? $this->faker->numerify('###.###.###.###') : null,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber,
            'address_json' => json_encode([
                'street' => $this->faker->streetName(),
                'number' => $this->faker->buildingNumber(),
                'complement' => $this->faker->optional()->secondaryAddress(),
                'neighborhood' => $this->faker->citySuffix(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zipcode' => $this->faker->postcode(),
            ]),
        ];
    }
    
    private function generateCpf(): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= mt_rand(0, 9);
        }
        
        // Calculate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $digit1 = 11 - ($sum % 11);
        if ($digit1 >= 10) $digit1 = 0;
        $cpf .= $digit1;
        
        // Calculate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $digit2 = 11 - ($sum % 11);
        if ($digit2 >= 10) $digit2 = 0;
        $cpf .= $digit2;
        
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    
    private function generateCnpj(): string
    {
        $cnpj = '';
        for ($i = 0; $i < 8; $i++) {
            $cnpj .= mt_rand(0, 9);
        }
        $cnpj .= '0001'; // Branch
        
        // Calculate first digit
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $digit1 = 11 - ($sum % 11);
        if ($digit1 >= 10) $digit1 = 0;
        $cnpj .= $digit1;
        
        // Calculate second digit
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $digit2 = 11 - ($sum % 11);
        if ($digit2 >= 10) $digit2 = 0;
        $cnpj .= $digit2;
        
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
    }
}
