<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 27.04.2018
 * Time: 12:19
 */

namespace console\helpers\excelImport;

use frontend\modules\regionals\models\Regional;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class DeleteRegional extends BaseObject implements JobInterface
{

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $cnt = Regional::deleteAll();
        echo "$cnt регионалок удалено";
    }
}
