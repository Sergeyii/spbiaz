<?php
/**
 * Created by PhpStorm.
 * User: y0rker
 * Date: 18.04.2017
 * Time: 14:52
 */

namespace common\helpers;

use linslin\yii2\curl\Curl;
use toris\torisRbac\models\User;
use Yii;

class Users extends User
{
    private static function getAssesTokenFromPaaaByAIS ($ais)
    {
        $codeSys = Yii::$app->params['toris-code'];
        $post = [
            'client_id'     => $codeSys,
            'client_secret' => $codeSys,
            'grant_type'    => 'AUTHORIZATION_CODE',
            'token_type'    => 'Bearer',
            'code'          => $ais
        ];
        $post = json_encode($post);
        $options = [
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_TIMEOUT         => 500,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $post,
            CURLOPT_HTTPHEADER      => [
                'Content-Type: application/json; charset=UTF-8',
                'Content-Length: ' . strlen($post)
            ]
        ];
        $proxySettings = Yii::$app->params['proxySettings'];
        if (!empty($proxySettings)) {
            $options[CURLOPT_PROXY] = $proxySettings['host'];
            $options[CURLOPT_PROXYUSERPWD] = $proxySettings['logpass'];
        }
        $curl = new Curl();
        $curl->setOptions($options);
        $response = $curl->post('http://paaa2.test.toris.vpn/picketlink-oauth-provider-wwwserver/token');
        print_r($response);
    }

    public static function getUserFromPaaaByAIS($ais)
    {
        //self::getAssesTokenFromPaaaByAIS('1d1b1684-e7d5-4e02-bea3-9dc128380d60');
        //exit();
        $options = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 500,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json; charset=UTF-8',
                'Authorization: Bearer 30dc7ce8dabd63ec1fdf315f974d5a69'
            ]
        ];
        $proxySettings = Yii::$app->params['proxySettings'];
        if (!empty($proxySettings)) {
            $options[CURLOPT_PROXY] = $proxySettings['host'];
            $options[CURLOPT_PROXYUSERPWD] = $proxySettings['logpass'];
        }
        $curl = new Curl();
        $curl->setOptions($options);
        $response = $curl->post('http://paaa2.test.toris.vpn/picketlink-oauth-provider-wwwserver/token/UserInfo');
        print_r($response);
    }

    public function isAdmin()
    {

    }
}
