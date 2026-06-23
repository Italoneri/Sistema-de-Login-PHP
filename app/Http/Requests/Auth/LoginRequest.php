<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Propositalmente SEM regra `exists:users` aqui. Validar existência
     * do e-mail nesta camada faria o Laravel devolver um erro de
     * validação diferente para "e-mail não cadastrado" vs "e-mail
     * cadastrado mas senha errada" — isso é enumeração de contas.
     * A verificação de credenciais (e a mensagem genérica de erro)
     * fica inteiramente no LoginController.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
