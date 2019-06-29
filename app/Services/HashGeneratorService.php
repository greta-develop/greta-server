<?php

namespace App\Services;


class HashGeneratorService
{
    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $apiKey;

    /**
     * HashGeneratorService constructor.
     */
    public function __construct() {
        $this->apiKey = config('key.kookmin.key');
    }

    /**
     * @param string $data
     * @return string
     */
    public function generator(string $data) {
        return base64_encode(hash_hmac('sha256', $data, $this->apiKey, true));
    }
}
