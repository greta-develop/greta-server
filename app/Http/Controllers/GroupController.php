<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * 이용자가 속한 그룹의 리스트를 반환합니다.
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     * @SWG\Get(
     *     path="/groups",
     *     description="이용자가 속한 그룹의 리스트를 반환합니다.",
     *     produces={"application/json"},
     *     tags={"Group"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful User Group List"
     *     ),
     * )
     */
    public function index() {
        return Group::where('user_id', User::find(Auth::user()->id)->id)->get();
    }

    /**
     * 이용자가 그룹을 생성합니다. (Deprecated)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     *
     * @SWG\Post(
     *     path="/groups",
     *     description="이용자가 그룹을 생성합니다.",
     *     produces={"application/json"},
     *     tags={"Group"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="subject",
     *         in="query",
     *         description="Group Subject",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="alias_url",
     *         in="query",
     *         description="Group Alias URL",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Create User Group"
     *     ),
     * )
     */
    public function store(Request $request) {
        try {
            DB::beginTransaction();

            Group::create([
                'user_id' => User::find(Auth::user()->id)->id,
                'subject' => $request->subject,
                'alias_url' => ! empty($request->alias_url) ?: '',
            ]);
        } catch (\Exception $exception) {
            DB::rollback();
            return response()->json([
                'message' => $exception->getMessage(),
            ], 501);
        }

        return response()->json([
            'message' => 'Successful Create User Group',
        ], 201);
    }
}
