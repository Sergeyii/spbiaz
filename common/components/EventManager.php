<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 06.10.2017
 * Time: 13:00
 */

namespace common\components;

use frontend\modules\contracts\events\AddressToArchive;
use frontend\modules\requests\fromGorod\eventHandlers\ChangeStatusHandler;
use frontend\modules\requests\fromGorod\eventHandlers\GetNewRequestHanler;
use frontend\modules\works\GatiXml\GatiStatusHandler;
//use toris\base\components\ActiveRecord;
use yii\db\ActiveRecord;

class EventManager
{
    const EVENT_REQUEST_INSERT = ActiveRecord::EVENT_AFTER_INSERT . '_Request';
    const EVENT_REQUEST_UPDATE = ActiveRecord::EVENT_AFTER_UPDATE . '_Request';
    const EVENT_REQUEST_DELETE = ActiveRecord::EVENT_AFTER_DELETE . '_Request';
    const EVENT_CONTRACT_UPDATE = ActiveRecord::EVENT_AFTER_UPDATE . '_Contract';
    const EVENT_WORK_GATI_INSERT = ActiveRecord::EVENT_AFTER_INSERT . '_WorkGati';
    const EVENT_ADDRESS_UPDATE = ActiveRecord::EVENT_AFTER_UPDATE . '_Address';
    const EVENTS = [
        self::EVENT_REQUEST_INSERT => [
            [GetNewRequestHanler::class, 'raiseEvent']
        ],
        self::EVENT_REQUEST_UPDATE => [
            [ChangeStatusHandler::class, 'raiseEvent']
        ],
        self::EVENT_REQUEST_DELETE => [
            [ChangeStatusHandler::class, 'raiseEvent']
        ],
        self::EVENT_CONTRACT_UPDATE => [
            [AddressToArchive::class, 'raiseEvent']
        ],
        self::EVENT_WORK_GATI_INSERT => [
            [GatiStatusHandler::class, 'raiseEvent']
        ],
        self::EVENT_ADDRESS_UPDATE => [
            [ChangeStatusHandler::class, 'raiseEvent']
        ]
    ];

    public static function init()
    {
        foreach (self::EVENTS as $eventName => $handlers) {
            /** @var array $handlers */
            foreach ($handlers as $handler) {
                \Yii::$app->on($eventName, $handler);
            }
        }
    }
}
