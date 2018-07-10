<?php

namespace console\controllers;

use yii\console\Controller;
use Yii;

class TestJobController extends Controller
{
    public function actionTest()
    {
        Yii::$app->queue->push(new TestJob([
            'name' => 'vasya',
        ]));
    }
}