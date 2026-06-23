<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Password::uncompromised() chama a API do Have I Been Pwned via
        // HTTP. Testes não devem depender de rede — por padrão o fake
        // considera toda senha "segura"; testes específicos de senha
        // vazada sobrescrevem este bind pra simular uma senha comprometida.
        $this->fakeUncompromisedVerifier(safe: true);
    }

    protected function fakeUncompromisedVerifier(bool $safe): void
    {
        $this->app->bind(UncompromisedVerifier::class, fn () => new class($safe) implements UncompromisedVerifier
        {
            public function __construct(private readonly bool $safe)
            {
            }

            public function verify($data)
            {
                return $this->safe;
            }
        });
    }
}
