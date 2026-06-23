<?php

declare(strict_types=1);

return [

    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'unique' => 'Este :attribute já está em uso.',
    'confirmed' => 'A confirmação de :attribute não corresponde.',
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'max' => [
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'string' => 'O campo :attribute deve ser um texto.',

    'password' => [
        'uncompromised' => 'Esta senha apareceu em um vazamento de dados conhecido. Escolha outra senha.',
    ],

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'token' => 'token',
    ],

];
