<?php

namespace Ziyanco\Library\Pay;
use GuzzleHttp\Client;
class IosClient
{
    /**
     * IOS支付回调
     * @param $params
     * @return array|mixed
     */
    public function iosSignCheck($params)
    {
        $receipt = $params['receipt'];
        $jsonItem = json_encode(['receipt-data' => $receipt]);
        $url = 'https://buy.itunes.apple.com/verifyReceipt';      //正式
        $client = new Client();
        $promise = $client->requestAsync('POST', $url, ['body' => $jsonItem, 'headers' => ['Content-Type' => 'application/json; charset=UTF-8']]);
        $response = $promise->wait();
        $response = $response->getBody()->getContents();
        print_r($response);
        $result = json_decode($response, true);
        if ($result['status'] == '21007') {
            //验证失败 返回app错误状态
            $url = 'https://sandbox.itunes.apple.com/verifyReceipt';  //测试

            $promise = $client->requestAsync('POST', $url, ['body' => $jsonItem, 'headers' => ['Content-Type' => 'application/json; charset=UTF-8']]);
            $response = $promise->wait();
            $response = $response->getBody()->getContents();
            $result = json_decode($response, true);

        }
        //如果检测到 等于 0 就是支付成功，其他的错误码去获取对应错误信息
        if ($result['status'] !== 0) {
            return [];
        }
        return $result;
    }
}