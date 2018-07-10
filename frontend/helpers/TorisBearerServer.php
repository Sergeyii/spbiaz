<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 17.11.2017
 * Time: 16:32
 */

namespace frontend\helpers;

use common\helpers\BaseCurlApi;
use toris\torisRbac\helpers\Replacer;
use Yii;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

class TorisBearerServer extends BaseCurlApi
{
    /** @var string Тут появится полученный bearer */
    public $bearer;

    public function init()
    {
        $this->baseUrl = Replacer::replaceUrlForConsole(Yii::$app->params['toris-paaa2']).
            'picketlink-oauth-provider-wwwserver/token';
        $this->method = 'post';
        parent::init();
    }

    protected function createRequest(): Request
    {
        $req = parent::createRequest();
        $req->setFormat(Client::FORMAT_JSON);
        return $req;
    }

    public function getRequestData()
    {
        return [
            'client_id' => Yii::$app->params['toris-code'],
            'client_secret' => Yii::$app->params['toris-secret'],
            'grant_type' => 'CLIENT_CREDENTIALS',
            'token_type' => 'Bearer'
        ];
    }

    public function success(Response $response)
    {
        parent::success($response);
        if (!$this->out->data['access_token']) {
            $this->out->setFalse('Не найдено поле access_token');
        } else {
            $this->bearer = $this->out->data['access_token'];
        }
    }
}
