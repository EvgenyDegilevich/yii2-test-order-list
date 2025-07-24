<?php

use yii\db\Migration;

class m250722_143950_add_services_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `services` 
            ADD INDEX `idx_services_name` (`name`(100))
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
