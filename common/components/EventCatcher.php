<?php
namespace common\components;

use toris\base\components\ActiveRecord;
use yii\base\Event;
use yii\db\AfterSaveEvent;

/**
 * Class EventCatcher
 *
 * @package common\components
 */
abstract class EventCatcher
{
    /**
     * @param Event $event
     */
    abstract public static function eventCatcher(Event $event);

    /**
     * @param Event $event
     *
     * @return bool
     */
    public static function raiseEvent(Event $event)//: ?bool
    {
        if (self::isNeedInvokeEvent($event)) {
            return static::eventCatcher($event);
        }

        return true;
    }

    /**
     * @param Event|AfterSaveEvent $event
     *
     * @return bool
     */
    private static function isNeedInvokeEvent(Event $event): bool
    {
        if (!($event instanceof AfterSaveEvent) ||
            $event->sender === null ||
            (!($event->sender instanceof ActiveRecord)) ||
            !method_exists($event->sender, 'events')) {
            return true;
        }

        /** @var ActiveRecord $model */
        $model = $event->sender;

        $handler   = static::class;
        $events    = $model->event();
        $eventName = $event->name;

        if (!isset($events[$eventName])) {
            return true;
        }
        $eventHandlers = $events[$eventName];

        if (!isset($eventHandlers[$handler])) {
            return true;
        }

        $eventHandler = $eventHandlers[$handler];

        $changedAttribute = array_keys($event->changedAttributes);
        if (empty(array_diff($changedAttribute, $eventHandler['except'] ?? []))) {
            return false;
        }

        if (!array_intersect($eventHandler['on'] ?? [], $changedAttribute)) {
            return false;
        }

        return true;
    }
}
