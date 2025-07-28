<?php

use app\enums\OrderMode;
use app\helpers\OrderHelper;

/**
 * @var $filterMode ?int
 */
?>

<div class="dropdown">
    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= Yii::t('orders', 'Mode') ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
        <li class="<?= !isset($filterMode) ? 'active' : '' ?>">
            <a href="<?= OrderHelper::createFilterUrl('mode') ?>">
                <?= Yii::t('orders', 'All') ?>
            </a>
        </li>
        <?php foreach (OrderMode::cases() as $modeEnum): ?>
            <?php
            $classActive = '';

            if (isset($filterMode)) {
                $classActive = $filterMode == $modeEnum->value ? 'active' : '';
            }
            ?>

            <li class="<?= $classActive ?>">
                <a href="<?= OrderHelper::createFilterUrl('mode', $modeEnum->value ) ?>">
                    <?= $modeEnum->getLabel() ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>