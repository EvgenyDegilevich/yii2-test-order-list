<?php

namespace app\services;

use app\repositories\OrderRepository;
use app\helpers\OrderHelper;

/**
 * Сервис для работы с заказами
 * 
 * @package app\services
 */
class OrderService
{
    /**
     * Получить данные для отображения фильтров
     * 
     * Собирает статистическую информацию для построения интерфейса фильтрации:
     * - количество заказов по режимам (исключая текущий фильтр по режиму)
     * - количество заказов по сервисам (исключая текущий фильтр по сервису)
     * - общее количество заказов с учетом всех фильтров
     * 
     * @param array $currentFilters Текущие активные фильтры
     * @return array{
     *     modeCounts: array<int, int>,
     *     serviceCounts: array,
     *     totalCount: int
     * } Данные для построения фильтров
     */
    public function getFilterData(array $currentFilters): array
    {
        $repository = new OrderRepository();

        return [
            'modeCounts' => $repository->getModeCounts($currentFilters, ['mode']),
            'serviceCounts' => $repository->getServiceCounts($currentFilters, ['service_id']),
            'totalCount' => $repository->getTotalCount($currentFilters),
        ];
    }

    /**
     * Выполнить поиск заказов с пагинацией
     * 
     * Осуществляет поиск заказов с поддержкой двух типов пагинации:
     * - Cursor-based: эффективная навигация prev/next по большим наборам данных
     * - Offset-based: переход на конкретные страницы
     * 
     * Автоматически определяет тип пагинации на основе параметров запроса
     * и форматирует результаты для отображения в интерфейсе.
     * 
     * @param array $filters Фильтры для отбора заказов
     * @param mixed $cursorPagination Объект пагинации с настройками и состоянием
     * @return array Массив отформатированных данных заказов для отображения
     * 
     * @throws \Exception Если возникла ошибка при получении данных
     */
    public function search(array $filters, $cursorPagination): array
    {
        $repository = new OrderRepository();
        // Получаем общее количество для статистики и настройки пагинации
        $totalCount = $repository->getTotalCount($filters);
        $cursorPagination->setTotalCount($totalCount);

        // Определяем тип пагинации и получаем данные
        if ($cursorPagination->isOffsetMode()) {
            $results = $repository->findWithOffset(
                $filters,
                $cursorPagination->currentPage,
                $cursorPagination->pageSize
            );
        } else {
            $results = $repository->findWithCursor(
                $filters, 
                $cursorPagination->cursor, 
                $cursorPagination->direction, 
                $cursorPagination->pageSize
            );
        }

        // Обрабатываем результаты через объект пагинации
        // (определение наличия следующей/предыдущей страницы, настройка курсоров)
        $processedResults = $cursorPagination->processResults($results);

        // Форматируем данные для отображения
        return $this->formatResultsForDisplay($processedResults);
    }

    /**
     * Форматировать результаты для отображения
     * 
     * Преобразует массив сырых данных из базы в формат,
     * подходящий для отображения в пользовательском интерфейсе.
     * 
     * @param array $results Массив сырых данных заказов
     * @return array Массив отформатированных данных
     */
    private function formatResultsForDisplay(array $results): array
    {
        $formattedResults = [];
        
        foreach ($results as $result) {
            $formattedResults[] = OrderHelper::formatForDisplay($result);
        }
        
        return $formattedResults;
    }
}
