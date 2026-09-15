<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LearningStrandService;
use Illuminate\Http\Request;

class LearningStrandController extends Controller
{
    protected LearningStrandService $learningStrandService;

    /***
     * Construct
     * 
     */
    public function __construct(LearningStrandService $learningStrandService)
    {
        $this->learningStrandService = $learningStrandService;
    }

    /**
     * List all learning strands function
     * 
     */
    public function index()
    {
        return $this->learningStrandService->index();
    }

    /**
     * Show single learning strand function
     * 
     */
    public function show($id)
    {
        return $this->learningStrandService->show($id);
    }

    /**
     * Create learning strand function
     * 
     */
    public function store(Request $request)
    {
        return $this->learningStrandService->store($request);
    }

    /**
     * Update learning strand function
     * 
     */
    public function update(Request $request, $id)
    {
        return $this->learningStrandService->update($request, $id);
    }

    /**
     * Delete learning strand function
     * 
     */
    public function destroy($id)
    {
        return $this->learningStrandService->destroy($id);
    }
}