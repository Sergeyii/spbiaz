<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 05.09.2017
 * Time: 18:54
 */

namespace console\controllers;


use frontend\helpers\WorkFieldMoveDynamicToStatic;
use yii\console\Controller;

class MoveWorkFieldsDynamicToStaticController extends Controller
{
    public function actionIndex()
    {
        $mover = new WorkFieldMoveDynamicToStatic();
        echo json_encode($mover->move());
    }
}