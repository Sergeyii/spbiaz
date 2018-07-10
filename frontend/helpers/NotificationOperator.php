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

class NotificationOperator extends NotificationBase
{
    /** @var Address */
    public $addressModel;

    public function getMsg()
    {
        $msg = "[b]Адресная программа изменила статус на: ".
            (new AgreedStatus())->getStatus($this->addressModel->address_status)."[/b][br]";
        return $msg;
    }

    protected function getLink()
    {
        return Yii::$app->urlManager->createAbsoluteUrl([
            '/addresses/default/update/',
            'id' => $this->addressModel->id
        ]);
    }
}