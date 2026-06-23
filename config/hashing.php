<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Argon2id é o algoritmo recomendado pela OWASP para hash de senhas em
    | 2026: resistente a ataques de GPU/ASIC por exigir memória, e vencedor
    | da Password Hashing Competition. Bcrypt fica como fallback caso o
    | servidor não tenha a extensão sodium/libargon2 disponível.
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    'argon' => [
        // Memória mínima recomendada pela OWASP (2026) para argon2id com
        // 1 thread e custo balanceado entre segurança e latência de login.
        'memory' => env('ARGON_MEMORY', 65536), // 64 MB
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => true,
    ],

    'rehash_on_login' => true,

];
