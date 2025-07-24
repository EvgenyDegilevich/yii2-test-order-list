<?php

namespace app\services;

use Yii;
use app\repositories\OrderRepository;
use app\helpers\OrderHelper;
use yii\web\Response;

/**
 * Сервис для экспорта заказов
 * 
 * Предоставляет функциональность экспорта данных заказов в различные форматы.
 * Использует потоковую обработку для эффективной работы с большими объемами данных,
 * минимизируя потребление памяти за счет батчевой обработки.
 * 
 * @package app\services
 */
class OrderExportService
{
    /**
     * Размер батча для обработки данных
     */
    private const BATCH_SIZE = 5000;

    /**
     * Экспортировать заказы в формат CSV
     * 
     * Выполняет потоковый экспорт заказов в CSV файл с учетом фильтров из запроса.
     * Использует батчевую обработку для минимизации потребления памяти.
     * Файл отправляется напрямую в браузер без сохранения на диске.
     * 
     * Особенности реализации:
     * - Потоковая запись для работы с большими объемами данных
     * - Автоматическое именование файла с временной меткой
     * - Обработка ошибок с логированием
     * 
     * @return string Содержимое CSV файла
     * 
     * @throws \Exception Если произошла ошибка при генерации файла
     */
    public static function toCsv(): string
    {
        try {
            $searchParams = Yii::$app->request->queryParams;
            $query = (new OrderRepository())->getQueryForExport($searchParams);

            self::configureResponseForCsvDownload();

            return self::generateCsvContent($query);
            
        } catch (\Throwable $e) {
            Yii::error('Ошибка при экспорте заказов в CSV: ' . $e->getMessage(), __METHOD__);
            throw new \Exception('Не удалось выполнить экспорт данных. Попробуйте позже.');
        }
    }

    /**
     * Настроить HTTP ответ для скачивания CSV файла
     * 
     * Устанавливает необходимые заголовки для корректного
     * скачивания файла браузером с правильной кодировкой.
     * 
     * @return void
     */
    private static function configureResponseForCsvDownload(): void
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->add(
            'Content-Disposition', 
            'attachment; filename="orders_export_' . date('Y-m-d_H-i-s') . '.csv"'
        );
    }
    
    /**
     * Сгенерировать содержимое CSV файла
     * 
     * Выполняет потоковую генерацию CSV контента с использованием
     * output buffering для минимизации потребления памяти.
     * 
     * @param \yii\db\Query $query Запрос для получения данных
     * @return string Содержимое CSV файла
     */
    private static function generateCsvContent(\yii\db\Query $query): string
    {
        ob_start();
        $output = fopen('php://output', 'w');
        
        if ($output === false) {
            throw new \Exception('Не удалось открыть поток для записи CSV');
        }

        try {
            fputcsv($output, OrderHelper::getCsvHeaders());

            foreach ($query->each(self::BATCH_SIZE) as $row) {
                $formattedRow = OrderHelper::formatForCsv($row);
                fputcsv($output, $formattedRow);
            }
            
        } finally {
            fclose($output);
        }

        return ob_get_clean();
    }
}
