<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LoginAttempt;
use Illuminate\Support\Carbon;

/**
 * Proteção contra brute force usando tabela própria (login_attempts) em
 * vez do RateLimiter/Cache nativo do Laravel — decisão deliberada: tabela
 * em banco dá persistência e trilha de auditoria que um cache com TTL
 * não oferece.
 *
 * Bloqueio por e-mail e por IP são checados separadamente (nunca informar
 * ao chamador qual dos dois bloqueou, pra não revelar se o e-mail existe
 * nem detalhes do ataque sendo mitigado).
 */
class BruteForceProtectionService
{
    public function isBlocked(string $email, string $ipAddress): bool
    {
        return $this->failedAttempts('email', $email) >= $this->maxAttemptsEmail()
            || $this->failedAttempts('ip_address', $ipAddress) >= $this->maxAttemptsIp();
    }

    public function registerAttempt(string $email, string $ipAddress, bool $successful, ?string $userAgent): void
    {
        LoginAttempt::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'successful' => $successful,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Segundos até o bloqueio expirar, usado pro header Retry-After na
     * resposta 429. Baseado na tentativa falha mais antiga ainda dentro
     * da janela (assim que ela "sair" da janela, o bloqueio libera).
     */
    public function secondsUntilUnlock(string $email, string $ipAddress): int
    {
        $oldestRelevantAttempt = LoginAttempt::query()
            ->where(fn ($query) => $query->where('email', $email)->orWhere('ip_address', $ipAddress))
            ->where('successful', false)
            ->where('created_at', '>=', $this->windowStart())
            ->oldest('created_at')
            ->first();

        if (! $oldestRelevantAttempt) {
            return 0;
        }

        $unlocksAt = $oldestRelevantAttempt->created_at->addMinutes($this->windowMinutes());

        return max(0, (int) now()->diffInSeconds($unlocksAt, false));
    }

    private function failedAttempts(string $column, string $value): int
    {
        return LoginAttempt::query()
            ->where($column, $value)
            ->where('successful', false)
            ->where('created_at', '>=', $this->windowStart())
            ->count();
    }

    private function windowStart(): Carbon
    {
        return now()->subMinutes($this->windowMinutes());
    }

    private function windowMinutes(): int
    {
        return (int) config('security.brute_force.window_minutes');
    }

    private function maxAttemptsEmail(): int
    {
        return (int) config('security.brute_force.max_attempts_email');
    }

    private function maxAttemptsIp(): int
    {
        return (int) config('security.brute_force.max_attempts_ip');
    }
}
