<?php

namespace App\Modules\Backoffice\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Application\Services\BackofficeHomeService;
use App\Modules\Backoffice\Application\Services\BackofficeShellService;
use Illuminate\Contracts\View\View;

final class BackofficeHomeController extends Controller
{
    public function __invoke(
        BackofficeHomeService $homeService,
        BackofficeShellService $shellService,
    ): View {
        return view('backoffice.home', [
            'shell' => $shellService->build(request()->user()),
            'vm' => $homeService->build(),
            'activeMenu' => 'dashboard',
        ]);
    }
}

