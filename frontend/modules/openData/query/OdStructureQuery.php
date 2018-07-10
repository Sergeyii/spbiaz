<?php

namespace frontend\modules\openData\query;

/**
 * This is the ActiveQuery class for [[\frontend\modules\openData\models\OpenDataStructure]].
 *
 * @see \frontend\modules\openData\models\OpenDataStructure
 */
class OdStructureQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \frontend\modules\openData\models\OpenDataStructure[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \frontend\modules\openData\models\OpenDataStructure|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
