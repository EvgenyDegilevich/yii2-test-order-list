<?php

namespace app\modules\orders\controllers\actions;

use app\modules\orders\models\OrdersSearch;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Экшен для отображения списка всех заказов с возможностью фильтрации
 *
 * Главная страница модуля заказов. Отображает пагинированный список
 * заказов с различными фильтрами: по статусу, сервису, режиму обработки
 * и поисковому запросу.
 *
 * @package app\modules\orders\controllers\actions
 */
class IndexAction extends Action
{
    /**
     * Отобразить список заказов с фильтрацией
     *
     * Загружает параметры поиска из запроса, инициализирует модель поиска,
     * выполняет фильтрацию и пагинацию данных, затем рендерит представление
     * со списком заказов.
     *
     * @return string HTML-код страницы со списком заказов
     * @throws NotFoundHttpException При ошибках валидации или отсутствии данных
     */
    public function run(): string
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new OrdersSearch();
        $searchModel->load($params, '');
        $searchModel->validate();

        [$data, $pagination] = $searchModel->search();

        return $this->controller->render('index', [
            'searchModel' => $searchModel,
            'data' => $data,
            'pagination' => $pagination,
            'queryParams' => $params,
        ]);
    }
}