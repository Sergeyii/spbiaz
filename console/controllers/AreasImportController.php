<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 28.06.2017
 * Time: 13:25
 */

namespace console\controllers;


use frontend\modules\directories\AreasImporter;
use yii\console\Controller;

class AreasImportController extends Controller
{

    /**
     * Imports districts from toris to dic_areas
     */
    public function actionIndex()
    {
        $ai = new AreasImporter();
        $ai->send();
    }
}