<?php
/**
 * Created by PhpStorm.
 * User: filatov
 * Date: 08.06.2018
 * Time: 15:42
 */

namespace frontend\modules\openData\models;

use yii\base\Model;

class ColumnObject extends Model
{
    public $data_type;
    public $title;
    public $name;
    public $description;
    public $description_en;
    public $dimension;
    public $is_primary;
    public $is_main_location;

    public function init()
    {
        $this->title = strtolower($this->title);
    }

    public function attributeLabels()
    {
        return [
            'data_type' => 'тип поля данных',
            'title' => 'имя атрибута',
            'name' => 'русское имя атрибута',
            'description' => 'описание атрибута',
            'description_en' => 'англоязычное описание атрибута',
            'dimension' => 'размерность атрибута (null без размерности)',
            'is_primary' => 'флаг, указывающий является ли атрибут ключом',
            'is_main_location' => 'флаг, определяющий какое из полей визуализировать на карте при наличии нескольких адресных атрибутов',
        ];
    }
}