<?php

namespace App\Services;

use App\Models\LearningStrand;
use Illuminate\Support\Facades\Validator;

class LearningStrandService
{
    /**
     * Get all learning strands by logged-in user.
     * 
     */
    public function index()
    {
        $user = auth()->user();
        $learningStrands = LearningStrand::where('user_id', $user->id)->get();

        return response()->json($learningStrands);
    }

    /**
     * Get a specific learning strand.
     * 
     */
    public function show($id)
    {
        $learningStrand = LearningStrand::with(['activities.questions.options'])->find($id);

        if (!$learningStrand) {
            return response()->json([
                'status' => false,
                'message' => 'Learning Strand not found.',
                'code' => 404,
            ], 404);
        }

        return response()->json($learningStrand);
    }
    
    /**
     * Handle learning strand creation.
     * 
     */
    public function store($request)
    {
        $user = auth()->user();

        if ($user->type !== 'teacher') {
            return response()->json([
                'message' => 'Unauthorized. Only teachers can create learning strands.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'required|string',
            'description'  => 'nullable|string',
            'tags'         => 'nullable|array',
            'cooldownDays' => 'nullable|integer',
        ], [
            'userId.exists' => 'The selected user must be a valid user with teacher type.',
        ]);

        $validated = $validator->validate();

        $learningStrand = LearningStrand::create([
            'title'                 => $validated['title'],
            'description'           => $validated['description'] ?? null,
            'user_id'               => $user->id,
            'learning_strand_tags'  => $validated['tags'] ?? null,
            'reapply_cooldown_days' => $validated['cooldownDays'] ?? 0,
        ]);

        return response()->json($learningStrand);
    }

    /**
     * Handle learning strand update.
     */
    public function update($request, $id)
    {
        $user = auth()->user();

        if ($user->type !== 'teacher') {
            return response()->json([
                'message' => 'Unauthorized. Only teachers can update learning strands.'
            ], 403);
        }

        $learningStrand = LearningStrand::find($id);

        if (!$learningStrand) {
            return response()->json([
                'status' => false,
                'message' => 'Learning Strand not found.',
            ], 404);
        }

        if ($learningStrand->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to update this learning strand.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'sometimes|required|string',
            'description'  => 'nullable|string',
            'tags'         => 'nullable|array',
            'status'       => 'sometimes|required|string|in:draft,active,inactive',
            'cooldownDays' => 'nullable|integer',
        ]);

        $validated = $validator->validate();

        $learningStrand->update([
            'title' => $validated['title'] ?? $learningStrand->title,
            'description' => array_key_exists('description', $validated) 
                ? $validated['description'] 
                : $learningStrand->description,
            'learning_strand_tags' => array_key_exists('tags', $validated) 
                ? $validated['tags'] 
                : $learningStrand->learning_strand_tags,
            'reapply_cooldown_days' => array_key_exists('cooldownDays', $validated) 
                ? $validated['cooldownDays'] 
                : $learningStrand->reapply_cooldown_days,
            'status' => $validated['status'] ?? $learningStrand->status,
        ]);

        return response()->json($learningStrand);
    }

    /**
     * Handle learning strand deletion.
     * 
     */
    public function destroy($id)
    {
        $user = auth()->user();

        if ($user->type !== 'teacher') {
            return response()->json([
                'message' => 'Unauthorized. Only teachers can delete learning strands.'
            ], 403);
        }
        
        $learningStrand = LearningStrand::find($id);

        if (!$learningStrand) {
            return response()->json([
                'status' => false,
                'message' => 'Learning Strand not found.',
            ], 404);
        }

        if ($learningStrand->user_id !== $user->id) {
            return response()->json([
                'message' => 'Forbidden. You do not have permission to delete this learning strand.'
            ], 403);
        }

        $learningStrand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Learning Strand deleted successfully.',
        ]);
    }
}