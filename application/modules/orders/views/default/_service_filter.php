<?php

use yii\helpers\Html;
use app\helpers\OrderHelper;

/**
 * @var $services array
 * @var $filterServiceId ?int
 * @var $totalCount int
 */
?>

<div class="dropdown">
    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= Yii::t('orders', 'Service') ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li class="<?= empty($filterServiceId) ? 'active' : '' ?>">
            <a href="<?= OrderHelper::createFilterUrl('service_id') ?>">
                <?= Yii::t('orders', 'All') ?> (<?= $totalCount ?? 0 ?>)
            </a>
        </li>
        <?php foreach ($services as $serviceId => $serviceData): ?>
            <?php
            $liClass = '';
            if ($filterServiceId == $serviceId) {
                $liClass = 'active';
            } elseif ($serviceData['count'] == 0) {
                $liClass = 'disabled';
            }
            ?>
            <li class="<?= $liClass ?>">
                <a href="<?= OrderHelper::createFilterUrl('service_id', $serviceId) ?>">
                    <span class="label-id"><?= $serviceData['count'] ?></span>
                    <?= Html::encode($serviceData['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>