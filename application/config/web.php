<?php
require_once __DIR__ . '/../enums/OrderStatus.php';

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'language' => $_ENV['APP_LANGUAGE'] ?? 'en-US',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => $_ENV['YII_COOKIE_VALIDATION_KEY'] ?? 'your-secret-key-here',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'orders/default/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => ($_ENV['APP_DEBUG'] ?? 'true') === 'true' ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'orders/default/index',
                'orders' => 'orders/default/index',
                'orders/<status:(' . implode('|', \app\enums\OrderStatus::getSlugs()) . ')>' => 'orders/default/index',
                'orders/export' => 'orders/default/export',
            ],
        ],
        'i18n' => [
            'translations' => [
                'orders*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'orders' => 'orders.php',
                    ],
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap5\BootstrapAsset' => false,
                'yii\bootstrap5\BootstrapPluginAsset' => false,
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@app/modules/orders/assets/src',
                    'js' => ['js/jquery.min.js'],
                    'jsOptions' => ['position' => 1]
                ],
            ],
        ],
    ],
    'params' => $params,
    'modules' => [
        'orders' => [
            'class' => 'app\modules\orders\Module',
        ],
    ],
];

if (($_ENV['APP_DEBUG'] ?? 'true') === 'true' && ($_ENV['APP_ENV'] ?? 'dev') === 'dev') {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*', '185.59.220.198'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*', '185.59.220.198'],
    ];
}

return $config;
