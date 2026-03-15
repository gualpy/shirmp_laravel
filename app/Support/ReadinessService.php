<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ReadinessService
{
    /**
     * @return array{status:string,checks:array<string,bool>,message?:string}
     */
    public function check(): array
    {
        $checks = [
            'app_key' => $this->hasAppKey(),
            'db' => $this->databaseReady(),
            'cache' => $this->cacheReady(),
        ];

        if (in_array(false, $checks, true)) {
            return [
                'status' => 'error',
                'checks' => $checks,
                'message' => 'Application is not ready.',
            ];
        }

        return [
            'status' => 'ok',
            'checks' => $checks,
        ];
    }

    private function hasAppKey(): bool
    {
        $appKey = config('app.key');

        return is_string($appKey) && trim($appKey) !== '';
    }

    private function databaseReady(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function cacheReady(): bool
    {
        $key = 'readyz:'.bin2hex(random_bytes(8));

        try {
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return $value === 'ok';
        } catch (\Throwable) {
            return false;
        }
    }
}
