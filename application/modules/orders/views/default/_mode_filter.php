<?php

use app\enums\OrderMode;
use app\helpers\OrderHelper;

/* @var $filterData array */
/* @var $searchParams array */
?>

<div class="dropdown">
    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= Yii::t('orders', 'Mode') ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
        <li class="<?= !isset($searchParams['mode']) ? 'active' : '' ?>">
            <a href="<?= OrderHelper::createModeFilterUrl(null, $searchParams) ?>">
                <?= Yii::t('orders', 'All') ?>
            </a>
        </li>
        <?php foreach (OrderMode::cases() as $modeEnum): ?>
            <?php
            $count = $filterData['modeCounts'][$modeEnum->value] ?? 0;
            $classActive = '';

            if (isset($searchParams['mode'])) {
                $classActive = $searchParams['mode'] == $modeEnum->value ? 'active' : '';
            }
            ?>

            <li class="<?= $classActive ?>">
                <a href="<?= OrderHelper::createModeFilterUrl($modeEnum->value, $searchParams) ?>">
                    <?= $modeEnum->getLabel() ?> (<?= $count ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>