<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user_id = auth()->user()->getAuthIdentifier();
        $goalsList = DB::table('goals')
            ->select('*')
            ->where(['user_id' => $user_id])
            ->where(['completed' => false])
            ->get();

        if (count($goalsList) < 1){
            return response()->json(['message' => 'No Goals Yet!']);
        }
        return response()->json($goalsList);
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
            'category' => ['Integer']
        ]);

        $user_id = auth()->user()->getAuthIdentifier();
        $goalExist = Goal::where('user_id',$user_id)
            ->where('title',$fields['title'])->first();

        if ($goalExist !== null) {
            return response()->json(['message' => 'Goal with the same title already added!'],400);
        }

        $fields['user_id'] = $user_id;
        Goal::create($fields);

        return response()->json([
            'message' => 'Successfully added new goal!',
            'goal' => $fields,
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
        $goal = Goal::where('user_id',$user_id)->where('id',$id)->first();

        if (!$goal){
            return response()->json(['message' => 'No goal with ID']);
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
            Goal::where('user_id',$user_id)->where('id',$id)
                ->update(['title' => $inputTitle['title']]);
            $message['message'] = 'Successfully updated goal title!';
        }

        if (isset($fields['description'])){
            $inputDesc = $request->validate(['description' => ['string','min:10']]);
            Goal::where('user_id',$user_id)->where('id',$id)
                ->update(['description' => $inputDesc['description']]);
            array_push($message,'Successfully updated goal description!');
        }

        if (isset($fields['category'])){
            $inputCategory = $request->validate(['category' => ['Integer']]);
            Goal::where('user_id',$user_id)->where('id',$id)
                ->update(['category' => $inputCategory['category']]);
            array_push($message,'Successfully updated goal category!');
        }

        if (isset($fields['due_date'])){
            $inputDueDate = $request->validate(['due_date' => ['Date']]);
            Goal::where('user_id',$user_id)->where('id',$id)
                ->update(['due_date' => $inputDueDate['due_date']]);
            array_push($message,'Successfully updated goal Due Date!');
        }

        if (isset($fields['completed'])){
            $inputDesc = $request->validate(['completed' => ['boolean']]);
            Goal::where('user_id',$user_id)->where('id',$id)
                ->update(['completed' => $inputDesc['completed']]);
            array_push($message,'Successfully updated completed goal!');
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

        $result = DB::table('goals')
            ->where('id',$id)
            ->where('user_id',$user_id)
            ->delete();

        if (!$result){
            return response()->json([
                'message' => 'Error! Such goal not exist!'
            ],400);
        }
        return response()->json(['message' => 'Successfully deleted goal!']);
    }
}
