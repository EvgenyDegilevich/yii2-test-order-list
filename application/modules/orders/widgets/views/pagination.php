<?php

use yii\data\Pagination;
use yii\widgets\LinkPager;

/**
 * @var $pagination Pagination
 */
?>

<div class="row">
    <div class="col-sm-8">
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination'],
            'linkContainerOptions' => ['class' => ''],
            'linkOptions' => ['class' => ''],
            'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => ''],
        ]) ?>
    </div>
    <div class="col-sm-4 pagination-counters">
        <?= Yii::t('orders', 'pagination.summary', [
            'start' => $pagination->offset + 1,
            'end' => min($pagination->offset + $pagination->limit, $pagination->totalCount),
            'total' => $pagination->totalCount
        ]) ?>
    </div>
</div>