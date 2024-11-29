<?php

namespace Ziyanco\Library\Pay;
/**
 * 合力宝
 */
class HlbPayClient
{
    protected $mchId;
    protected $publicKey;

    public function webPay($setting, $order, $type)
    {
        $this->mchId = $setting['mch_id'];
        $this->publicKey = $setting['public_key'];

        $message_body = [
            'body' => "U9CS饰品",
            'amount' => (string)$order->fact_money,
            'detail' => "U9CS饰品",
            'trade_type' => $type,
            'mchid' => $this->mchId,
            "mch_orderid" => $order->order_sn,
            'show_url' => $setting['return_url'],
            'notify_url' => $setting['notify_url'],
            'client_ip' => '127.0.0.1'
        ];
        $message_body['sign'] = $this->sign($message_body, $this->publicKey);
        print_r($message_body);
        $header = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Accept' => 'application/json'
        ];
        //鐢熸垚message
        $client = new Client();
        $url = 'http://api.timezsoft.com/pay/createorder';
        $promise = $client->requestAsync('POST', $url, [
            'body' => json_encode($message_body),
            'headers' => $header
        ]);
        $response = $promise->wait();
        $response = $response->getBody()->getContents();
        $result = json_decode($response, true);
        print_r($result);
        if (!empty($result['code']) && $result['code'] != "00") {
            throw new \ErrorException($result['msg']);
        }
        if (!empty($result['code']) && $result['code'] == "00") {
            if (in_array($type, ['alipay.qrcode', 'weixin.qrcode'])) {
                return ['type' => 'qrcode', 'data' => $result['pay_info']];
            }
            return ['type' => 'url', 'data' => $result['pay_info']];
        }
        return "";
    }


    public function sign($data, $mchKey)
    {
        ksort($data);
        $str = '';
        foreach ($data as $key => $value) {
            $str = $str . $key . '=' . $value . '&';
        }
        $str = $str . 'key=' . $mchKey;
        return md5($str);
    }

    public function signCheck($setting, $data)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        $checkSign = $this->sign($data, $setting['public_key']);
        if ($sign != $checkSign) {
            return false;
        }
        return true;
    }
}