<?php

use yii\db\Migration;

class m250722_143902_add_users_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `users` 
            ADD INDEX `idx_users_first_name` (`first_name`(50)),
            ADD INDEX `idx_users_last_name` (`last_name`(50)),
            ADD INDEX `idx_users_fullname` (`first_name`(50), `last_name`(50))
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
