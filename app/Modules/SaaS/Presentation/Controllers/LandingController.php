<?php

namespace App\Modules\SaaS\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\TenantOnboardingService;
use Illuminate\View\View;

final class LandingController extends Controller
{
    public function __invoke(TenantOnboardingService $onboardingService): View
    {
        return view('public.landing', [
            'plans' => $onboardingService->landingPlans(),
        ]);
    }
}
