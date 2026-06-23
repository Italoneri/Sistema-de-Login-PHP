<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mass assignment allowlist explícita. Nunca incluir aqui campos
     * sensíveis (email_verified_at, remember_token) — preencher esses só
     * via métodos dedicados do Laravel (markEmailAsVerified, setRememberToken),
     * nunca a partir de input direto do usuário (proteção contra mass assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast 'password' => 'hashed' garante que QUALQUER atribuição a este
     * atributo (Model::create, $user->password = ..., update()) passa
     * automaticamente por Hash::make() usando o driver configurado em
     * config/hashing.php (argon2id). Elimina o risco de alguém esquecer
     * de chamar Hash::make() manualmente em algum ponto do código.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
