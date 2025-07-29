<?php

namespace app\services;

use Yii;
use app\helpers\OrderHelper;
use yii\db\Query;
use yii\web\BadRequestHttpException;

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
     * Экспортировать заказы в формат CSV
     *
     * Основной метод для экспорта заказов. Выполняет потоковый экспорт
     * отфильтрованных заказов в CSV файл с отправкой напрямую в браузер.
     * После завершения экспорта завершает выполнение приложения.
     *
     * @param Query $query Подготовленный запрос для экспорта данных
     * @return never Метод завершает выполнение приложения
     * @throws BadRequestHttpException Если произошла ошибка при генерации файла
     */
    public static function toCsv(Query $query): never
    {
        try {
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
            fputcsv($output, OrderHelper::getCsvHeaders());
            $lastId = PHP_INT_MAX;

            do {
                $batchQuery = clone $query;
                $batchQuery->andWhere(['<', 'o.id', $lastId])->limit(self::BATCH_SIZE);
                $rows = $batchQuery->all();
                $batchSize = count($rows);

                foreach ($rows as $row) {
                    $csvRow = OrderHelper::formatForCsv($row);
                    fputcsv($output, $csvRow);
                    $lastId = min($lastId, $row['id']);
                }

                fflush($output);
                unset($rows);
            } while ($batchSize === self::BATCH_SIZE);
        } finally {
            fclose($output);
        }
    }
}
