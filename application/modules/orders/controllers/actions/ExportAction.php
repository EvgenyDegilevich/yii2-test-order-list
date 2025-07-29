<?php

namespace app\modules\orders\controllers\actions;

use app\modules\orders\models\OrdersSearch;
use app\services\OrderExportService;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Экспорт заказов в CSV формат
 *
 * Экшен для экспорта отфильтрованных заказов в CSV файл с учетом всех
 * примененных фильтров из текущего запроса. Автоматически настраивает
 * HTTP-ответ для скачивания файла с корректными заголовками.
 *
 * @package app\modules\orders\controllers\actions
 */
class ExportAction extends Action
{
    /**
     * Выполнить экспорт заказов в CSV
     *
     * Загружает параметры поиска, валидирует их, настраивает HTTP-ответ
     * для скачивания файла и запускает процесс экспорта через сервис.
     * После выполнения завершает выполнение скрипта.
     *
     * @return never Метод завершает выполнение скрипта
     * @throws BadRequestHttpException При некорректных параметрах запроса
     * @throws NotFoundHttpException При отсутствии данных для экспорта
     */
    public function run(): never
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new OrdersSearch();
        $searchModel->load($params, '');
        $searchModel->validate();

        $this->configureResponse();

        OrderExportService::toCsv($searchModel->getQueryForExport());
    }

    /**
     * Настроить HTTP ответ для скачивания CSV файла
     *
     * Устанавливает необходимые заголовки для корректного
     * скачивания файла браузером с правильной кодировкой.
     *
     * @return void
     */
    private function configureResponse(): void
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->add(
            'Content-Disposition',
            'attachment; filename="orders_export_' . date('Y-m-d_H-i-s') . '.csv"'
        );
        $response->send();
    }
}