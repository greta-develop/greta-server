<?php

namespace App\Http\Controllers;


use App\Bank;
use App\User;
use App\Group;
use App\Receipt;
use App\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HashController;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class BankController extends Controller
{
    /**
     * @var \App\Http\Controllers\HashController
     */
    protected $hc;

    /**
     * @var Client
     */
    protected $client;

    /**
     * BankController constructor.
     * @param \App\Http\Controllers\HashController $hc
     * @param Client $client
     */
    public function __construct(HashController $hc, Client $client) {
        $this->hc = $hc;
        $this->client = $client;
    }

    /**
     * 이용자의 은행 정보를 저장합니다.
     *
     * @param Request $request
     * @return string
     *
     * @SWG\Post(
     *     path="/auth/users/banks",
     *     description="이용자의 은행 정보를 저장합니다.",
     *     produces={"application/json"},
     *     tags={"Bank"},
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Authorization Token",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="bank_id",
     *         in="query",
     *         description="Bank ID",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="query",
     *         description="Bank Account Name",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="number",
     *         in="query",
     *         description="Bank Account Number",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="verify",
     *         in="query",
     *         description="Bank Account Flag",
     *         required=true,
     *         type="boolean"
     *     ),
     *     @SWG\Parameter(
     *         name="group_subject",
     *         in="query",
     *         description="Group Subject",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="group_url",
     *         in="query",
     *         description="Group Alias URL",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful Create User Bank Account"
     *     ),
     * )
     */
    public function store(Request $request) {
        if (! $request->verify) {
            $header = [
                'uuId' => '단말기 고유ID(UDID)',
                'subChannel' => '채널구분(앱 구분용)',
                'deviceModel' => '단말기 모델명',
                'deviceOs' => '단말기OS명',
                'carrier' => '캐리어명',
                'connectionType' => '연결정보',
                'appName' => '앱이름',
                'appVersion' => '앱버전',
                'scrNo' => '화면번호',
                'scrName' => '화면명',
            ];

            $data = [
                '계좌번호' => $request->number,
                '상품계약회차' => '',
            ];

            $url = config('key.kookmin.endpoint') . 'getAccountHolder';

            $datas = $this->hc->index($header, $data);
            $response = $this->client->post($url, [
                'headers' => [
                    'apiKey' => config('key.kookmin.key'),
                    'hsKey' => $datas['hsKey'],
                    'Content-Type' => 'application/json',
                ],
                'body' => $datas['body']
            ]);

            if (json_decode($response->getBody(), true)['dataHeader']['resultCode'] = '000') {
                return 1;
            } else {
                return 0;
            }
        }

        if (empty($request->group_url)) {
            return response()->json([
                'message' => 'Group URL is required',
            ], 401);
        }

        Log::debug($request->all());
        try {
            $groupId = Group::insertGetId([
                'user_id' => User::find(Auth::user()->id)->id,
                'subject' => $request->group_subject,
                'alias_url' => $request->group_url,
            ]);

            $bankData = Bank::create([
                'user_id' => User::find(Auth::user()->id)->id,
                'bank_id' => '07',
                'number' => $request->number,
                'name' => $request->name,
            ]);

            $transactions = array(
                array('receipt' => true, 'id' => '1','group_id' => '1','title' => '신입생환영회 도시락 30인분','transaction_date' => '2019-01-19','handling' => '토마토(안산한양대)','balance' => '4049198','transaction_amount' => '120000','deposit_amount' => '0','created_at' => NULL,'updated_at' => '2019-06-29 11:56:16'),
                array('id' => '2','group_id' => '1','title' => '신입생환영회 공책, 스테이플러,테이프','transaction_date' => '2019-01-25','handling' => '주식회사 아성','balance' => '4039198','transaction_amount' => '10000','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '3','group_id' => '1','title' => NULL,'transaction_date' => '2019-02-07','handling' => '카드할인캐쉬백','balance' => '4044424','transaction_amount' => '0','deposit_amount' => '5226','created_at' => NULL,'updated_at' => NULL),
                array('id' => '4','group_id' => '1','title' => '신입생 발송용 학과 소식지 인쇄','transaction_date' => '2019-02-13','handling' => '한양사','balance' => '4014674','transaction_amount' => '29750','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '5','group_id' => '15','title' => '신입생 발송용 A4 우편봉투','transaction_date' => '2019-02-13','handling' => '대학당','balance' => '4001674','transaction_amount' => '13000','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '6','group_id' => '29','title' => '우편 봉인용 풀 5개','transaction_date' => '2019-02-13','handling' => '씨유한양대셔틀콕','balance' => '3996674','transaction_amount' => '5000','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '7','group_id' => '1','title' => NULL,'transaction_date' => '2019-02-14','handling' => '카드할인캐쉬백','balance' => '4000454','transaction_amount' => '0','deposit_amount' => '3780','created_at' => NULL,'updated_at' => NULL),
                array('id' => '8','group_id' => '17','title' => '신입생 안내문 발송 (66명)','transaction_date' => '2019-02-15','handling' => '우정사업본부(한양대)','balance' => '3972734','transaction_amount' => '27720','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '9','group_id' => '29','title' => '신입생 OT 치킨마요 도시락 66개','transaction_date' => '2019-02-19','handling' => '한솥(한대점)','balance' => '3781334','transaction_amount' => '191400','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '10','group_id' => '1','title' => '학생회비 납부','transaction_date' => '2019-02-19','handling' => '이동훈','balance' => '3971334','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '11','group_id' => '17','title' => '신입생 OT 학과 포스터 인쇄','transaction_date' => '2019-02-20','handling' => '한양사','balance' => '3966134','transaction_amount' => '52000','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '12','group_id' => '17','title' => '학생회비 납부','transaction_date' => '2019-02-20','handling' => '심규선','balance' => '4156134','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '13','group_id' => '1','title' => '학생회비 납부','transaction_date' => '2019-02-20','handling' => '이찬영','balance' => '4346134','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '14','group_id' => '1','title' => '학생회 비품 구입 (칼,자)','transaction_date' => '2019-02-20','handling' => '케이-슈퍼마켓','balance' => '4344734','transaction_amount' => '1400','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '15','group_id' => '28','title' => '앙 기모찌~~','transaction_date' => '2019-02-20','handling' => '학생회비 납부부','balance' => '4487234','transaction_amount' => '142500','deposit_amount' => '0','created_at' => NULL,'updated_at' => '2019-06-30 05:32:07'),
                array('id' => '16','group_id' => '27','title' => '학생회비 납부','transaction_date' => '2019-02-21','handling' => '이세현','balance' => '4677234','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '17','group_id' => '1','title' => '학생회비 납부','transaction_date' => '2019-02-21','handling' => '차세진','balance' => '4867234','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '18','group_id' => '1','title' => '학생회비 납부','transaction_date' => '2019-02-21','handling' => '박태환','balance' => '5057234','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '19','group_id' => '1','title' => '학생회비 납부','transaction_date' => '2019-02-21','handling' => '이예은','balance' => '5057234','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '20','group_id' => '23','title' => '강은서','transaction_date' => '2019-02-21','handling' => '학생회비 납부','balance' => '5389734','transaction_amount' => '0','deposit_amount' => '190000','created_at' => NULL,'updated_at' => NULL),
                array('id' => '21','group_id' => '1','title' => '신입생OT퀴즈 상품','transaction_date' => '2019-02-21','handling' => '씨유한양대셔틀','balance' => '5372734','transaction_amount' => '17000','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL),
                array('id' => '22','group_id' => '1','title' => '신입생 OT 도시락 추가 구매','transaction_date' => '2019-02-21','handling' => '한솥(한대점)','balance' => '5361134','transaction_amount' => '11600','deposit_amount' => '0','created_at' => NULL,'updated_at' => NULL)
            );

            foreach ($transactions as $transaction) {
                $trans = Transaction::insertGetId([
                    'group_id' => $groupId,
                    'title' => $transaction['title'],
                    'transaction_date' => $transaction['transaction_date'],
                    'handling' => $transaction['handling'],
                    'balance' => $transaction['balance'],
                    'transaction_amount' => $transaction['transaction_amount'],
                    'deposit_amount' => $transaction['deposit_amount'],
                ]);

                if (! empty($transaction['receipt'])) {
                    Receipt::create([
                        'transaction_id' => $trans,
                        'payment_amount' => $transaction['deposit_amount'],
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'bankDatas' => $bankData,
            ], 201);
        } catch (\Exception $exception) {
            return $exception;
        }
    }

}
