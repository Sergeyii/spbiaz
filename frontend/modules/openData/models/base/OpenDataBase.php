<?php

namespace frontend\modules\openData\models\base;

/**
 * This is the model class for table "od_data".
 *
 * @property integer $num_id
 * @property string $row
 */
class OpenDataBase extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'od_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['row'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'num_id' => 'Num ID',
            'row' => 'Row',
        ];
    }

    /**
     * @inheritdoc
     * @return \frontend\modules\openData\query\OdQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \frontend\modules\openData\query\OdQuery(get_called_class());
    }
}