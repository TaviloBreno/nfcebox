<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            ['name' => 'Notebook Dell Inspiron', 'description' => 'Notebook Dell Inspiron 15 3000, Intel Core i5, 8GB RAM, 256GB SSD', 'ncm' => '84713012', 'unit' => 'UN', 'price_range' => [2500, 4000]],
            ['name' => 'Mouse Sem Fio Logitech', 'description' => 'Mouse óptico sem fio com receptor USB', 'ncm' => '84716070', 'unit' => 'UN', 'price_range' => [50, 150]],
            ['name' => 'Teclado Mecânico', 'description' => 'Teclado mecânico RGB com switches blue', 'ncm' => '84716070', 'unit' => 'UN', 'price_range' => [200, 500]],
            ['name' => 'Monitor LED 24"', 'description' => 'Monitor LED Full HD 24 polegadas', 'ncm' => '85285210', 'unit' => 'UN', 'price_range' => [600, 1200]],
            ['name' => 'Smartphone Samsung', 'description' => 'Smartphone Samsung Galaxy A54 128GB', 'ncm' => '85171231', 'unit' => 'UN', 'price_range' => [1200, 2000]],
            ['name' => 'Fone de Ouvido Bluetooth', 'description' => 'Fone de ouvido sem fio com cancelamento de ruído', 'ncm' => '85183000', 'unit' => 'UN', 'price_range' => [150, 800]],
            ['name' => 'Impressora Multifuncional', 'description' => 'Impressora jato de tinta multifuncional com Wi-Fi', 'ncm' => '84433210', 'unit' => 'UN', 'price_range' => [300, 800]],
            ['name' => 'HD Externo 1TB', 'description' => 'HD externo portátil USB 3.0 1TB', 'ncm' => '84717050', 'unit' => 'UN', 'price_range' => [250, 400]],
            ['name' => 'Webcam Full HD', 'description' => 'Webcam Full HD 1080p com microfone integrado', 'ncm' => '85258100', 'unit' => 'UN', 'price_range' => [100, 300]],
            ['name' => 'Carregador Portátil', 'description' => 'Power bank 10000mAh com entrada USB-C', 'ncm' => '85076000', 'unit' => 'UN', 'price_range' => [80, 200]],
        ];
        
        $product = $this->faker->randomElement($products);
        $price = $this->faker->randomFloat(2, $product['price_range'][0], $product['price_range'][1]);
        
        return [
            'name' => $product['name'] . ' ' . $this->faker->randomElement(['Pro', 'Plus', 'Standard', 'Premium', '']),
            'description' => $product['description'],
            'sku' => strtoupper($this->faker->bothify('??###??')),
            'ncm' => $product['ncm'],
            'cfop' => $this->faker->randomElement(['5102', '5405', '5656', '6102', '6108']),
            'cest' => $this->faker->optional(0.3)->numerify('##.###.##'),
            'unit' => $product['unit'],
            'price' => $price,
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
