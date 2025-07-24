<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Services model
 * 
 * @property int $id
 * @property string $name
 *
 * @property Order[] $orders
 */
class Services extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 300],
            [['name'], 'trim'],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('orders', 'ID'),
            'name' => Yii::t('orders', 'Service Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Orders::class, ['service_id' => 'id']);
    }
}
