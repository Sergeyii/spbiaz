<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 11:34
 */

namespace frontend\modules\openData;

use yii\base\BootstrapInterface;
use yii\base\Module;

class OpenData extends Module implements BootstrapInterface
{
    public $controllerNamespace = 'frontend\modules\openData\controllers';

    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'frontend\modules\openData\commands';
        }
    }
}
