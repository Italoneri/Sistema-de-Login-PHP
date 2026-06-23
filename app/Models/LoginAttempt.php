<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de auditoria de tentativas de login (sucesso e falha).
 * Sem relação com User: precisamos registrar tentativas mesmo quando
 * o e-mail informado não existe, para detectar enumeração de contas
 * e aplicar bloqueio por e-mail-alvo independente de ele ser válido.
 */
class LoginAttempt extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'email',
        'ip_address',
        'successful',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
