<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Permissions/Index');
    }

    public function data(): JsonResponse
    {
        $query = Permission::query()->select('permissions.*');

        return DataTables::eloquent($query)->toJson();
    }
}
