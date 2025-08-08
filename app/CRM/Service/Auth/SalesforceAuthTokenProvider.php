<?php

namespace App\CRM\Service\Auth;

use Illuminate\Support\Facades\Cache;
use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\CacheInterface;

class SalesforceAuthTokenProvider implements AuthTokenProviderInterface
{
    private const RESERVED_CHARACTERS = '{}()/\@:';

    private string $cacheKey;

    public function __construct(
        private SalesforceAuthService $authService,
        private int $cacheTtl = 3500,
    ) {
    }

    public function provide(): string
    {
        return $this->get()['access_token'];
    }

    public function refresh(): void
    {
        $this->clear();
        $this->get();
    }

    public function tokenType(): string
    {
        return $this->get()['token_type'];
    }

    private function cacheKey(): string
    {
        $key = implode('.', [$this::class, 'cacheKey']);

        return $this->cacheKey ??= str_replace(str_split(self::RESERVED_CHARACTERS), '.', $key);
    }

    /**
     * @return array{
     *    access_token: string,
     *    signature: string,
     *    scope: string,
     *    instance_url: string,
     *    id: string,
     *    token_type: string,
     *    issued_at: string
     *  }
     */
    private function get(): array
    {
        return Cache::remember(
            $this->cacheKey(),
            $this->cacheTtl,
            fn () => $this->fetch()
        );
    }

    /**
     * @return array{
     *    access_token: string,
     *    signature: string,
     *    scope: string,
     *    instance_url: string,
     *    id: string,
     *    token_type: string,
     *    issued_at: string
     *  }
     */
    private function fetch(): array
    {
        return $this->authService->token();
    }

    private function clear(): bool
    {
        return Cache::delete($this->cacheKey());
    }
}
