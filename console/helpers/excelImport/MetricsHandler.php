<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 12.04.2018
 * Time: 18:16
 */

namespace console\helpers\excelImport;

use toris\base\exceptions\SaveARException;
use toris\templates\modules\directories\models\Metrics;
use yii\base\ErrorException;

class MetricsHandler
{
    /** @var array excel => prod */
    protected $arExcelProdMetricNames = [
        'т.кв.м' => [
            'name' => '1000 м&sup2;',
            'type' => Metrics::TYPE_VOLUME
        ],
        'т.руб.' => [
            'name' => 'тыс. руб.',
            'type' => Metrics::TYPE_COST
        ],
        'куб.м' => [
            'name' => 'м&sup3;',
            'type' => Metrics::TYPE_VOLUME
        ],
        'п.м' => [
            'name' => 'П.М',
            'type' => Metrics::TYPE_VOLUME
        ],
        'т.п.м' => [
            'name' => 'Т.П.М',
            'type' => Metrics::TYPE_VOLUME
        ],
        'шт.' => [
            'name' => 'ШТ.',
            'type' => Metrics::TYPE_VOLUME
        ],
    ];

    /**
     * Создать необходимые метрические системы
     * @throws SaveARException
     */
    public function createMetrics()
    {
        foreach ($this->arExcelProdMetricNames as $metricData) {
            if (!Metrics::find()->where($metricData)->one()) {
                $model = new Metrics($metricData);
                $model->setScenario('admin-create');
                if (!$model->save()) {
                    throw new SaveARException($model);
                }
            }
        }
    }

    /**
     * На проде уже заведены некоторые метрические системы,
     * чтобы не плодить одни и те же с разными названиями делается подмена
     * @param string $metric
     * @throws ErrorException
     * @return integer metric id
     */
    public function getProdMetric(string $metric)
    {
        if (!isset($this->arExcelProdMetricNames[$metric])) {
            throw new ErrorException('Пришла неизвестная метрическая система, обновить список!');
        }
        /** @var $model Metrics */
        if (!$model = Metrics::find()->where($this->arExcelProdMetricNames[$metric])->one()) {
            throw new ErrorException('Метрическая система не найдена в приложении, проверить метод createMetrics()');
        }
        return $model->id;
    }
}
