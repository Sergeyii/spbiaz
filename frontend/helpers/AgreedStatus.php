<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 27.12.2016
 * Time: 15:42
 */

namespace frontend\helpers;

use frontend\modules\addresses\models\AddressStatuses;

class AgreedStatus
{
    const AP_STATUS_INITIAL = 0;
    const AP_STATUS_PROJECT = 1;
    const AP_STATUS_FOR_AGREED = 2;
    const AP_STATUS_AGREED_NOT = 3;
    const AP_STATUS_AGREED = 4;
    const AP_STATUS_FOR_APPROVE = 5;
    const AP_STATUS_APPROVE_NOT = 6;
    const AP_STATUS_APPROVE_YES = 7;
    const AP_STATUS_ARCHIVE = 8;
    const AP_STATUS_DELETED = 9;
    const AP_STATUSES = [
        self::AP_STATUS_PROJECT => 'Проект',
        self::AP_STATUS_FOR_AGREED => 'Направлено на согласование',
        self::AP_STATUS_AGREED_NOT => 'Не согласовано',
        self::AP_STATUS_AGREED => 'Согласовано',
        self::AP_STATUS_FOR_APPROVE => 'На утверждении',
        self::AP_STATUS_APPROVE_NOT => 'На утверждении: Отклонено',
        self::AP_STATUS_APPROVE_YES => 'На утверждении: Утверждено',
        self::AP_STATUS_ARCHIVE => 'Архив',
        self::AP_STATUS_DELETED => 'Удален'
    ];
    const AP_AGREED_STATUSES = [
        self::AP_STATUS_AGREED_NOT => 'Не согласовано',
        self::AP_STATUS_AGREED => 'Согласовано'
    ];
    const AP_APPROVE_STATUSES = [
        self::AP_STATUS_APPROVE_NOT => 'На утверждении: Отклонено',
        self::AP_STATUS_APPROVE_YES => 'На утверждении: Утверждено',
    ];
    const AP_DECLINE_STATUSES = [
        self::AP_STATUS_AGREED_NOT,
        self::AP_STATUS_APPROVE_NOT
    ];

    const AP_SUCCESS_STATUSES = [
        self::AP_STATUS_AGREED,
        self::AP_STATUS_APPROVE_YES,
    ];

    const AP_STATUSES_FOR_CREATE_WORK = [
        self::AP_STATUS_INITIAL,
        self::AP_STATUS_PROJECT,
        self::AP_STATUS_AGREED_NOT,
        self::AP_STATUS_APPROVE_NOT,
    ];

    public static function getStatus($statusId)
    {
        $status = 'Неизвестный статус';
        if (array_key_exists($statusId, self::AP_STATUSES)) {
            $status = self::AP_STATUSES[$statusId];
        } else {
            $status .= " ($statusId)";
        }
        return $status;
    }

    public static function canCreateWork($status)
    {
        return in_array($status, self::AP_STATUSES_FOR_CREATE_WORK);
    }

    /**
     * Находится ли статус в начальной фазе - до утверждения
     * @param AddressStatuses|null $status
     * @return bool
     */
    public function isStatusInBeforeApproveFase($status): bool
    {
        return !empty($status)
            && in_array($status->status, [
                self::AP_STATUS_INITIAL,
                self::AP_STATUS_PROJECT,
                self::AP_STATUS_FOR_AGREED,
            ]);
    }

    /**
     * Находится ли статус в начальной фазе - до утверждения
     * @param AddressStatuses|null $status
     * @return bool
     */
    public function isStatusInBeforeFinalFase($status): bool
    {
        return $status
            && ($this->isStatusInBeforeApproveFase($status) || $status->status == self::AP_STATUS_FOR_APPROVE)
            && !in_array($status->status, [
                self::AP_STATUS_APPROVE_YES,
                self::AP_STATUS_APPROVE_NOT,
            ]);
    }
}