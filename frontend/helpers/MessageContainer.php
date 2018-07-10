<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 09.01.2017
 * Time: 15:27
 */

namespace frontend\helpers;

use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class MessageContainer
{
    /** @var bool is request ok? */
    public $status = true;

    /** @var string message for explain what happened */
    public $msg = '';

    /** @var array additional data */
    public $data = [];

    /** @var int|string http error code  */
    public $errorCode = 500;

    public function merge(self $container)
    {
        $this->status = $container->status;
        $this->msg = $container->msg;
        $this->data = ArrayHelper::merge($this->data, $container->data);
    }

    public function __construct($container = null)
    {
        if ($container instanceof self) {
            $this->merge($container);
        }
    }

    public function setFalse($msg, $code = null)
    {
        $this->status = false;
        if ($code && (is_string($code) || is_integer($code))) {
            $this->errorCode = $code;
        }
        if ($msg instanceof Model) {
            $this->setMsgModel($msg);
        } elseif (gettype($msg) == 'string') {
            $this->msg .= "$msg. ";
        } else {
            throw new ErrorException('Сообщение должно быть типа string или yii\base\Model');
        }
        return $this->status;
    }

    protected function setMsgString($msg)
    {
        $this->msg = $msg;
    }

    protected function setMsgModel(Model $model)
    {
//        $this->msg = Html::errorSummary($model);
        $lines = [];
        foreach ($model->getErrors() as $errors) {
            foreach ($errors as $error) {
                if (!in_array($error, $lines, true)) {
                    $lines[] = $error;
                }
            }
        }
        $this->msg = $model::className().': '.implode('. ', $lines);
    }

    public function setTrue($msg) {
        $this->status = true;
        $this->msg = $msg;
        return $this->status;
    }
}