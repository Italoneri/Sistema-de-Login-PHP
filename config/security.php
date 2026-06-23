<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proteção contra Brute Force
    |--------------------------------------------------------------------------
    |
    | Limites de tentativas falhas de login dentro de uma janela de tempo.
    | Dois limites separados e independentes:
    | - "email": protege a conta-alvo (limite mais restritivo)
    | - "ip": protege contra spray attack vindo do mesmo IP contra várias
    |   contas (limite mais permissivo, pois pode haver usuários legítimos
    |   atrás do mesmo NAT/proxy corporativo)
    |
    */

    'brute_force' => [
        'max_attempts_email' => (int) env('BRUTE_FORCE_MAX_ATTEMPTS_EMAIL', 5),
        'max_attempts_ip' => (int) env('BRUTE_FORCE_MAX_ATTEMPTS_IP', 20),
        'window_minutes' => (int) env('BRUTE_FORCE_WINDOW_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout de Inatividade
    |--------------------------------------------------------------------------
    |
    | Diferente do "lifetime" do session.php (que é absoluto desde a criação
    | do cookie), este timeout é de INATIVIDADE: conta a partir do último
    | request do usuário autenticado, renovando a cada interação.
    |
    */

    'inactivity_timeout_minutes' => (int) env('AUTH_INACTIVITY_TIMEOUT_MINUTES', 15),

];
