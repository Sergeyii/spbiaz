<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 13:14
 */

namespace frontend\modules\openData;

use frontend\helpers\MessageContainer;
use frontend\modules\openData\curlApi\LoadDataApi;
use frontend\modules\openData\curlApi\LoadStructureApi;
use frontend\modules\openData\models\OpenDataStructure;
use \frontend\modules\openData\models\OpenData;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

class Importer extends BaseObject implements JobInterface
{
    /** @var LoadStructureApi */
    protected $structureApi;

    /** @var LoadDataApi */
    protected $dataApi;

    /** @var MessageContainer */
    protected $structureCont;

    /** @var MessageContainer */
    protected $out;

    public function init()
    {
        $this->structureApi = new LoadStructureApi();
        $this->dataApi = new LoadDataApi();
        $this->out = new MessageContainer();
        $this->out->setTrue('Обновление не требуется');
    }

    /**
     * Стартовый метод
     * @return MessageContainer
     */
    public function run()
    {
        //if ($this->needImport()) {
            OpenDataStructure::deleteAll();
            OpenData::deleteAll();
            $this->import();
            $this->out->setTrue('Обновление прошло успешно');
        //}
        return $this->out;
    }

    /**
     * Is import is needed. Compare structure num_id in db with those who come from api
     * @return bool
     */
    protected function needImport(): bool
    {
        $out = true;
        $this->structureCont = $this->structureApi->send();
        if ($structure = OpenDataStructure::find()->one()) {
            if ($structure->num_id == $this->structureCont->data['num_id']) {
                $out = false;
            }
        }
        return $out;
    }

    protected function import()
    {
        $this->structureApi->send();//Добавил код, без него непонятно как получить данные
        $this->structureApi->save();
        $this->dataApi->send();
        $this->dataApi->saveAll();
    }

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $this->run();
    }
}