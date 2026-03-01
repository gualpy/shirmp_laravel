<?php

namespace App\Multitenancy;

use RuntimeException;

class TenantScopeBypass
{
    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(callable $callback)
    {
        if (! app()->runningInConsole()) {
            throw new RuntimeException('Tenant scope bypass is allowed only in CLI/seeders.');
        }

        $context = app(TenantContext::class);
        $context->enableBypass();

        try {
            return $callback();
        } finally {
            $context->disableBypass();
        }
    }
}
