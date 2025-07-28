<?php

use app\models\Orders;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use app\enums\OrderStatus;
use app\enums\OrderMode;
use app\helpers\OrderHelper;
use yii\bootstrap5\Nav;

/** @var yii\web\View $this */
/** @var app\modules\orders\models\OrdersSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('orders', 'Orders');

$navItems = OrderHelper::getNavItems($searchModel->status);

$searchForm = $this->render('_search', ['model' => $searchModel]);
$navItems[] = $searchForm;
?>
<div class="orders-index">
    <?= Nav::widget([
        'items' => $navItems,
        'options' => ['class' => 'nav nav-tabs p-b'],
        'encodeLabels' => false,
    ]) ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table order-table'],
        'layout' => '{items}<div class="row"><div class="col-sm-8"><nav>{pager}</nav></div>{summary}</div>',
        'summary' => Yii::t('orders', 'summary', [
            'start' => '{begin}',
            'end' => '{end}',
            'total' => '{totalCount}'
        ]),
        'summaryOptions' => [
            'class' => 'col-sm-4 pagination-counters',
        ], 
        'columns' => [
            'id',
            [
                'label' => Yii::t('orders', 'User'),
                'value' => function ($model) {
                    return Html::encode(trim(($model->user->first_name ?? '') . ' ' . ($model->user->last_name ?? '')));
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'link',
                'value' => function ($model) {
                    return Html::tag('span', Html::encode($model['link']), ['class' => 'link']);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'quantity',
                'value' => function ($model) {
                    return Html::encode($model->quantity);
                },
                'format' => 'raw',
            ],
            [
                'label' => Yii::t('orders', 'Service'),
                'value' => function ($model) {
                    return Html::encode($model->service->name);
                },
                'format' => 'raw',
                'contentOptions' => ['class' => 'service'],
                'headerOptions' => ['class' => 'dropdown-th'],
                'header' => $this->render('_service_filter', [
                    ...$searchModel->getServiceFilterData(),
                    'filterServiceId' => $searchModel->service_id
                ]),
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $status = OrderStatus::tryFrom($model->status);
                    return Html::encode($status?->getLabel() ?? '');
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'mode',
                'value' => function ($model) {
                    $mode = OrderMode::tryFrom($model->mode);
                    return Html::encode($mode?->getLabel() ?? '');
                },
                'format' => 'raw',
                'headerOptions' => ['class' => 'dropdown-th'],
                'header' => $this->render('_mode_filter', [
                    'filterMode' => $searchModel->mode
                ]),
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $date = $model->created_at ? Yii::$app->formatter->asDate($model->created_at, 'Y-m-d') : '';
                    $time = $model->created_at ? Yii::$app->formatter->asTime($model->created_at, 'H:i:s') : '';
                    return Html::tag('span', $date, ['class' => 'nowrap']) .
                        Html::tag('span', $time, ['class' => 'nowrap']);
                },
                'format' => 'raw',
            ],
        ],
    ]); ?>

    <?php if ($dataProvider->getTotalCount() > 0): ?>
        <?= Html::a(
            Yii::t('orders', 'Save Result'),
            array_merge(['/orders/export'], Yii::$app->request->queryParams),
            ['class' => 'btn btn-sm btn-default pull-right', 'target' => '_blank']
        ) ?>
    <?php endif; ?>
</div>