<?php

namespace App\Multitenancy;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    private bool $bypass = false;
    private bool $readOnlyMode = false;

    public function setCurrentTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function currentTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->bypass = false;
        $this->readOnlyMode = false;
    }

    public function enableBypass(): void
    {
        $this->bypass = true;
    }

    public function disableBypass(): void
    {
        $this->bypass = false;
    }

    public function isBypassed(): bool
    {
        return $this->bypass;
    }

    public function setReadOnlyMode(bool $readOnlyMode): void
    {
        $this->readOnlyMode = $readOnlyMode;
    }

    public function isReadOnlyMode(): bool
    {
        return $this->readOnlyMode;
    }
}
