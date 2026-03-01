<?php

namespace App\Multitenancy;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        $slug = $this->slugFromSubdomain($request->getHost())
            ?? $this->slugFromHeader($request);

        if ($slug === null) {
            return null;
        }

        return Tenant::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    private function slugFromSubdomain(string $host): ?string
    {
        if (preg_match('/^([a-z0-9-]+)\.localhost$/i', $host, $matches) !== 1) {
            return null;
        }

        return strtolower((string) $matches[1]);
    }

    private function slugFromHeader(Request $request): ?string
    {
        $slug = trim((string) $request->header('X-Tenant', ''));

        return $slug !== '' ? strtolower($slug) : null;
    }
}
