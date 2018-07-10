<?php

namespace console\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `ad_data_9`.
 */
class M180608093141Create_od_data_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('od_data', [
            'num_id' => $this->primaryKey(),
            'row' => 'jsonb'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('od_data');
    }
}
