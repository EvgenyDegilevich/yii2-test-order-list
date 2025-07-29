<?php

use app\enums\OrderMode;
use app\modules\orders\models\OrdersSearch;
use app\modules\orders\widgets\DropdownFilterWidget;
use yii\helpers\Html;

/**
 * @var $orders array
 * @var $model OrdersSearch
 */
?>

<table class="table order-table">
    <thead>
    <tr>
        <th><?= Yii::t('orders', 'table.header.column.id') ?></th>
        <th><?= Yii::t('orders', 'table.header.column.user') ?></th>
        <th><?= Yii::t('orders', 'table.header.column.link') ?></th>
        <th><?= Yii::t('orders', 'table.header.column.quantity') ?></th>
        <th class="dropdown-th">
            <?= DropdownFilterWidget::widget([
                'type' => DropdownFilterWidget::TYPE_SERVICE,
                'selectedValue' => $model->service_id,
                'label' => Yii::t('orders', 'table.header.column.service'),
                ...$model->getServiceFilterData()
            ]) ?>
        </th>
        <th><?= Yii::t('orders', 'table.header.column.status') ?></th>
        <th class="dropdown-th">
            <?= DropdownFilterWidget::widget([
                'type' => DropdownFilterWidget::TYPE_MODE,
                'items' => OrderMode::getOrderModes(),
                'selectedValue' => $model->mode,
                'label' => Yii::t('orders', 'table.header.column.mode'),
            ]) ?>
        </th>
        <th><?= Yii::t('orders', 'table.header.column.created') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= Html::encode($order['id']) ?></td>
            <td><?= Html::encode($order['user_full_name']) ?></td>
            <td class="link"><?= Html::encode($order['link']) ?></td>
            <td><?= Html::encode($order['quantity']) ?></td>
            <td class="service">
                <span class="label-id"><?= Html::encode($order['service_id']) ?></span>
                <?= Html::encode($order['service_name']) ?>
            </td>
            <td><?= Html::encode($order['status_label']) ?></td>
            <td><?= Html::encode($order['mode_label']) ?></td>
            <td>
                <span class="nowrap"><?= $order['formatted_date'] ?></span>
                <span class="nowrap"><?= $order['formatted_time'] ?></span>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>