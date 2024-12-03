<?php

namespace Ziyancs\Library\Pay;
/**
 * 汇付
 */
class HeepayClient
{
    public function webPay($setting, $order)
    {
        $mhtOrderNo = $order->order_sn;
        $mchId = $setting['mch_id'];
        $childMchId = $setting['child_mch_id'];
        $mchKey = $setting['key'];
        $productName = "U9CS";
        $postData = [];
        $postData['version'] = 1;
        $postData['pay_type'] = 64;
        $postData['agent_id'] = $mchId;
        $postData['ref_agent_id'] = $childMchId;
        $postData['agent_bill_id'] = $mhtOrderNo;
        $postData['agent_bill_time'] = date('Ymdhis');
        $postData['pay_amt'] = number_format($order->fact_money / 100, 2, '.', '');
        $postData['notify_url'] = $setting['notify_url'];
        $postData['return_url'] = $setting['return_url'];
        $postData['user_ip'] = !empty($order->ip) ? str_replace('.', '_', $order->ip) : '127_0_0_1';
        $postData['goods_name'] = $productName;
        $postData['remark'] = $productName;
        $postData['sign_type'] = 'MD5';
        $postData['meta_option'] = $setting['meta_option'];
        $postData['sign'] = $this->sign($postData, $mchKey);
        $formString = $this->makeForm("https://pay.heepay.com/Payment/Index.aspx", $postData);
        return ['type' => 'form', 'data' => $formString];
    }

    public function makeForm($url, $requestData)
    {
        $formString = "<form id=\"submitForm\" action=\"" . $url . "\" method=\"post\">";
        foreach ($requestData as $itemKey => $itemVal) {
            $formString .= "<input type=\"hidden\" name=\"" . $itemKey . "\" value=\"" . $itemVal . "\">";
        }
        $formString .= "</form>";

        $formString .= "<script>";
        $formString .= "document.getElementById('submitForm').submit()";
        $formString .= "</script>";
        return $formString;
    }

    public function sign($data, $key)
    {
        $str = '';
        $arr = ['version', 'agent_id', 'agent_bill_id', 'agent_bill_time', 'pay_type', 'pay_amt', 'notify_url', 'return_url', 'user_ip'];
        foreach ($arr as $item) {
            $str = $str . $item . '=' . $data[$item] . '&';
        }
        $str = $str . 'key=' . $key;
        $str = $str . '&ref_agent_id=' . $data['ref_agent_id'];
        echo PHP_EOL . "md5key====>>>" . $str . PHP_EOL;
        return md5($str);
    }


    public function callSign($data, $key)
    {
        $str = '';
        $arr = ['result', 'agent_id', 'jnet_bill_no', 'agent_bill_id', 'pay_type', 'pay_amt', 'remark'];
        foreach ($arr as $item) {
            $str = $str . $item . '=' . $data[$item] . '&';
        }
        $str = $str . 'key=' . $key;
        echo PHP_EOL . "back---md5key====>>>" . $str . PHP_EOL;
        return md5($str);

    }

    /**
     * 回调签名验证
     * @param $setting
     * @param $params
     * @return void
     */
    public function signCheck($setting, $params)
    {
        $sign = $this->callSign($params, $setting['key']);
        if ($params['sign'] != $sign) {
            return false;
        }
        return true;
    }
}