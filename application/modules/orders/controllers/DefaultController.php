<?php

namespace app\modules\orders\controllers;

use app\modules\orders\controllers\actions\ExportAction;
use app\modules\orders\controllers\actions\IndexAction;
use app\modules\orders\models\OrdersSearch;
use app\services\OrderExportService;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Контроллер для управления заказами
 *
 * Основные действия:
 * - index: отображение списка заказов с возможностью фильтрации
 * - export: экспорт отфильтрованных заказов в CSV формат
 *
 * @package app\modules\orders\controllers
 */
class DefaultController extends Controller
{
    /**
     * Использовать специальный макет для модуля заказов
     * @var string
     */
    public $layout = 'orders';

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'index' => ['GET'],
                        'export' => ['GET'],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'index' => IndexAction::class,
            'export' => ExportAction::class,
        ];
    }
}
