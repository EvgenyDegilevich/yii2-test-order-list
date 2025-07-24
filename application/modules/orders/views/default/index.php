<?php

use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use app\enums\OrderStatus;
use app\helpers\OrderHelper;
use yii\data\Pagination;
use app\widgets\CustomLinkPager;

/* @var $this yii\web\View */
/* @var $models array */
/* @var $currentStatus string|null */
/* @var $filterData array */
/* @var $cursorPagination app\modules\orders\components\CursorPagination */
/* @var $searchParams array */

$this->title = Yii::t('orders', 'Orders');

$dataProvider = new ArrayDataProvider([
    'allModels' => $models,
    'pagination' => false,
    'sort' => false,
]);

$navItems[] = [
    'label' => Yii::t('orders', 'All orders'),
    'url' => ['/orders'],
    'active' => $currentStatus === null,
    'options' => $currentStatus === null ? ['class' => 'active'] : [],
    'linkOptions' => ['class' => false],
];

foreach (OrderStatus::cases() as $statusEnum) {
    $statusSlug = $statusEnum->getSlug();

    $navItems[] = [
        'label' => $statusEnum->getLabel(),
        'url' => ['/orders/' . $statusSlug],
        'active' => $currentStatus === $statusSlug,
        'options' => $currentStatus === $statusSlug ? ['class' => 'active'] : [],
        'linkOptions' => ['class' => false],
    ];
}

$searchForm = '<li class="pull-right custom-search">' .
    Html::beginForm(['/orders/' . ($currentStatus ?? '')], 'get', ['class' => 'form-inline']) .
    '<div class="input-group">' .
    Html::textInput('search', $searchParams['search'] ?? '', [
        'class' => 'form-control',
        'placeholder' => Yii::t('orders', 'Search orders'),
    ]) .
    '<span class="input-group-btn search-select-wrap">' .
    Html::dropDownList(
        'search_type',
        (int)($searchParams['search_type'] ?? 1),
        OrderHelper::getSearchTypes(),
        ['class' => 'form-control search-select']
    ) .
    '<button type="submit" class="btn btn-default">' .
    '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>' .
    '</button>' .
    '</span>' .
    '</div>' .
    Html::endForm() .
    '</li>';

$navItems[] = $searchForm;
$paginationParams = $searchParams;
unset($paginationParams['cursor'], $paginationParams['direction'], $paginationParams['page']);
?>

<?= Nav::widget([
    'items' => $navItems,
    'options' => ['class' => 'nav nav-tabs p-b'],
    'encodeLabels' => false,
]) ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table order-table'],
    'layout' => '{items}',
    'columns' => [
        [
            'attribute' => 'id',
            'label' => Yii::t('orders', 'ID'),
            'value' => function ($model) {
                return Html::encode($model['id']);
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'user_full_name',
            'label' => Yii::t('orders', 'User'),
            'value' => function ($model) {
                return Html::encode($model['user_full_name']);
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'link',
            'label' => Yii::t('orders', 'Link'),
            'value' => function ($model) {
                return Html::tag('span', Html::encode($model['link']), ['class' => 'link']);
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'quantity',
            'label' => Yii::t('orders', 'Quantity'),
            'value' => function ($model) {
                return Html::encode($model['quantity']);
            },
            'format' => 'raw',
        ],
        [
            'label' => Yii::t('orders', 'Service'),
            'value' => function ($model) {
                return Html::tag('span', $model['service_id'], ['class' => 'label-id']) .
                    Html::encode($model['service_name']);
            },
            'format' => 'raw',
            'contentOptions' => ['class' => 'service'],
            'headerOptions' => ['class' => 'dropdown-th'],
            'header' => $this->render('_service_filter', [
                'filterData' => $filterData,
                'searchParams' => $searchParams
            ]),
        ],
        [
            'attribute' => 'status_label',
            'label' => Yii::t('orders', 'Status'),
            'value' => function ($model) {
                return Html::encode($model['status_label']);
            },
            'format' => 'raw',
        ],
        [
            'label' => Yii::t('orders', 'Mode'),
            'value' => function ($model) {
                return Html::encode($model['mode_label']);
            },
            'format' => 'raw',
            'headerOptions' => ['class' => 'dropdown-th'],
            'header' => $this->render('_mode_filter', [
                'filterData' => $filterData,
                'searchParams' => $searchParams
            ]),
        ],
        [
            'attribute' => 'created_at',
            'label' => Yii::t('orders', 'Created'),
            'value' => function ($model) {
                return Html::tag('span', $model['formatted_date'], ['class' => 'nowrap']) .
                    Html::tag('span', $model['formatted_time'], ['class' => 'nowrap']);
            },
            'format' => 'raw',
        ],
    ],
]) ?>

<div class="row">
    <div class="col-sm-8">
        <?php if ($cursorPagination->getPageCount() > 1): ?>
            <nav>
                <?= CustomLinkPager::widget([
                    'pagination' => new Pagination([
                        'totalCount' => $filterData['totalCount'],
                        'pageSize' => 100,
                        'page' => $cursorPagination->getCurrentPage() - 1,
                        'pageSizeParam' => false,
                        'pageParam' => 'page',
                        'route' => '/orders/' . ($currentStatus ?? ''),
                        'params' => $paginationParams,
                    ]),
                    'cursorPagination' => $cursorPagination,
                    'searchParams' => $searchParams,
                    'currentStatus' => $currentStatus,
                    'maxButtonCount' => 10,
                ]); ?>
            </nav>
        <?php endif; ?>
    </div>

    <div class="col-sm-4 pagination-counters">
        <?php
        [$start, $end] = $cursorPagination->getCurrentRange();
        echo Yii::t('orders', '{start} to {end} of {total}', [
            'start' => $start,
            'end' => $end,
            'total' => $filterData['totalCount']
        ]);
        ?>

        <?php if (!empty($models)): ?>
            <br>
            <?= Html::a(
                Yii::t('orders', 'Save Result'),
                array_merge(['/orders/export'], $searchParams),
                ['class' => 'btn btn-sm btn-default', 'style' => 'margin-top: 5px;', 'target' => '_blank']
            ) ?>
        <?php endif; ?>
    </div>
</div>