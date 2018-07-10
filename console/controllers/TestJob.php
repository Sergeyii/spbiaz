<?php

namespace console\controllers;

use yii\base\BaseObject;
use yii\queue\JobInterface;

class TestJob extends BaseObject implements JobInterface
{
    public $name;

    public function execute($queue)
    {
        file_put_contents(__DIR__.'/1.txt', $this->name);
    }
}