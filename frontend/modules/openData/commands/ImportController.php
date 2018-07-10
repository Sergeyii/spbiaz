<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 12:48
 */

namespace frontend\modules\openData\commands;

use frontend\modules\openData\Importer;
use yii\console\Controller;

class ImportController extends Controller
{
    public function actionIndex()
    {
        $i = new Importer();
        $i->run();
        echo "done\n";
    }
}
