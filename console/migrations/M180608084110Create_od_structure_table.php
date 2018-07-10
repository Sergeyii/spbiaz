<?php

namespace console\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `open_data_structure_9`.
 */
class M180608084110Create_od_structure_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('od_structure', [
            'num_id' => $this->primaryKey(),
            'create_at' => $this->date(),
            'structure' => 'jsonb'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('od_structure');
    }
}
