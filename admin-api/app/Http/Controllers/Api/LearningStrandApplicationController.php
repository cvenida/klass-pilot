<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LearningStrandApplicationService;
use Illuminate\Http\Request;

class LearningStrandApplicationController extends Controller
{
    protected LearningStrandApplicationService $learningStrandApplicationService;

    /***
     * Construct
     * 
     */
    public function __construct(LearningStrandApplicationService $learningStrandApplicationService)
    {
        $this->learningStrandApplicationService = $learningStrandApplicationService;
    }

    /**
     * List all applications function
     * 
     */
    public function index()
    {
        return $this->learningStrandApplicationService->index();
    }

    /**
     * Show single application function
     * 
     */
    public function show($id)
    {
        return $this->learningStrandApplicationService->show($id);
    }

    /**
     * Apply for learning strand function
     * 
     */
    public function store(Request $request)
    {
        return $this->learningStrandApplicationService->apply($request);
    }

    /**
     * Update application status function
     * 
     */
    public function updateStatus(Request $request, $id)
    {
        return $this->learningStrandApplicationService->updateStatus($request, $id);
    }

    /**
     * Delete application function
     * 
     */
    public function destroy($id)
    {
        return $this->learningStrandApplicationService->destroy($id);
    }
}