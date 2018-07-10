<?php

namespace frontend\modules\openData\models\base;

/**
 * This is the model class for table "od_structure".
*
    * @property integer $num_id
    * @property string $create_at
    * @property string $structure
*/
class OpenDataStructureBase extends \yii\db\ActiveRecord
{
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'od_structure';
    }

    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            [['create_at'], 'safe'],
            [['structure'], 'safe'],
        ];
    }

    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return [
            'num_id' => 'Num ID',
            'create_at' => 'Create At',
            'structure' => 'Structure',
        ];
    }

        /**
         * @inheritdoc
         * @return \frontend\modules\openData\query\OdStructureQuery the active query used by this AR class.
         */
        public static function find()
        {
            return new \frontend\modules\openData\query\OdStructureQuery(get_called_class());
        }
}