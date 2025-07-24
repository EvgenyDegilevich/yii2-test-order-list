<?php

use yii\helpers\Html;
use app\helpers\OrderHelper;

/* @var $filterData array */
/* @var $searchParams array */
?>

<div class="dropdown">
    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= Yii::t('orders', 'Service') ?>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li class="<?= empty($searchParams['service_id']) ? 'active' : '' ?>">
            <a href="<?= OrderHelper::createServiceFilterUrl(null, $searchParams) ?>">
                <?= Yii::t('orders', 'All') ?> (<?= $filterData['totalCount'] ?? 0 ?>)
            </a>
        </li>
        <?php foreach ($filterData['serviceCounts'] as $serviceData): ?>
            <li class="<?= ($searchParams['service_id'] ?? null) == $serviceData['service_id'] ? 'active' : '' ?>">
                <a href="<?= OrderHelper::createServiceFilterUrl($serviceData['service_id'], $searchParams) ?>">
                    <span class="label-id"><?= $serviceData['service_id'] ?></span>
                    <?= Html::encode($serviceData['name']) ?> (<?= $serviceData['count'] ?>)
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>