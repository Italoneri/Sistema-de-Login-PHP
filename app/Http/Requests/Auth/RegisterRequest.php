<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Rota pública de cadastro — qualquer visitante pode submeter.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // Validação 'dns' (checagem de registro MX) foi avaliada e
            // descartada: depende de dns_get_record(), que tem suporte
            // inconsistente em builds de PHP pra Windows e pode rejeitar
            // domínios válidos dependendo do servidor. 'rfc' já garante
            // formato de e-mail estrito.
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],

            // Política NIST SP 800-63B: comprimento mínimo importa mais
            // que complexidade de caracteres. uncompromised() consulta a
            // API do Have I Been Pwned (k-anonymity, não envia a senha em
            // texto claro) e rejeita senhas já vazadas publicamente.
            'password' => ['required', 'confirmed', Password::min(12)->uncompromised()],
        ];
    }
}
