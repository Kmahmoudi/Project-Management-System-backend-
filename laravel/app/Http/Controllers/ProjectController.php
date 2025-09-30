<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('creator')->latest('id')->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $projects,
            'message' => 'Projects list',
        ]);
    }

    public function show($id)
    {
        $project = Project::with('creator','tasks')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $project,
            'message' => 'Project details',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $data['created_by'] = $request->user()->id;

        $project = Project::create($data);

        return response()->json([
            'success' => true,
            'data'    => $project,
            'message' => 'Project created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);

        $project->update($data);

        return response()->json([
            'success' => true,
            'data'    => $project,
            'message' => 'Project updated',
        ]);
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Project deleted',
        ]);
    }
}
