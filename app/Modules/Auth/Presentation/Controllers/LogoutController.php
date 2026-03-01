<?php

namespace App\Modules\Auth\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\LogoutAction;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutAction $action): Response
    {
        $action->execute($request->user());

        return response()->noContent();
    }
}
