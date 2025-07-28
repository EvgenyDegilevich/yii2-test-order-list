<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Users model
 * 
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 *
 * @property Order[] $orders
 */
class Users extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['first_name', 'last_name'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 300],
            [['first_name', 'last_name'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('orders', 'ID'),
            'first_name' => Yii::t('orders', 'First Name'),
            'last_name' => Yii::t('orders', 'Last Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Orders::class, ['user_id' => 'id']);
    }
}
