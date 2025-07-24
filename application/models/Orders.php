<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\enums\OrderStatus;
use app\enums\OrderMode;

/**
 * Orders model
 */
class Orders extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'link', 'quantity', 'service_id', 'status', 'mode'], 'required'],
            [['user_id', 'quantity', 'service_id', 'status', 'created_at', 'mode'], 'integer'],
            [['link'], 'string', 'max' => 300],
            [['link'], 'url'],
            [['status'], 'in', 'range' => OrderStatus::values()],
            [['mode'], 'in', 'range' => OrderMode::values()],
            [['quantity'], 'integer', 'min' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('orders', 'ID'),
            'user_id' => Yii::t('orders', 'User'),
            'link' => Yii::t('orders', 'Link'),
            'quantity' => Yii::t('orders', 'Quantity'),
            'service_id' => Yii::t('orders', 'Service'),
            'status' => Yii::t('orders', 'Status'),
            'created_at' => Yii::t('orders', 'Created At'),
            'mode' => Yii::t('orders', 'Mode'),
        ];
    }

    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(OrderUsers::class, ['id' => 'user_id']);
    }

    public function getService(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Services::class, ['id' => 'service_id']);
    }
}
