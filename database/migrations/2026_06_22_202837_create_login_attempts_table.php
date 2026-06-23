<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de auditoria de tentativas de login, usada pelo
     * BruteForceProtectionService para bloquear por e-mail e por IP.
     * Sem FK pra `users`: precisamos registrar tentativas mesmo com
     * e-mail inexistente, pra detectar enumeração de contas.
     */
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip_address', 45); // suporta IPv6
            $table->boolean('successful')->default(false);
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['email', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
