<?php

use yii\db\Migration;

class m250722_143505_add_fk_orders_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `orders` 
            ADD CONSTRAINT `fk_orders_user_id` 
            FOREIGN KEY (`user_id`) 
            REFERENCES `users` (`id`) 
            ON DELETE RESTRICT 
            ON UPDATE CASCADE
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
