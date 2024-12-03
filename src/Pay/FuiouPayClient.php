<?php

namespace Ziyancs\Library\Pay;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 富友--https://www.fuioupay.com/
 * http://180.168.100.158:13318/fuiouWposApipay/sheng-chan-quan-xian.html
 */
class FuiouPayClient
{
    protected $rsa_public_key;
    protected $rsa_private_key;

    public function webPay($setting, $order, $type)
    {
        $this->rsa_public_key = $setting['public_key'];
        $this->rsa_private_key = $setting['private_key'];

        $message_body = [
            'mchnt_cd' => $setting['mch_id'],
            'order_date' => date('Ymd'),
            'order_id' => $order->order_sn,
            'order_amt' => $order->fact_money,
            'order_pay_type' => $type,
            'back_notify_url' => $setting['notify_url'],
            'goods_name' => 'U9CS',
            'goods_detail' => 'U9CS',
            'ver' => '1.0.0'
        ];
        print_r($message_body);
        //鐢熸垚message
        $message = $this->publicEncryptRsa(json_encode($message_body));
        $client = new Client(['verify' => false]);
        $url = 'https://hlwnets.fuioupay.com/aggpos/order.fuiou';        //鎺ュ彛鍦板潃
        $res = $client->request('POST', $url, [
            'json' => [
                'mchnt_cd' => $message_body['mchnt_cd'],                //杩欓噷鐨刴chnt_cd瑕佸彇鎶ユ枃浣撻噷闈㈢殑mchnt_cd
                'message' => $message
            ]
        ]);
        $result = $res->getBody()->getContents();
        $decrypted = "";
        if ($result) {
            $result = json_decode($result, true);
            if ($result['resp_code'] == '0000') {

                $decrypted = $this->privateDecryptRsa($result['message'], $this->rsa_private_key);
                if ($decrypted) {
                    $decrypted = json_decode($decrypted, true);
                }
            } else {
                throw new \ErrorException($result['resp_desc']);
            }
        }
        return ['type' => 'qrcode', 'data' => $decrypted['order_info']];
    }


    /**
     * 解密
     * @param $setting
     * @param $message
     * @return void
     */
    public function decryptionRsa($setting, $message)
    {
        $this->rsa_private_key = $setting['private_key'];
        $decrypted = $this->privateDecryptRsa($message, $this->rsa_private_key);
        if ($decrypted) {
            $decrypted = json_decode($decrypted, true);
        }
        return $decrypted;
    }


    /**
     * 回调通知
     * @return void
     */

    public function notify($params)
    {
        $setting=[]; //配置项
        $notifyData = $this->decryptionRsa($setting, $params['message']);
        $outTradeNo = !empty($notifyData['order_id']) ? $notifyData['order_id'] : ''; //外部订单号
        echo "=====2=====>>" . PHP_EOL;
        if ($notifyData['order_st'] != 1) {  //通知类型是失败的
            writeLog("富友回调数据--order_st!=1交易失败:", [], $params);
            return "fail";
        }

    }

    private function publicEncryptRsa($plainData = '')
    {
        if (!is_string($plainData)) {
            return null;
        }
        $encrypted = '';
        $partLen = $this->getPublicKenLen() / 8 - 11;
        $plainData = str_split($plainData, $partLen);
        $publicPEMKey = $this->getPublicKey();
        foreach ($plainData as $chunk) {
            $partialEncrypted = '';
            $encryptionOk = openssl_public_encrypt($chunk, $partialEncrypted, $publicPEMKey, OPENSSL_PKCS1_PADDING);
            if ($encryptionOk === false) {
                return false;
            }
            $encrypted .= $partialEncrypted;
        }
        return base64_encode($encrypted);
    }

    private function getPublicKenLen()
    {
        $pub_id = openssl_get_publickey($this->getPublicKey());
        return openssl_pkey_get_details($pub_id)['bits'];
    }

    private function getPublicKey()
    {
        $public_key = $this->rsa_public_key;
        $pubic_pem = chunk_split($public_key, 64, "\n");
        $pubic_pem = "-----BEGIN PUBLIC KEY-----\n" . $pubic_pem . "-----END PUBLIC KEY-----\n";
        return $pubic_pem;
    }

    private function privateDecryptRsa($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        $decrypted = '';

        $partLen = $this->getPrivateKenLen() / 8;
        $data = str_split(base64_decode($data), $partLen);

        $privatePEMKey = $this->getPrivateKey();

        foreach ($data as $chunk) {
            $partial = '';
            $decryptionOK = openssl_private_decrypt($chunk, $partial, $privatePEMKey, OPENSSL_PKCS1_PADDING);
            if ($decryptionOK === false) {
                return false;
            }
            $decrypted .= $partial;
        }
        return $decrypted;
    }

    private function getPrivateKenLen()
    {
        $pub_id = openssl_get_privatekey($this->getPrivateKey());
        return openssl_pkey_get_details($pub_id)['bits'];
    }

    private function getPrivateKey()
    {

        $private_key = $this->rsa_private_key;
        $private_pem = chunk_split($private_key, 64, "\n");
        $private_pem = "-----BEGIN PRIVATE KEY-----\n" . $private_pem . "-----END PRIVATE KEY-----\n";
        return $private_pem;
    }
}