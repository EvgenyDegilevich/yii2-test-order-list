<?php

namespace app\modules\orders\controllers;

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
     * Конфигурация поведений контроллера
     *
     * Настраивает фильтры и ограничения для действий контроллера,
     * включая ограничения HTTP методов для безопасных операций.
     *
     * @return array<string, array<string, mixed>> Конфигурация поведений
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'export' => ['GET'],
                    ],
                ],
            ]
        );
    }

    /**
     * Конфигурация автономных действий
     *
     * @return array<string, array<string, mixed>> Конфигурация действий
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Отобразить список всех заказов с возможностью фильтрации
     *
     * Главная страница модуля заказов. Отображает пагинированный список
     * заказов с различными фильтрами: по статусу, сервису, режиму обработки
     * и поисковому запросу.
     *
     * Поддерживаемые GET параметры:
     * - status: slug статуса для фильтрации (например, 'pending', 'completed')
     * - OrdersSearch[service_id]: ID сервиса для фильтрации
     * - OrdersSearch[mode]: режим обработки заказа
     * - OrdersSearch[search]: поисковый запрос
     * - OrdersSearch[search_type]: тип поиска
     *
     * @return string HTML содержимое страницы
     * @throws NotFoundHttpException Если указан несуществующий статус
     */
    public function actionIndex(): string
    {
        $searchModel = new OrdersSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Экспорт заказов в CSV формат
     *
     * Экспортирует отфильтрованные заказы в CSV файл с учетом всех
     * примененных фильтров из текущего запроса. Использует сервис
     * OrderExportService для генерации CSV данных.
     *
     * Применяются те же фильтры, что и на странице списка заказов:
     * - Фильтр по статусу
     * - Фильтр по сервису
     * - Фильтр по режиму обработки
     * - Поисковые фильтры
     *
     * @throws BadRequestHttpException Если произошла ошибка при генерации CSV
     */
    public function actionExport()
    {
        return OrderExportService::toCsv();
    }
}
