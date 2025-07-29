<?php

namespace app\modules\orders\widgets;

use app\helpers\OrderHelper;
use app\modules\orders\models\OrdersSearch;
use yii\base\Widget;

class TabsWidget extends Widget
{
    public OrdersSearch $model;

    public function run()
    {
        return $this->render('tabs', [
            'navItems' => OrderHelper::getNavItems($this->model->status),
            'model' => $this->model,
        ]);
    }
}