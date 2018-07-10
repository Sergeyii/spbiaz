<?php

namespace frontend\modules\openData\query;

/**
 * This is the ActiveQuery class for [[\frontend\modules\openData\models\OpenData]].
 *
 * @see \frontend\modules\openData\models\OpenData
 */
class OdQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \frontend\modules\openData\models\OpenData[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \frontend\modules\openData\models\OpenData|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
