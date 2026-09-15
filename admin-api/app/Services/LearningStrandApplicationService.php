<?php

namespace App\Services;

use App\Models\LearningStrandApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LearningStrandApplicationService
{
    /**
     * Get all learning strand applications.
     * 
     */
    public function index()
    {
        $applications = LearningStrandApplication::with(['learningStrand', 'student'])->get();

        return response()->json($applications);
    }

    /**
     * Get a specific learning strand application by ID.
     * 
     */
    public function show($id)
    {
        $application = LearningStrandApplication::with(['learningStrand', 'student'])->find($id);

        if (!$application) {
            return response()->json([
                'status' => false,
                'message' => 'Application not found.',
                'code' => 404,
            ]);
        }

        return response()->json($application);
    }

    /**
     * Handle student application submission.
     * 
     */
    public function apply($request)
    {
        $validator = Validator::make($request->all(), [
            'learningStrandId' => 'required|exists:learning_strand,id',
            'userId'           => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('type', 'student');
                }),
            ],
        ], [
            'userId.exists' => 'The selected user must be a valid user with student type.',
        ]);

        $validated = $validator->validate();

        $existingApplication = LearningStrandApplication::where('learning_strand_id', $validated['learningStrandId'])
            ->where('user_id', $validated['userId'])
            ->latest('created_at')
            ->first();

        if ($existingApplication) {
            if ($existingApplication->status === 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'You already have a pending application for this learning strand.',
                ], 400);
            }

            if ($existingApplication->reapply_eligible_at && Carbon::now()->lt($existingApplication->reapply_eligible_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not yet eligible to reapply for this learning strand.',
                ], 422);
            }
        }

        $application = LearningStrandApplication::create([
            'learning_strand_id'  => $validated['learningStrandId'],
            'user_id'             => $validated['userId'],
            'status'              => 'pending',
            'reapply_eligible_at' => null,
        ]);

        return response()->json($application);
    }

    /**
     * Handle updating application status.
     * 
     */
    public function updateStatus($request, $id)
    {
        $application = LearningStrandApplication::with('learningStrand')->find($id);

        if (!$application) {
            return response()->json([
                'status' => false,
                'message' => 'Application not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $validated = $validator->validate();

        $reapplyEligibleAt = null;
        if ($validated['status'] === 'rejected' && $application->learningStrand->reapply_cooldown_days > 0) {
            $reapplyEligibleAt = Carbon::now()->addDays($application->learningStrand->reapply_cooldown_days);
        }

        $application->update([
            'status'              => $validated['status'],
            'reapply_eligible_at' => $reapplyEligibleAt,
        ]);

        return response()->json($application);
    }

    /**
     * Handle application deletion.
     * 
     */
    public function destroy($id)
    {
        $application = LearningStrandApplication::find($id);

        if (!$application) {
            return response()->json([
                'status' => false,
                'message' => 'Application not found.',
            ], 404);
        }

        $application->delete();

        return response()->json([
            'status' => true,
            'message' => 'Application deleted successfully.',
        ]);
    }
}