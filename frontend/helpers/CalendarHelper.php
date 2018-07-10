<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 27.01.2017
 * Time: 16:24
 */

namespace frontend\helpers;


class CalendarHelper
{

    public function checkCalendar ($workDays) {
        // делаем запрос на сервис пересчета рабочих дней
        $calendarDays = '5';
        return $calendarDays; // может быть работать с датами '20.12.2017'?
    }
}