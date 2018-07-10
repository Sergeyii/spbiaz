<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.12.2016
 * Time: 14:58
 */

namespace frontend\helpers;

use toris\torisRbac\helpers\Replacer;
use toris\torisRbac\models\User;
use Yii;
use yii\base\BaseObject;
use yii\console\Application;

abstract class NotificationBase extends BaseObject
{
    protected $domain = 'http://beta.test.toris.vpn';
    protected $systemId = 'urn:eis:toris:ap';

    public function init()
    {
        $this->domain = 'http://'.Yii::$app->params['toris-domain'];
        $this->systemId = Yii::$app->params['toris-code'];
        $baseUrl = Replacer::replaceUrlForConsole(Yii::$app->params['base-url']);
        if (Yii::$app instanceof Application) {
            Yii::$app->urlManager->setBaseUrl($baseUrl);
        }
    }

    public function notify($userId)
    {
        $out = new MessageContainer();
        $out->merge($this->request($userId));
        return $out;
    }

    abstract protected function getLink();

    abstract protected function getMsg();

    protected function request($userId)
    {
        $out = new MessageContainer();
        if ($user = User::findOne($userId)) {
            if ($user->esov_uid) {
                $s_url = $this->domain . "/api/notifier/";

                $header = array();
                $header[] = 'Content-type: application/json';
                $header[] = 'SystemID: ' . $this->systemId;

                $requestData = array(
                    "message" => $this->getMsg(),
                    "link" => $this->getLink(),
                    "userESOVid" => $user->esov_uid
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $s_url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestData));

                $data = curl_exec($curl);

                if (curl_errno($curl)) {
                    $out->setFalse(curl_error($curl));
                } else {
                    $out->data[$userId] = $data;
                    $out->setTrue("$userId - запрос на нотификацию отправлен");
                }

                curl_close($curl);
            } else {
                $out->setFalse("У пользователя {$user->username} отсутствует параметр 'esov_uid'. Невозможно отправить сообщение.");
            }
        } else {
            $out->setFalse("Не найден пользователь: $userId");
        }

        return $out;
    }

}