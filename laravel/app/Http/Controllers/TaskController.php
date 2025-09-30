<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\TaskAssignmentService;
use App\Jobs\SendTaskAssignedNotification;
use App\Models\User;
class TaskController extends Controller
{
    public function indexByProject(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $query = Task::where('project_id', $project->id)->with('assignee');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($q = $request->query('q')) {
            $query->where('title', 'like', "%{$q}%");
        }

        $tasks = $query->latest('id')->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Project tasks',
        ]);
    }

    public function show($id)
    {
        $task = Task::with(['assignee','project'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $task,
            'message' => 'Task details',
        ]);
    }

    // manager-only via route middleware
    public function store(Request $request, $projectId, TaskAssignmentService $assignmentService)
    {
        $project = Project::findOrFail($projectId);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'in:pending,in-progress,done',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = new Task([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'pending',
            'due_date'    => $data['due_date'] ?? null,
            'project_id'  => $project->id,
            'assigned_to' => $data['assigned_to'] ?? null,
        ]);

        $task->save();

        if (!empty($data['assigned_to'])) {
            $assignmentService->assign($task, (int)$data['assigned_to']);
            // queue a notification
            SendTaskAssignedNotification::dispatch($task->assigned_to, $task->id);
        }

        return response()->json([
            'success' => true,
            'data'    => $task->load('assignee'),
            'message' => 'Task created',
        ], 201);
    }

    // manager OR assigned user can update; only manager may reassign
    public function update(Request $request, $id, TaskAssignmentService $assignmentService)
    {
        $task = Task::findOrFail($id);
        $user = $request->user();

        $canUpdate = ($user->role === 'manager') || ($task->assigned_to === $user->id);
        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Forbidden: only manager or the assigned user can update this task',
            ], 403);
        }

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'in:pending,in-progress,done',
            'due_date'    => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Only manager can reassign
        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== null) {
            if ($user->role !== 'manager') {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Only managers can reassign tasks',
                ], 403);
            }

            // perform validated assignment
            $assignmentService->assign($task, (int)$data['assigned_to']);
            SendTaskAssignedNotification::dispatch($task->assigned_to, $task->id);

            // Remove from $data so we don't double-update below
            unset($data['assigned_to']);
        }

        if (!empty($data)) {
            $task->update($data);
        }

        return response()->json([
            'success' => true,
            'data'    => $task->load('assignee'),
            'message' => 'Task updated',
        ]);
    }

    // manager-only via route middleware
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Task deleted',
        ]);
    }
}
