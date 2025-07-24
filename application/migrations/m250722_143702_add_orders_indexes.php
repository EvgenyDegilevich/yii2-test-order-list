<?php

use yii\db\Migration;

class m250722_143702_add_orders_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
            ALTER TABLE `orders` 
            ADD INDEX `idx_orders_id_desc` (`id` DESC),
            ADD INDEX `idx_orders_created_at` (`created_at`),
            ADD INDEX `idx_orders_link` (`link`(100)),
            ADD INDEX `idx_orders_status_id_desc` (`status`, `id` DESC),
            ADD INDEX `idx_orders_mode_id_desc` (`mode`, `id` DESC),
            ADD INDEX `idx_orders_status_mode` (`status`, `mode`),
            ADD INDEX `idx_orders_status_service_id_desc` (`status`, `service_id`, `id` DESC),
            ADD INDEX `idx_orders_filters_id_desc` (`status`, `service_id`, `mode`, `id` DESC)
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
