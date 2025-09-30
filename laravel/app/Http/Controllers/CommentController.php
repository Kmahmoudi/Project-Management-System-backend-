<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * GET /api/tasks/{task_id}/comments
     * Paginated list of comments on a task
     */
    public function index($taskId)
    {
        $task = Task::findOrFail($taskId);

        $comments = Comment::with('user:id,name')
            ->where('task_id', $task->id)
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $comments,
            'message' => 'Comments list',
        ]);
    }

    /**
     * POST /api/tasks/{task_id}/comments
     * Add a new comment to a task (auth required)
     */
    public function store(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);

        $data = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $comment = Comment::create([
            'body'    => $data['body'],
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
        ])->load('user:id,name');

        return response()->json([
            'success' => true,
            'data'    => $comment,
            'message' => 'Comment added',
        ], 201);
    }
}
