<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 31.01.2018
 * Time: 16:35
 */

namespace console\controllers;

use console\helpers\ApplicationVersion;
use yii\console\Controller;

class AppVersionController extends Controller
{
    public function actionIndex()
    {
        $filename = __DIR__ . '/../../info.json';
        if (file_exists($filename)) {
            $info = json_decode(file_get_contents($filename));
        } else {
            $info = (object) ['build' => '45', 'tag' => '1.0.1'];
        }
        $info->tag = ApplicationVersion::get();
        file_put_contents($filename, json_encode($info));
    }
}
