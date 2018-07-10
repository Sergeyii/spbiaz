<?php
namespace frontend\helpers;

class HelperStatuses
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;
    const STATUS_ARCHIVE = 2;
    const STATUS_FINISHED = 3;
    const STATUS_APPROVED = 4;

    public static function arrayStatusesNames()
    {
        return [
            self::STATUS_DELETED    => 'Удален',
            self::STATUS_ACTIVE     => 'Опубликован',
            self::STATUS_ARCHIVE    => 'В архиве',
            self::STATUS_FINISHED   => 'Выполнен',
            self::STATUS_APPROVED   => 'Подтвержден'
        ];
    }

    public static function getStatusName($status)
    {
        $array = self::arrayStatusesNames();
        if (!empty($array[$status])) {
            return $array[$status];
        } else {
            return $array[self::STATUS_ACTIVE];
        }
    }

}
