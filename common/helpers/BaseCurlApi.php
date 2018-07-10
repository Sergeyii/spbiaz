<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 24.08.2017
 * Time: 11:04
 */

namespace common\helpers;

use common\exceptions\CommonException;
use frontend\helpers\MessageContainer;
//use toris\logger\helpers\Logger;
use yii\base\BaseObject;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;
use Yii;
use yii\httpclient\Exception;
use yii\httpclient\Request;
use yii\httpclient\Response;

class BaseCurlApi extends BaseObject
{
    /** @var Client */
    protected $client;

    /** @var string Replacer::replaceUrl(Yii::$app->params['toris']).Yii::$app->params['organization-search'], */
    public $baseUrl;

    /** @var MessageContainer */
    public $out;

    /** @var string */
    public $method = 'get';

    public function init()
    {
        $this->client = new Client([
            'baseUrl' => $this->baseUrl,
            'transport' => CurlTransport::class,
        ]);
        $this->out = new MessageContainer();
    }

    /**
     * @return Request
     */
    protected function createRequest(): Request
    {

        return $this->client->createRequest()
            ->setMethod($this->method)
            ->setData($this->getRequestData())
            ->setOptions($this->getRequestOptions());
    }

    protected function getRequestData()
    {
        return [];
    }

    protected function getRequestOptions(): array
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_FOLLOWLOCATION  => false,
            CURLOPT_TIMEOUT         => 500,
            CURLOPT_RETURNTRANSFER  => 1,
        ];
        $proxySettings = Yii::$app->params['proxySettings'];
        if (!empty($proxySettings)) {
            $options[CURLOPT_PROXY] = $proxySettings['host'];
            $options[CURLOPT_PROXYUSERPWD] = $proxySettings['logpass'];
        }
        return $options;
    }

    public function send(): MessageContainer
    {
        $request = $this->createRequest();
        try {
            $response = $request->send();
            if ($response->isOk) {
                $this->success($response);
            } else {
                $msg = "Ошибка при запросе к \"$this->baseUrl\"";
                $this->out->data = $response->data;
                $this->error($msg);
            }
        } catch (CommonException $e) {
            $this->error($e->getMessage());
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return $this->out;
    }

    protected function success(Response $response)
    {
        $this->out->setTrue('Данные получены. ');
        $this->out->data = $response->data;
    }

    protected function error($msg)
    {
        $lines = [];
        foreach ($this->out->data as $name => $errors) {
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    if (!in_array($error, $lines, true)) {
                        $lines[] = "$name: $error";
                    }
                }
            } else {
                $lines[] = "$name: $errors";
            }
        }
        $msg .= implode('. ', $lines);
//        Logger::writeLogStatic($msg, Logger::TYPE_ERROR);
        Yii::$app->session->setFlash('error', $msg);
        $this->out->setFalse($msg);
    }
}
