<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeAuditService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BackofficeAdminAuditController extends Controller
{
    public function __invoke(Request $request, BackofficeAuditService $auditService, BackofficeShellService $shellService): View
    {
        return view('backoffice.admin.audit-index', [
            'shell' => $shellService->build($request->user()),
            'vm' => $auditService->globalView($request->only(['tenant_id', 'user', 'action_key', 'date_from', 'date_to'])),
            'activeMenu' => 'admin',
        ]);
    }
}
