<?php

namespace App\Http\Controllers;

use App\User;
use App\Reply;
use App\Group;
use App\Modify;
use App\AdminReply;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * 그룹에 해당하는 거래 내역을 보여줍니다.
     *
     * @param string $url
     * @return string
     *
     * @SWG\Get(
     *     path="/share/{url}",
     *     description="그룹에 해당하는 거래 내역을 보여줍니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="url",
     *         in="path",
     *         description="Short Share URL",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Show Transaction List"
     *     ),
     * )
     */
    public function show(string $url) {
        return Transaction::select('transactions.id', 'handling as content', 'transaction_amount as amount', 'title as detail', 'balance as balance', 'transaction_date as date',
            DB::raw("(CASE WHEN transactions.deposit_amount = 0 THEN 'Card' WHEN transactions.deposit_amount > 0 THEN 'Deposit' ELSE 'Withdraw' END) as type"),
            DB::raw("(CASE WHEN receipts.transaction_id = transactions.id THEN 'Attached' ELSE 'None' END) as receipt"),
            DB::raw("(CASE WHEN (replies.transaction_id = transactions.id) && (replies.status = 'A') THEN 'A' WHEN (replies.transaction_id = transactions.id) && (replies.status = 'U') THEN 'U' ELSE 'N' END) as reply"))
            ->leftJoin('receipts', 'transactions.id', 'receipts.transaction_id')
            ->leftJoin('groups', 'transactions.group_id', 'groups.id')
            ->leftJoin('replies', 'transactions.id', 'replies.transaction_id')
            ->where('group_id', Group::where('alias_url', $url)->first()->id)
            ->groupBy('transactions.id')
            ->groupBy('reply')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * 거래내역을 수정 합니다.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     *
     * @SWG\Post(
     *     path="/transaction/{id}",
     *     description="거래내역을 수정합니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction ID",
     *         required=true,
     *         type="integer"
     *     ),
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
     *         description="Modify Subject",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Modify Transaction Subject"
     *     ),
     * )
     */
    public function modifyTableCell(int $id, Request $request) {
        try {
            $prevTransaction = Transaction::where('id', $id)->first();
            $data = Transaction::where('id', $id)->update([
                'title' => $request->subject,
            ]);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        Modify::create([
            'group_id' => Transaction::where('id', $id)->first()->group_id,
            'transaction_id' => $id,
            'user_id' => User::find(Auth::user()->id)->id,
            'prev_subject' => $prevTransaction->handling,
            'subject' => $request->subject,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $data,
        ], 200);
    }

    /**
     * 그룹내에 수정한 이력을 보여줍니다.
     *
     * @param string $url
     * @return \Illuminate\Http\JsonResponse|string
     *
     * @SWG\Get(
     *     path="/transaction/{url}",
     *     description="그룹내에 수정한 이력을 보여줍니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="url",
     *         in="path",
     *         description="Group Alias URL",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Modify Transaction Records"
     *     ),
     * )
     */
    public function groupModifyRecord(string $url) {
        return Modify::where('group_id', Group::where('alias_url', $url)->first()->id)->get();
    }

    /**
     * 거래내역에 답글을 요청합니다.
     *
     * @param int $id
     * @param Request $request
     * @return mixed
     *
     * @SWG\Post(
     *     path="/transaction/{id}/replies",
     *     description="거래내역에 답글을 요청합니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction ID",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="subject",
     *         in="query",
     *         description="Reply Subject",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="Reply Email",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Transaction Reply"
     *     ),
     * )
     */
    public function reply(int $id, Request $request) {
        return Reply::create([
            'transaction_id' => $id,
            'email' => $request->email,
            'subject' => $request->subject,
            'status' => 'U',
        ]);
    }

    /**
     * 거래내역에 댓글리스트를 불러옵니다.
     *
     * @param int $id
     * @return mixed
     *
     * @SWG\Get(
     *     path="/transaction/{id}/replies",
     *     description="거래내역에 댓글리스트를 불러옵니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Transaction ID",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Transaction Reply List"
     *     ),
     * )
     */
    public function replyShow(int $id) {
        return Reply::where('transaction_id', $id)->get();
    }

    /**
     * 이용자가 거래내역에 남긴 댓글에 관리자가 답글을 남깁니다.
     *
     * @param int $id
     * @param Request $request
     * @return array
     *
     * @SWG\Post(
     *     path="/replies/{id}",
     *     description="이용자가 거래내역에 남긴 댓글에 관리자가 답글을 남깁니다.",
     *     produces={"application/json"},
     *     tags={"Transaction"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reply ID",
     *         required=true,
     *         type="integer"
     *     ),
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
     *         description="Reply Subject",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful Admin Reply"
     *     ),
     * )
     */
    public function adminReply(int $id, Request $request) {
        return AdminReply::create([
            'reply_id' => $id,
            'subject' => $request->subject
        ]);
    }

    public function sync() {

    }
}
