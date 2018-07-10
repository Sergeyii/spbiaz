<?php

namespace frontend\modules\openData\models;

use yii\db\Query;

/**
 * Class OpenDataStructure
 * @package frontend\modules\openData\models
 * @property ColumnObject[] $columns
 */
class OpenDataStructure extends \frontend\modules\openData\models\base\OpenDataStructureBase
{
    protected $_columns;

    public function getColumns()
    {
        if (!$this->_columns) {
            $this->_columns = [];
            $arRawData = (new Query)->select(['data' => 'jsonb_array_elements(structure)'])->from(OpenDataStructure::tableName())->all();
            foreach ($arRawData as $rawData) {
                $this->_columns[] = new ColumnObject(json_decode($rawData['data']));
            }
        }
        return $this->_columns;
    }
}