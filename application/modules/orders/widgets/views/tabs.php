<?php

use app\enums\OrderSearchType;
use app\modules\orders\models\OrdersSearch;
use yii\helpers\Html;

/**
 * @var $navItems array
 * @var $model OrdersSearch
 */
?>

<ul class="nav nav-tabs p-b">
    <?php foreach ($navItems as $navItem) : ?>
        <?php if ($navItem['active']) : ?>
            <?php $formAction = $navItem['url']; ?>
        <?php endif; ?>
        <li class="<?= $navItem['active'] ? 'active' : '' ?>">
            <?= Html::a($navItem['label'], $navItem['url']) ?>
        </li>
    <?php endforeach; ?>
    <li class="pull-right custom-search">
        <?= Html::beginForm($formAction ?? ['/orders'], 'get', ['class' => 'form-inline']) ?>
            <div class="input-group">
                <?= Html::textInput('search', $model->search ?? '', [
                    'class' => 'form-control',
                    'placeholder' => Yii::t('orders', 'search.placeholder'),
                ]) ?>
                <span class="input-group-btn search-select-wrap">
                    <?= Html::dropDownList(
                        'search_type',
                        (int)($model->search_type ?? 1),
                        OrderSearchType::getSearchTypes(),
                        ['class' => 'form-control search-select']
                    ) ?>
                    <button type="submit" class="btn btn-default">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                    </button>
                </span>
            </div>
        <?= Html::endForm() ?>
    </li>
</ul>