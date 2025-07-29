<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
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

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    public function getService(): ActiveQuery
    {
        return $this->hasOne(Services::class, ['id' => 'service_id']);
    }
}
