<?php

namespace app\modules\orders\controllers\actions;

use app\services\OrderExportService;
use yii\base\Action;
use yii\web\BadRequestHttpException;

/**
 * Экспорт заказов в CSV формат
 *
 * Экспортирует отфильтрованные заказы в CSV файл с учетом всех
 * примененных фильтров из текущего запроса.
 */
class ExportAction extends Action
{
    /**
     * @return null
     * @throws BadRequestHttpException
     */
    public function run()
    {
        return OrderExportService::toCsv();
    }
}