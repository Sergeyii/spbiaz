<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 12.04.2018
 * Time: 17:26
 */

namespace common\helpers;

use frontend\helpers\MessageContainer;
use toris\torisRbac\helpers\Replacer;
use Yii;
use yii\helpers\ArrayHelper;
use yii\httpclient\Response;

class EasAddressApi extends BaseCurlApi
{
    /** @var string */
    public $inputAddress;

    /** @var string */
    public $pAddress;

    /** @var string */
    public $easCode;

    /**
     * Включить кэширование запросов
     * @var bool
     */
    public $enableCache = false;

    /** @var array eas_code => address */
    protected static $cacheEasAddresses = [];

    public function init()
    {
        $this->baseUrl = Replacer::replaceUrlForConsole(Yii::$app->params['toris']).'address-web/rest/building/search';
        $this->inputAddress = str_replace(['лит.', 'ЛИТ.'], 'литера ', $this->inputAddress);
        $this->inputAddress = str_replace(['ул.', 'УЛ.'], 'улица ', $this->inputAddress);
        $this->inputAddress = str_replace(['д.', 'Д.'], 'дом ', $this->inputAddress);
        $this->inputAddress = str_replace(['р.', 'Р.'], '', $this->inputAddress);
        $this->inputAddress = str_replace(['В.О.', ''], '', $this->inputAddress);
        $this->inputAddress = str_replace([' ,'], ',', $this->inputAddress);
        parent::init();
    }

    public function getRequestData()
    {
        return [
            'pAddress' => $this->inputAddress,
        ];
    }

    public function send(): MessageContainer
    {
        if (!isset(self::$cacheEasAddresses[$this->inputAddress])) {
            $this->out = parent::send();
        } else {
            $this->out->setTrue('Из кэша');
        }
        return $this->out;
    }

    public function success(Response $response)
    {
        parent::success($response);
        if ($this->enableCache) {
            $this->selectCurrentEas();
            self::$cacheEasAddresses[$this->inputAddress] = $this->easCode;
        }
    }

    /**
     * Взять первый из найденных адресов и использовать его easCode
     */
    public function selectCurrentEas()
    {
        $easCodes = ArrayHelper::getColumn($this->out->data, 'buildingEasId');
        $easCodes = array_filter($easCodes);
        if (!empty($easCodes)) {
            $this->easCode = (string) current($easCodes);
            $this->selectCurrentAddress();
        }
    }

    /**
     * Взять первый из найденных адресов и использовать его в качестве pAddress
     */
    protected function selectCurrentAddress()
    {
        $addresses = ArrayHelper::getColumn($this->out->data, 'pAddress');
        $addresses = array_filter($addresses);
        if (!empty($addresses)) {
            $this->pAddress = (string) current($addresses);
        }
    }
}
