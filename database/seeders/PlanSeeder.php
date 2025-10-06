<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('plans')->insert([
            [
                'name' => 'Plano Básico',
                'description' => 'Ideal para iniciantes que desejam experimentar os recursos principais.',
                'amount' => 29.90,
                'duration_days' => 30,
                'plan_type' => 'Mensal',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Plano Intermediário',
                'description' => 'Perfeito para usuários que buscam mais recursos e melhor custo-benefício.',
                'amount' => 59.90,
                'duration_days' => 30,
                'plan_type' => 'Mensal',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Plano Premium Anual',
                'description' => 'Ideal para quem quer aproveitar todos os recursos com o melhor desconto anual.',
                'amount' => 599.00,
                'duration_days' => 365,
                'plan_type' => 'Anual',
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Plano Standart',
                'description' => 'Ideal para quem quer aproveitar todos os recursos com o melhor desconto anual.',
                'amount' => 5.90,
                'duration_days' => 7,
                'plan_type' => 'Semanal',
                'active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
