<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 12.04.2018
 * Time: 12:42
 */

namespace console\helpers\excelImport;

use frontend\modules\directories\models\TypeWork;
use frontend\modules\templates\models\sub\TemplateSubFieldAdditional;
use frontend\modules\templates\models\Template;
use frontend\modules\works\dynamic\models\WorkDynamic;
use toris\base\exceptions\SaveARException;
use yii\base\BaseObject;
use yii\base\ErrorException;

class WorkTypeData extends BaseObject
{
    /** @var TypeWork */
    public $wt;

    /** @var integer */
    public $startColIndex;

    /** @var integer */
    public $endColIndex;

    /** @var integer */
    public $volumeIndex;

    /** @var integer */
    public $volumeMetrics;

    /** @var integer */
    public $costIndex;

    /** @var integer */
    public $costMetrics;

    /** @var string */
    public $stairs;

    /** @var integer */
    public $stairsIndex = -1;

    /** @var WorkDynamic */
    public $dynamicWork;

    /** @var Template */
    public $template;

    /** @var TemplateSubFieldAdditional */
    protected $fieldAdditionalStairs;

    /** @var array */
    protected $postFormatted = [];

    /** @var MetricsHandler */
    protected $metricsHandler;

    /** @var bool Нужно ли создавать работу для данного вида работ с текущей строке */
    protected $needCreateWork = true;

    public function init()
    {
        if (!empty($this->template->fieldsAdditional)) {
            foreach ($this->template->fieldsAdditional as $fieldAdditional) {
                if ($fieldAdditional->code == ImportWorksFromExcelHelper::SUB_FIELD_CODE) {
                    $this->fieldAdditionalStairs = $fieldAdditional;
                    break;
                }
            }
        }
        if (!$this->fieldAdditionalStairs) {
            throw new ErrorException('У шаблона должны быть динамические поля. Куда лестничную клетку сохранять буим?');
        }

        $this->metricsHandler = new MetricsHandler();
    }

    /**
     * Индекс колонки относится к этому виду работы
     * @param $key
     * @return bool
     */
    public function in($key): bool
    {
        return $key >= $this->startColIndex && $key <= $this->endColIndex;
    }



    /**
     * Прочитать данные из второй строки и созранить их как объем, стоимость или инфу о лестничной клетке
     * @param integer $index номер столбца
     * @param string $metric еденицы измерения
     */
    public function setMetric(int $index, string $metric)
    {
        if (strpos($metric, 'руб')) {
            $this->costMetrics = $this->metricsHandler->getProdMetric($metric);
            $this->costIndex = $index;
        } elseif ($metric == 'л.кл') {
            $this->stairs = $metric;
            $this->stairsIndex = $index;
        } else {
            $this->volumeMetrics = $this->metricsHandler->getProdMetric($metric);
            $this->volumeIndex = $index;
        }
    }



    public function handleDataCell(int $colIndex, string $value)
    {
        if (!$value) {
            $this->needCreateWork = false;
            return;
        }
        if ($colIndex == $this->startColIndex) {
            $this->createPostFormatted();
        }
        switch ($colIndex) {
            case $this->volumeIndex:
                $this->postFormatted[$this->dynamicWork->formName()]['plan_volume'] = $value;
                $this->postFormatted[$this->dynamicWork->formName()]['plan_volume_metric_id'] = $this->volumeMetrics;
                break;
            case $this->costIndex:
                $this->postFormatted[$this->dynamicWork->formName()]['plan_cost'] = $value;
                $this->postFormatted[$this->dynamicWork->formName()]['plan_cost_metric_id'] = $this->costMetrics;
                break;
            case $this->stairsIndex:
                $fieldName = WorkDynamic::FIELD_PREFIX . $this->fieldAdditionalStairs->id;
                $this->postFormatted[$this->dynamicWork->formName()]['fields'][$this->fieldAdditionalStairs->id] = $fieldName;
                $this->postFormatted[$this->dynamicWork->formName()][$fieldName] = $value;
                break;
            default:
                throw new ErrorException("Индекс колонки не относится к данным о выбранном виде работы - ошибка логики!
                $this");
        }
        if ($colIndex == $this->endColIndex) {
            $this->dynamicWork->loadFields($this->postFormatted);
            if (!$this->dynamicWork->load($this->postFormatted)) {
                throw new ErrorException('При импорте из excel возникла проблема при загрузке данных в WorkDynamic');
            }
            if (!$this->dynamicWork->save()) {
                throw new SaveARException($this->dynamicWork);
            }
        }
    }

    public function __toString()
    {
        return "this->wt->name:{$this->wt->name}; this->startColIndex:{$this->startColIndex}; 
        this->endColIndex:{$this->endColIndex}; this->dynamicWork->name:{$this->dynamicWork->name}";
    }

    /**
     * Создание массива данных для заполнения объекта WorkDynamic
     */
    protected function createPostFormatted()
    {
        $this->postFormatted[$this->dynamicWork->formName()] = [
            'type_work' => $this->wt->id,
        ];
        $this->postFormatted[$this->dynamicWork->formName()]['fields'] = [];
        foreach ($this->template->fieldsAdditional as $fieldAdditional) {
            $fieldName = WorkDynamic::FIELD_PREFIX . $fieldAdditional->id;
            $this->postFormatted[$this->dynamicWork->formName()]['fields'][$fieldAdditional->id] = $fieldName;
            $this->postFormatted[$this->dynamicWork->formName()][$fieldName] = '';
        }
    }
}
