<?php
/**
 * Created by PhpStorm.
 * User: nikita.klesov
 * Date: 06.04.2018
 * Time: 11:37
 */

namespace console\controllers;

use console\helpers\excelImport\ImportRegional;
use console\helpers\excelImport\ImportWorksFromExcelHelper;
use Yii;
use yii\console\Controller;

class ImportFromExcelController extends Controller
{
    public function actionIndex()
    {
//        Yii::$app->queue->push(
//            new ImportWorksFromExcelHelper([
//                'filePath' => '3616-1.xlsx',
//            ])
//        );
//        Yii::$app->queue->push(
//            new ImportWorksFromExcelHelper([
//                'filePath' => '3616-2.xlsx',
//            ])
//        );
//        Yii::$app->queue->push(
//            new ImportWorksFromExcelHelper([
//                'filePath' => '3614-1.xlsx',
//            ])
//        );
//        Yii::$app->queue->push(
//            new ImportWorksFromExcelHelper([
//                'filePath' => '3614-2.xlsx',
//            ])
//        );
        Yii::$app->queue->push(
            new ImportRegional()
        );
    }
}
