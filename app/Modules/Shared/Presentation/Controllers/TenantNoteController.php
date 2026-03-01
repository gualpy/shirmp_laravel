<?php

namespace App\Modules\Shared\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Application\Actions\ListTenantNotesAction;
use Illuminate\Http\JsonResponse;

class TenantNoteController extends Controller
{
    public function index(ListTenantNotesAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action(),
        ]);
    }
}
