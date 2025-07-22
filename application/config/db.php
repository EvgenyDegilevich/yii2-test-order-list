<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf('mysql:host=%s;dbname=%s', 
        $_ENV['DB_HOST'] ?? 'mysql', 
        $_ENV['DB_NAME'] ?? 'yii2_app'
    ),
    'username' => $_ENV['DB_USER'] ?? 'yii2_user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'yii2_password',
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    'enableSchemaCache' => ($_ENV['APP_ENV'] ?? 'dev') === 'prod',
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];