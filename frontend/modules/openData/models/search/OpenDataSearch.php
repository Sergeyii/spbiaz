<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 15:32
 */

namespace frontend\modules\openData\models\search;

use frontend\modules\openData\models\OpenData;
use frontend\modules\openData\models\OpenDataStructure;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OpenDataSearch extends OpenData
{
    /** Захордкодил поля, для вывод на индексной странице */
    const COLUMNS_FOR_INDEX_PAGE = [
        'Kod',
        'Status',
        'Zakazchik',
        'INN_zakazchika',
        'Rajon',
        'Adres',
        'Vid_rabot',
        'Titul',
        'DNR_zajavl.',
        'DOR_zajavl.',
        'DNR_sogl.',
        'DOR_sogl.',
        'DFNR',
    ];

    private $dynamicFields = [];

    public function __get($name)
    {
        return $this->dynamicFields[$name] ?? null;
    }

    public function __set($name, $value)
    {
        return $this->dynamicFields[$name] = $value;
    }

    public function init()
    {
        parent::init();

        foreach($this->getPurifiedColumnsNames() as $propertyName){
            $this->{$propertyName} = null;
        }
    }

    public function rules()
    {
        return [
            [$this->getPurifiedColumnsNames(), 'string'],
        ];
    }

    private function getPurifiedAlias($title): string
    {
        return str_replace('.', '', $title);
    }

    private function getPurifiedColumnsNames(): array
    {
        return array_map(function($title){
            return $this->getPurifiedAlias($title);
        }, ArrayHelper::getColumn($this->structure->columns, 'title'));
    }

    public function search($params)
    {
        $this->load($params);

        $columnsToSelect = ['num_id' => 'num_id'];;
        foreach ($this->structure->columns as $column) {
            $alias = $this->getPurifiedAlias($column->title);
            $columnsToSelect[$alias] = new Expression("row->>'$column->title'");
        }

        $q = (new Query())
            ->select($columnsToSelect)
            ->from(OpenData::tableName());

        // grid filtering conditions
        $q->andFilterWhere([
            'num_id' => $this->num_id,
        ]);

        foreach($this->getPurifiedColumnsNames() as $propertyName){
            //Замечание: круглые скобки нужны для правильной работы json выражения
            $q->andFilterWhere([
                'LIKE', new Expression("(row->>'".$propertyName."')"), $this->{$propertyName}
            ]);
        }

        $models = $q->all();

        $dp = new ArrayDataProvider([
            'allModels' => $models,
            'sort' => [
                'attributes' => ArrayHelper::getColumn($this->structure->columns, 'title')
            ],
        ]);

        if(!$this->validate()){
            // uncomment the following line if you do not want to return any records when validation fails
            $q->where('0=1');
            return $dp;
        }

        return $dp;
    }
}