<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     */
    protected $fillable = [
        'name',
        'description',
        'amount',
        'duration_days',
        'plan_type',
        'active',
    ];

    /**
     * Relação com assinaturas (Subscription)
     * Um plano pode ter muitas assinaturas.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPlansFormattedForBot()
    {
        $result = [];
        $plans = $this->where('active', true)->get();

        if ($plans->isEmpty()) return false;

        foreach ($plans as $plan) {
            $result['inline_keyboard'][] = [
                [
                    'text' => "{$plan->name} - R$ " . number_format($plan->amount, 2, ',', '.'),
                    'callback_data' => "select_plan_{$plan->id}"
                ]
            ];
        }

        return $result;
    }
}
