<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Bank;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HashController;
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

            if (json_decode($response->getBody(), true)['dataHeader']['resultCode']) {
                return 1;
            } else {
                return 0;
            }
        }

        return Bank::create([
            'user_id' => User::find(Auth::user()->id)->id,
            'bank_id' => '07',
            'number' => $request->number,
        ]);
    }

}
