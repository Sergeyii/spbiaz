<?php

namespace frontend\modules\openData\models;
use yii\helpers\Html;

/**
 * Class OpenData
 * @package frontend\modules\openData\models
 *
 */
class OpenData extends \frontend\modules\openData\models\base\OpenDataBase
{
    /** @var OpenDataStructure */
    public $structure;

    /** @var array */
    protected $fields;

    public function init()
    {
        $this->structure = new OpenDataStructure();

    }

    /**
     * Get field from json
     * @param string $name
     * @return null
     */
    public function jsonField(string $name)
    {
        if (!$this->fields) {
            $this->fields = json_decode($this->row);
        }
        $out = $this->fields->$name ?? '';
        if (filter_var($out, FILTER_VALIDATE_URL)) {
            $out = Html::a('ссылка', $out);
        }
            return $out;
    }

    public function attributeLabels()
    {
        $labels = [];
        foreach ($this->structure->columns as $column) {
            $labels[$column->title] = $column->name;
        }
        return $labels;
    }
}