<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue', 'open-data'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => '@runtime/logs/debug.log',
                    'categories' => ['console\helpers\*'],
                ],
            ],
        ],
        'urlManager' => [
            'scriptUrl' => '/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ]
    ],
    'params' => $params,
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'console\migrations',
            ],
            'migrationPath' => null, // disable not namespaced migration completely
        ],
    ],
    'modules' => [
        'open-data' => \frontend\modules\openData\OpenData::class
    ],
];
