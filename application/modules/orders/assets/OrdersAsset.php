<?php

namespace app\modules\orders\assets;

use yii\web\AssetBundle;

class OrdersAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/orders/assets/src';
    
    public $css = [
        'css/bootstrap.min.css',
        'css/custom.css',
    ];
    
    public $js = [
        'js/jquery.min.js',
        'js/bootstrap.min.js',
    ];

    public $depends = [];
}