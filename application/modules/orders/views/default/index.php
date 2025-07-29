<?php

use app\modules\orders\models\OrdersSearch;
use app\modules\orders\widgets\OrdersTableWidget;
use app\modules\orders\widgets\PaginationWidget;
use app\modules\orders\widgets\TabsWidget;
use yii\data\Pagination;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var OrdersSearch $searchModel
 * @var array $data
 * @var Pagination $pagination
 * @var array $queryParams
 */

$this->title = Yii::t('orders', 'title');

echo TabsWidget::widget(['model' => $searchModel]);

if ($searchModel->hasErrors()) {
    echo Html::errorSummary($searchModel, ['class' => 'alert alert-danger']);
}

echo OrdersTableWidget::widget([
    'model' => $searchModel,
    'orders' => $data
]);

echo PaginationWidget::widget(['pagination' => $pagination]);

if ($pagination->totalCount > 0) {
    echo Html::a(
        Yii::t('orders', 'export.button'),
        array_merge(['/orders/export'], $queryParams),
        ['class' => 'btn btn-sm btn-default pull-right', 'target' => '_blank']
    );
}
