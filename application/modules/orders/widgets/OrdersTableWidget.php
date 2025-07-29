<?php

namespace app\modules\orders\widgets;

use app\modules\orders\models\OrdersSearch;
use yii\base\Widget;

class OrdersTableWidget extends Widget
{
    public array $orders;
    public OrdersSearch $model;

    public function run()
    {
        return $this->render('ordersTable', [
            'orders' => $this->orders,
            'model' => $this->model,
        ]);
    }
}