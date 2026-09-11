<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\SaaS\Domain\Models\Plan;
use App\Modules\SaaS\Presentation\Requests\UpdatePlanRequest;
use Illuminate\Http\RedirectResponse;

final class BackofficeAdminPlanUpdateController extends Controller
{
    public function __invoke(Plan $plan, UpdatePlanRequest $request, SaaSService $service): RedirectResponse
    {
        $service->updatePlan($plan, $request->validated());

        return redirect()->route('backoffice.admin.plans.index')->with('status', __('admin.plan_updated'));
    }
}
