<?php
/**
 * Created by PhpStorm.
 * User: a.lukyanovich
 * Date: 21.03.2018
 * Time: 11:01
 */

namespace common\components;

use linslin\yii2\curl\Curl;
use Yii;

class Sender
{
    /**
     * @var Curl
     */
    protected $curl;


    public function __construct()
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_TIMEOUT         => 500,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $proxySettings = Yii::$app->params['proxySettings'];

        if (!empty($proxySettings)) {
            $options[CURLOPT_PROXY] = $proxySettings['host'];
            $options[CURLOPT_PROXYUSERPWD] = $proxySettings['logpass'];
        }

        $this->curl = new Curl();
        $this->curl->setOptions($options);
    }

    public function send($url)
    {
        $find_from_api = $this->curl->get($url);

        if (empty($find_from_api)) {
            $find_from_api = json_encode(['error' => 'Получен пустой ответ']);
        }

        return $find_from_api;
    }
}
