<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 29.12.2016
 * Time: 14:58
 */

namespace frontend\helpers;

use frontend\modules\addresses\models\Address;
use Yii;
use yii\base\ErrorException;

class NotificationAgreed extends NotificationBase
{
    /** @var Address */
    public $addressModel;

    public function init()
    {
        parent::init();
        if (!$this->addressModel) {
            throw new ErrorException('Необходимо задать addressModel');
        }
    }

    public function getMsg()
    {
        $msg = '[b]Получен запрос на согласование[/b][br]';
        $msg .= "Требуется согласовать адресную программу {$this->addressModel->year}[br]";
        return $msg;
    }

    public function getLink()
    {
        return Yii::$app->urlManager->createUrl([
            '/addresses/default/update/',
            'id' => $this->addressModel->id
        ]);
    }
}