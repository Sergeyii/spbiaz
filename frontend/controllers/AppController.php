<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 09.10.2017
 * Time: 9:11
 */

namespace frontend\controllers;

use common\components\EventManager;
//use toris\base\components\Controller;
use yii\web\Controller;

class AppController extends Controller
{
    public function beforeAction($action)
    {
        EventManager::init();
        return parent::beforeAction($action);
    }
}
