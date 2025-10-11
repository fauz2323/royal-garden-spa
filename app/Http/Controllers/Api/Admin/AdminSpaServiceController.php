<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpaServiceRequest;
use App\Http\Requests\UpdateSpaServiceRequest;
use App\Models\SpaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminSpaServiceController extends Controller
{
    /**
     * Display a listing of the spa services.
     */
    public function index()
    {
        $services = SpaService::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Spa services retrieved successfully',
            'data' => [
                'services' => $services
            ]
        ]);
    }

    /**
     * Store a newly created spa service.
     */
    public function store(StoreSpaServiceRequest $request)
    {

        $service = SpaService::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration' => $request->duration,
            'is_active' => $request->boolean('is_active', true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spa service created successfully',
            'data' => [
                'service' => $service
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified spa service.
     */
    public function show($id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Spa service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Spa service retrieved successfully',
            'data' => [
                'service' => $service
            ]
        ]);
    }

    /**
     * Update the specified spa service.
     */
    public function update(UpdateSpaServiceRequest $request, $id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Spa service not found'
            ], Response::HTTP_NOT_FOUND);
        }


        $service->update($request->only([
            'name',
            'description',
            'price',
            'duration',
            'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Spa service updated successfully',
            'data' => [
                'service' => $service->fresh()
            ]
        ]);
    }

    /**
     * Remove the specified spa service.
     */
    public function destroy($id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Spa service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Spa service deleted successfully'
        ]);
    }

    /**
     * Toggle service status (active/inactive).
     */
    public function toggleStatus($id)
    {
        $service = SpaService::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Spa service not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $service->update([
            'is_active' => !$service->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully',
            'data' => [
                'service' => $service->fresh()
            ]
        ]);
    }
}
