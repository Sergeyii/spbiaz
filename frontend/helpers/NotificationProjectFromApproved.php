<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 24.05.2018
 * Time: 14:22
 */

namespace frontend\helpers;


class NotificationProjectFromApproved extends NotificationOperator
{
    public function getMsg()
    {
        return '[b]Утвержденную адресную программу скопировали со статусом проект. Ожидается внесение изменений.[/b][br]';
    }
}