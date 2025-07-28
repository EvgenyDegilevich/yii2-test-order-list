<?php

namespace app\services;

use app\modules\orders\models\OrdersSearch;
use Yii;
use app\helpers\OrderHelper;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Сервис для экспорта заказов
 *
 * Предоставляет функциональность экспорта данных заказов в различные форматы
 * с поддержкой потоковой обработки больших объемов данных.
 *
 * Особенности реализации:
 * - Потоковая обработка для минимизации использования памяти
 * - Батчевая обработка записей для оптимизации производительности
 * - Автоматическая сборка мусора при обработке больших объемов
 * - Устойчивость к прерываниям и таймаутам
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
     * Интервал для принудительной сборки мусора (количество записей)
     */
    private const GC_INTERVAL = 10000;

    /**
     * Разделитель CSV полей
     */
    private const CSV_DELIMITER = ';';

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
        $response->headers->add('Content-Encoding', 'identity');
        $response->send();
    }

    /**
     * Экспортировать заказы в формат CSV
     *
     * Основной метод для экспорта заказов. Выполняет потоковый экспорт
     * отфильтрованных заказов в CSV файл с отправкой напрямую в браузер.
     *
     * @throws BadRequestHttpException Если произошла ошибка при генерации файла
     */
    public static function toCsv(): void
    {
        try {
            $searchParams = Yii::$app->request->queryParams;
            $query = (new OrdersSearch())->getQueryForExport($searchParams);

            self::configureResponseForCsvDownload();

            self::export($query);

            Yii::$app->end();
        } catch (\Throwable $e) {
            Yii::error('Ошибка при экспорте заказов в CSV: ' . $e->getMessage(), __METHOD__);
            throw new BadRequestHttpException('Не удалось выполнить экспорт данных. Попробуйте позже.');
        }
    }

    /**
     * Выполнить потоковый экспорт данных в CSV формат
     *
     * Основная логика экспорта с батчевой обработкой данных.
     * Использует курсорную пагинацию для эффективной работы с большими наборами данных.
     *
     * @param Query $query Подготовленный запрос для экспорта
     * @return void
     * @throws \Exception Если не удалось открыть поток вывода
     */
    private static function export(Query $query): void
    {
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new \Exception('Не удалось открыть поток для записи CSV');
        }

        try {
            fputcsv($output, OrderHelper::getCsvHeaders(), self::CSV_DELIMITER);

            $processedCount = 0;
            $lastId = PHP_INT_MAX;

            while (true) {
                $batchQuery = clone $query;
                $batchQuery->andWhere(['<', 'o.id', $lastId])
                    ->limit(self::BATCH_SIZE);

                $rows = $batchQuery->all();

                if (empty($rows)) {
                    break;
                }

                $batchSize = count($rows);

                foreach ($rows as $row) {
                    $csvRow = OrderHelper::formatForCsv($row);
                    fputcsv($output, $csvRow, self::CSV_DELIMITER);
                    $lastId = min($lastId, $row['id']);
                }

                $processedCount += $batchSize;
                fflush($output);
                unset($rows);

                if ($processedCount % self::GC_INTERVAL === 0) {
                    gc_collect_cycles();
                }

                if ($batchSize < self::BATCH_SIZE) {
                    break;
                }
            }
        } finally {
            fclose($output);
        }
    }

    /*private static function export2(\yii\db\Query $query): void
    {
        $output = fopen('php://output', 'w');

        if ($output === false) {
            throw new \Exception('Не удалось открыть поток для записи CSV');
        }

        $processedCount = 0;
        try {
            fputcsv($output, OrderHelper::getCsvHeaders());

            foreach ($query->batch(self::BATCH_SIZE) as $rows) {

                $batchSize = count($rows);
                foreach ($rows as $row) {
                    $csvRow = OrderHelper::formatForCsv($row);
                    fputcsv($output, $csvRow);
                }

                $processedCount += $batchSize;
                fflush($output);
                unset($rows);

                if ($processedCount % 10000 === 0) {
                    gc_collect_cycles();
                }
            }
        } finally {
            fclose($output);
        }
    }*/
}
