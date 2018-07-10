<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 05.09.2017
 * Time: 17:35
 */

namespace frontend\helpers;

use toris\base\exceptions\SaveARException;
use frontend\modules\templates\models\sub\TemplateSubField;
use frontend\modules\works\dynamic\models\WorkDynamic;
use frontend\modules\works\dynamic\render\FieldFactory;
use frontend\modules\works\models\Work;
use frontend\modules\works\models\WorkField;
use yii\db\Transaction;
use Yii;

class WorkFieldMoveDynamicToStatic
{
    const COST_WORK_AP3 = 'CostWorkAp3';
    const VOLUME_AP3 = 'VolumeAp3';
    const AMOUNT_WORK_MANUAL_CLEANING = 'AmountWorkManualCleaning';
    const AMOUNT_WORK_AUTOMATIC_CLEANING = 'AmountWorkAutomaticCleaning';
    const FIELD_CODES = [
        self::COST_WORK_AP3, self::VOLUME_AP3, self::AMOUNT_WORK_AUTOMATIC_CLEANING, self::AMOUNT_WORK_MANUAL_CLEANING
    ];

    protected $_values;

    /** @var Transaction */
    protected $_transaction;

    /** @var array Work ids */
    protected $_updated;

    public function move()
    {
        $this->_transaction = \Yii::$app->db->beginTransaction();
        foreach (Work::find()->each(1000) as $workModel) {
            // без mainModel, т.к. иначе генерируется тонны запросов
            $dynamicModel = new WorkDynamic();
            // isNewRecord нужен для правильного отображения полей-справочников
            $dynamicModel->isNewRecord = $workModel->isNewRecord;
            $this->_initFieldValues();
            $foundFlag = false;
            /** @var WorkField $field */
            foreach (WorkField::find()->where([
                'work_id' => $workModel->id,
                'type' => [
                    TemplateSubField::TYPE_FLOAT,
                    TemplateSubField::TYPE_INTEGER,
                    TemplateSubField::TYPE_STRING,
                    TemplateSubField::TYPE_TEXT,
                ]
            ])->all() as $field) {
                if (in_array($field->code, self::FIELD_CODES)) {
                    $factoryField = new FieldFactory($dynamicModel);
                    $fieldObj = $factoryField->get($field);
                    $value = $fieldObj->getValueOnUpdate();
                    if ($field->multiple) {
                        $value = current($value);
                    }
                    $this->_values[$field->code] = $value;
                    $foundFlag = true;
                }
            }
            if ($foundFlag) {
                $this->_copyValues($workModel);
            }
            Yii::getLogger()->flush();
            gc_collect_cycles();
//            echo memory_get_usage(), PHP_EOL;
        }
        $this->_transaction->commit();
        return $this->_updated;
    }

    protected function _initFieldValues()
    {
        $this->_values = [
            self::COST_WORK_AP3 => null,
            self::VOLUME_AP3 => null,
            self::AMOUNT_WORK_MANUAL_CLEANING => null,
            self::AMOUNT_WORK_AUTOMATIC_CLEANING => null,
        ];
    }

    protected function _copyValues(Work $work)
    {
        $work->setScenario(Work::SCENARIO_API_POB_CREATE);
        if (!$work->plan_cost) {
            $work->plan_cost = floatval($this->_values[self::COST_WORK_AP3]);
        }

        if (!$work->plan_volume) {
            if (!$work->plan_volume = floatval($this->_values[self::VOLUME_AP3])) {
                if (!$work->plan_volume = floatval($this->_values[self::AMOUNT_WORK_AUTOMATIC_CLEANING])) {
                    $work->plan_volume = floatval($this->_values[self::AMOUNT_WORK_MANUAL_CLEANING]);
                }
            }
        }

        if (!$work->save()) {
            $this->_transaction->rollBack();
            throw new SaveARException($work);
        } else {
            $this->_updated[] = $work->id;
        }
    }
}