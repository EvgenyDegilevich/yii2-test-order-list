<?php

namespace app\modules\orders\controllers\actions;

use app\modules\orders\models\OrdersSearch;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Экшен для отображения списка всех заказов с возможностью фильтрации
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
 */
class IndexAction extends Action
{
    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function run(): string
    {
        $searchModel = new OrdersSearch();
        $dataProvider = $searchModel->search($this->controller->request->queryParams);

        return $this->controller->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}