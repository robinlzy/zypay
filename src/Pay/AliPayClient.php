<?php

namespace Ziyancs\Zypay\Pay;
use Ziyancs\Zypay\Pay\Alipay\V2\Aop\AlipayConfig;
use Ziyancs\Zypay\Pay\Alipay\V2\Aop\AopClient;
use Ziyancs\Zypay\Pay\Alipay\V2\Aop\Request\AlipayTradeWapPayRequest;

/**
 * 支付宝支付
 */
class AliPayClient
{
    /**
     * @param $params
     * @return void
     */
    public function checkSign($setting,$params){
        //签名验证
        $aop = new AopClient ();
        $aop->alipayrsaPublicKey = $setting['alipay_public_cert_path'];
        return $aop->rsaCheckV1($params, NULL, "RSA2");
    }

    public function getClient($config)
    {
        $appId = $config['app_id'];
        $privateKey = $config['app_secret_cert'];
        $alipayPublicKey = $config['alipay_public_cert_path'];
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $appId;
        $aop->rsaPrivateKey = $privateKey;
        $aop->alipayrsaPublicKey = $alipayPublicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        return $aop;
    }

    /**
     * 阿里H5支付
     * @return void
     */
    public function alipayH5($settings, $object)
    {
        $object->product_code = 'QUICK_WAP_WAY';
        $appId = (String)trim($settings['app_id']);
        $privateKey = $settings['app_secret_cert'];
        $alipayPublicKey = $settings['alipay_public_cert_path'];
        $alipayConfig = new AlipayConfig();
        $alipayConfig->setServerUrl("https://openapi.alipay.com/gateway.do");
        $alipayConfig->setAppId($appId);
        $alipayConfig->setPrivateKey($privateKey);
        $alipayConfig->setFormat("json");
        $alipayConfig->setAlipayPublicKey($alipayPublicKey);
        $alipayConfig->setCharset("UTF-8");
        $alipayConfig->setSignType("RSA2");
        $alipayClient = new AopClient($alipayConfig);
        $request = new AlipayTradeWapPayRequest();
        $json = json_encode($object);
        $request->setBizContent($json);
        $request->setReturnUrl($settings['return_url']);
        $request->setNotifyUrl($settings['notify_url']);
        $pageRedirectionData = $alipayClient->pageExecute($request,"GET");
        return ['type'=>'url','url'=>$pageRedirectionData];
    }

    /**
     * 阿里Web支付
     * @return void
     */
    public function alipayWeb($settings, $object)
    {
        $object->product_code = 'FAST_INSTANT_TRADE_PAY';
        $appId = (String)trim($settings['app_id']);
        $privateKey = $settings['app_secret_cert'];
        $alipayPublicKey = $settings['alipay_public_cert_path'];
        $alipayConfig = new AlipayConfig();
        $alipayConfig->setServerUrl("https://openapi.alipay.com/gateway.do");
        $alipayConfig->setAppId($appId);
        $alipayConfig->setPrivateKey($privateKey);
        $alipayConfig->setFormat("json");
        $alipayConfig->setAlipayPublicKey($alipayPublicKey);
        $alipayConfig->setCharset("UTF-8");
        $alipayConfig->setSignType("RSA2");
        $alipayClient = new AopClient($alipayConfig);
        $request = new AlipayTradeWapPayRequest();
        $json = json_encode($object);
        $request->setBizContent($json);
        $request->setReturnUrl($settings['return_url']);
        $request->setNotifyUrl($settings['notify_url']);
        $pageRedirectionData = $alipayClient->pageExecute($request,"GET");
        return ['type'=>'url','url'=>$pageRedirectionData];
    }

    /**
     * 阿里APP支付
     * @return void
     */
    public function alipayApp($settings, $object)
    {
        $object->product_code = 'QUICK_MSECURITY_PAY';
        $appId = (String)trim($settings['app_id']);
        $privateKey = $settings['app_secret_cert'];
        $alipayPublicKey = $settings['alipay_public_cert_path'];
        $alipayConfig = new AlipayConfig();
        $alipayConfig->setServerUrl("https://openapi.alipay.com/gateway.do");
        $alipayConfig->setAppId($appId);
        $alipayConfig->setPrivateKey($privateKey);
        $alipayConfig->setFormat("json");
        $alipayConfig->setAlipayPublicKey($alipayPublicKey);
        $alipayConfig->setCharset("UTF-8");
        $alipayConfig->setSignType("RSA2");
        $alipayClient = new AopClient($alipayConfig);
        $request = new AlipayTradeWapPayRequest();
        $json = json_encode($object);
        $request->setBizContent($json);
        $request->setReturnUrl($settings['return_url']);
        $request->setNotifyUrl($settings['notify_url']);
        $pageRedirectionData = $alipayClient->sdkExecute($request);
        return $pageRedirectionData;
    }


}