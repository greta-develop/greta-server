<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HashGeneratorService;

class HashController extends Controller
{
    /**
     * @var HashGeneratorService
     */
    protected $hgs;

    /**
     * HashController constructor.
     * @param HashGeneratorService $hgs
     */
    public function __construct(HashGeneratorService $hgs) {
        $this->hgs = $hgs;
    }

    /**
     * @param array $header
     * @param array $body
     * @return array
     */
    public function index(array $header, array $body) {
//        return $this->createData(json_encode($header, JSON_UNESCAPED_UNICODE), json_encode($body, JSON_UNESCAPED_UNICODE));
        return [
            'hsKey' => $this->generator($this->createData(json_encode($header, JSON_UNESCAPED_UNICODE), json_encode($body, JSON_UNESCAPED_UNICODE))),
            'body' => $this->createData(json_encode($header, JSON_UNESCAPED_UNICODE), json_encode($body, JSON_UNESCAPED_UNICODE)),
        ];
    }

    /**
     * @param string $data
     * @return string
     */
    private function generator(string $data) {
        return $this->hgs->generator($data);
    }

    /**
     * @param $header
     * @param $request
     * @return string
     */
    private function createData($header, $request) {
        $string = <<<AA
{"dataHeader":$header,"dataBody":$request}
AA;

        return $string;
    }
}
