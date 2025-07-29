<?php

use app\helpers\OrderHelper;
use app\modules\orders\widgets\DropdownFilterWidget;
use yii\helpers\Html;

/**
 * @var $type string
 * @var $label string
 * @var $totalCount int|null
 * @var $selectedValue int|null
 * @var $items array
 */
?>

<div class="dropdown">
    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="true">
        <?= $label ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li class="<?= !isset($selectedValue) ? 'active' : '' ?>">
            <a href="<?= OrderHelper::createFilterUrl($type) ?>">
                <?= Yii::t('orders', 'filter.all') ?>
                <?php if ($totalCount !== null): ?>
                    (<?= $totalCount ?>)
                <?php endif; ?>
            </a>
        </li>
        <?php foreach ($items as $value => $item): ?>
            <?php $liClass = ''; ?>
            <?php if (isset($selectedValue) && $selectedValue == $value): ?>
                <?php $liClass = 'active'; ?>
            <?php elseif (isset($item['count']) && $item['count'] == 0): ?>
                <?php $liClass = 'disabled'; ?>
            <?php endif; ?>
            <li class="<?= $liClass ?>">
                <a href="<?= OrderHelper::createFilterUrl($type, $value) ?>">
                    <?php if ($type === DropdownFilterWidget::TYPE_SERVICE): ?>
                        <span class="label-id"><?= $value ?></span>
                        <?= Html::encode($item['name'] . ' (' . $item['count'] . ')') ?>
                    <?php else: ?>
                        <?= Html::encode($item) ?>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>