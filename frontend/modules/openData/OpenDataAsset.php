<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 09.06.2018
 * Time: 10:56
 */

namespace frontend\modules\openData;

use yii\web\AssetBundle;

class OpenDataAsset extends AssetBundle
{
    public $sourcePath = '@frontend/modules/openData/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/loadOpenData.js',
    ];
}