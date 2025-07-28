<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\enums\OrderSearchType;

/** @var yii\web\View $this */
/** @var app\modules\orders\models\OrdersSearch $model */
/** @var yii\widgets\ActiveForm $form */

$status = Yii::$app->request->get('status');
?>
<style>
    .search-errors {
        margin-bottom: 10px;
        padding: 5px 10px;
        font-size: 12px;
    }
</style>

<li class="pull-right custom-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/orders/' . ($status ?? '')],
        'method' => 'get',
        'options' => [
            'class' => 'form-inline'
        ],
        'fieldConfig' => [
            'template' => '{input}',
            'options' => ['class' => '']
        ]
    ]); ?>

    <?= $form->errorSummary($model, [
        'class' => 'alert alert-danger search-errors',
        'style' => $model->hasErrors() ? '' : 'display: none;'
    ]) ?>

    <div class="input-group">
        <?= $form->field($model, 'search', ['options' => ['tag' => false],])->textInput([
            'class' => 'form-control',
            'placeholder' => Yii::t('orders', 'Search orders'),
        ])->label(false) ?>

        <span class="input-group-btn search-select-wrap">
            <?= $form->field($model, 'search_type', ['options' => ['tag' => false]])->dropDownList(
                OrderSearchType::getSearchTypes(), [
                'class' => 'form-control search-select',
            ])->label(false) ?>
            
            <?= Html::submitButton(
                '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>',
                [
                    'class' => 'btn btn-default',
                    'type' => 'submit'
                ]
            ) ?>
        </span>
    </div>
    <?php ActiveForm::end(); ?>
</li>
