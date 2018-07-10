<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 23.11.2017
 * Time: 9:46
 */

namespace common\helpers\customHandlers;

use frontend\helpers\MessageContainer;
use frontend\modules\addresses\models\Address;
use frontend\modules\directories\models\TypeAP;
use frontend\modules\directories\models\TypeApGeoData;
use frontend\modules\works\dynamic\models\WorkDynamic;
use frontend\modules\works\models\EasAddress;
use frontend\modules\works\models\Work;
use frontend\modules\works\models\WorksAddresses;
use toris\base\exceptions\SaveARException;
use toris\logger\helpers\Logger;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;
use Yii;

class ReSaveEacWorksToEnableOnMap extends BaseObject implements JobInterface
{

    /**
     * @param Queue $queue
     * @throws SaveARException
     */
    public function execute($queue)
    {
        $cont = new MessageContainer();
        $query = Work::find()
            ->from(['work' => Work::tableName()])
            ->leftJoin(['work_ap' => WorksAddresses::tableName()], 'work_ap.work_id = work.id')
            ->leftJoin(['ap' => Address::tableName()], 'work_ap.address_id = ap.id')
            ->leftJoin(['ta' => TypeAP::tableName()], 'ap.dic_type_ap_id = ta.id')
            ->leftJoin(['ta_geo' => TypeApGeoData::tableName()], 'ta.id = ta_geo.type_ap_id')
            ->where(['ta_geo.eas_only' => true]);
        foreach ($query->each(1000) as $workModel) {
            /** @var Work $workModel */
            $workModel->setScenario(Work::SCENARIO_ADMIN_UPDATE);
            $workModel = $this->checkWorkAddr($workModel);
            $dynamicModel = new WorkDynamic([], ['mainModel' => $workModel]);
            if (!$dynamicModel->save()) {
                $cont->setFalse($dynamicModel);
                Logger::writeLogStatic($cont->msg, Logger::TYPE_ERROR);
            }
            Yii::getLogger()->flush();
            gc_collect_cycles();
        }
    }

    protected function checkWorkAddr(Work $workModel): Work
    {
        if (!$workModel->workAddr) {
            if ($workModel->eas_code) {
                if ($easAddressModel = EasAddress::find()->where(['eas_code' => $workModel->eas_code])->one()) {
                    $workModel->workAddr = $easAddressModel->address;
                } else {
                    $workModel->eas_code = null;
                }
            }
        }
        return $workModel;
    }
}
