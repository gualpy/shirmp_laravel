<?php

namespace App\Modules\Suppliers\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Suppliers\Application\Actions\CreateSupplierAction;
use App\Modules\Suppliers\Application\Actions\ListSuppliersAction;
use App\Modules\Suppliers\Application\Actions\UpdateSupplierAction;
use App\Modules\Suppliers\Application\DTO\SupplierDataDTO;
use App\Modules\Suppliers\Domain\Models\Supplier;
use App\Modules\Suppliers\Presentation\Requests\StoreSupplierRequest;
use App\Modules\Suppliers\Presentation\Requests\UpdateSupplierRequest;
use App\Modules\Suppliers\Presentation\Resources\SupplierResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SupplierController extends Controller
{
    public function index(Request $request, ListSuppliersAction $action): AnonymousResourceCollection
    {
        return SupplierResource::collection($action->execute($request->query('type')));
    }

    public function store(StoreSupplierRequest $request, CreateSupplierAction $action): JsonResponse
    {
        $supplier = $action->execute(SupplierDataDTO::fromArray($request->validated()));

        return (new SupplierResource($supplier))->response()->setStatusCode(201);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier, UpdateSupplierAction $action): SupplierResource
    {
        $payload = array_merge(
            $supplier->only(['name', 'type', 'code', 'contact_name', 'phone', 'email', 'notes', 'is_active']),
            $request->validated(),
        );

        return new SupplierResource($action->execute($supplier, SupplierDataDTO::fromArray($payload)));
    }
}
