<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();
        $tasksList = DB::table('tasks')
            ->select('*')
            ->where(['user_id' => $user_id])
            ->where('status','!=','Completed')
            ->get();

        if (count($tasksList) < 1){
            return response()->json(['message' => 'No Tasks Yet!']);
        }
        return response()->json($tasksList);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'title' => ['required','string'],
            'description' => ['string'],
            'due_date' => ['date'],
            'priority' => ['string'],
            'goal_id' => ['Integer'],
            'task_id' => ['Integer'],
        ]);

        $user_id = auth()->user()->getAuthIdentifier();

        //Check if goal ID exist
        if (isset($fields['goal_id'])){
            $goalExist = Goal::where('user_id',$user_id)
                ->where('id',$fields['goal_id'])->first();
            if (!$goalExist){
                return response()->json(['message' => 'There is no goal with this ID!'],400);
            }
        }

        //Check if parent task exists
        if (isset($fields['task_id'])){
            $taskExist = Task::where('user_id',$user_id)
                ->where('id',$fields['task_id'])->first();
            if (!$taskExist){
                return response()->json(['message' => 'There is no parent task with this ID!'],400);
            }
        }

        //Check if task title exist
        $taskTitleExist = Task::where('user_id',$user_id)
            ->where('title',$fields['title'])->first();

        if ($taskTitleExist !== null) {
            return response()->json(['message' => 'Task with the same title already added! Just set it recur!'],400);
        }

        $fields['user_id'] = $user_id;

        Task::create($fields);

        return response()->json([
            'message' => 'Successfully added new task!',
            'task' => $fields,
        ],201);

    }

    /**
     * Display the specified resource.
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();
        $goal = Task::where('user_id',$user_id)
            ->where('id',$id)->first();

        if (!$goal){
            return response()->json(['message' => 'No task with such ID']);
        }
        return response()->json($goal);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();

        $fields = $request->all();

        $message = [];

        if (isset($fields['title'])){
            $inputTitle = $request->validate(['title' => ['string','min:10','max:455']]);
            Task::where('user_id',$user_id)->where('id',$id)
                ->update(['title' => $inputTitle['title']]);
            $message['message'] = 'Successfully updated task title!';
        }

        if (isset($fields['description'])){
            $inputDesc = $request->validate(['description' => ['string','min:10']]);
            Task::where('user_id',$user_id)->where('id',$id)
                ->update(['description' => $inputDesc['description']]);
            array_push($message,'Successfully updated task description!');
        }

        if (isset($fields['status'])){
            $inputCategory = $request->validate(['status' => ['string']]);
            Task::where('user_id',$user_id)->where('id',$id)
                ->update(['status' => $inputCategory['status']]);
            array_push($message,'Successfully updated task status!');
        }

        if (isset($fields['due_date'])){
            $inputDueDate = $request->validate(['due_date' => ['Date']]);
            Task::where('user_id',$user_id)->where('id',$id)
                ->update(['due_date' => $inputDueDate['due_date']]);
            array_push($message,'Successfully updated task Due Date!');
        }

        if (isset($fields['priority'])){
            $inputDesc = $request->validate(['priority' => ['string']]);
            Task::where('user_id',$user_id)->where('id',$id)
                ->update(['priority' => $inputDesc['priority']]);
            array_push($message,'Successfully updated task priority!');
        }

        return response()->json($message);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();

        $result = DB::table('tasks')
            ->where('id',$id)
            ->where('user_id',$user_id)
            ->delete();

        if (!$result){
            return response()->json([
                'message' => 'Error! Such task not exist!'
            ],400);
        }
        return response()->json(['message' => 'Successfully deleted task!']);
    }
}
