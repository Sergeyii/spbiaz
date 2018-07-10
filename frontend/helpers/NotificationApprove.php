<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 15.02.2018
 * Time: 17:13
 */

namespace frontend\helpers;

class NotificationApprove extends NotificationAgreed
{
    public function getMsg()
    {
        $msg = '[b]Получен запрос на утверждение[/b][br]';
        $msg .= "Требуется утвердить адресную программу {$this->addressModel->year}[br]";
        return $msg;
    }
}
