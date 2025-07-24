<?php

use yii\db\Migration;

class m250722_143534_add_fk_orders_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `orders` 
            ADD CONSTRAINT `fk_orders_service_id` 
            FOREIGN KEY (`service_id`) 
            REFERENCES `services` (`id`) 
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
