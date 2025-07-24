<?php

namespace app\repositories;

use yii\db\Query;

/**
 * Репозиторий для работы с заказами
 * 
 * Предоставляет методы для получения заказов с различными типами пагинации,
 * подсчета статистики, фильтрации и экспорта данных.
 * Инкапсулирует всю логику работы с базой данных для сущности заказа.
 * 
 * @package app\repositories
 */
class OrderRepository
{
    /**
     * Получить заказы с cursor-based пагинацией
     * 
     * Использует cursor-based пагинацию для эффективной навигации
     * по большим наборам данных. Поддерживает навигацию вперед и назад.
     * 
     * @param array $filters Фильтры для отбора заказов
     * @param int|null $cursor ID заказа для начала выборки (null для первой страницы)
     * @param string $direction Направление навигации: 'next' или 'prev'
     * @param int $limit Максимальное количество записей (+ 1 для определения наличия следующей страницы)
     * @return array Массив данных заказов
     */
    public function findWithCursor(
        array $filters,
        ?int $cursor = null,
        string $direction = 'next',
        int $limit = 100
    ): array {
        $query = $this->buildBaseQuery();
        $this->applyFilters($query, $filters);

        if ($cursor !== null) {
            if ($direction === 'next') {
                $query->andWhere(['<', 'o.id', $cursor]);
            } else {
                $query->andWhere(['>', 'o.id', $cursor]);
                $query->orderBy(['o.id' => SORT_ASC]); // Для обратного направления
            }
        }
        
        $results = $query->limit($limit + 1)->all();
        
        if ($direction === 'prev') {
            $results = array_reverse($results);
        }
        
        return $results;
    }

    /**
     * Получить заказы с offset-based пагинацией
     * 
     * Использует традиционную пагинацию с offset для случаев,
     * когда необходим переход на конкретную страницу.
     * 
     * @param array $filters Фильтры для отбора заказов
     * @param int $page Номер страницы (начиная с 1)
     * @param int $pageSize Количество записей на странице
     * @return array Массив данных заказов
     */
    public function findWithOffset(array $filters, int $page = 1, int $pageSize = 100): array
    {
        $query = $this->buildBaseQuery();
        $this->applyFilters($query, $filters);

        $offset = ($page - 1) * $pageSize;
        $query->offset($offset)->limit($pageSize);
        
        return $query->all();
    }

    /**
     * Получить статистику по режимам заказов
     * 
     * Подсчитывает количество заказов для каждого режима (ручной/автоматический)
     * с учетом переданных фильтров. Может исключать определенные фильтры из подсчета.
     * 
     * @param array $filters Фильтры для применения к запросу
     * @param array $excludeFilters Список фильтров, которые нужно исключить из подсчета
     * @return array<int, int> Массив [режим => количество]
     */
    public function getModeCounts(array $filters = [], array $excludeFilters = []): array
    {
        $query = (new Query())
            ->select(['o.mode', 'COUNT(o.id) as count'])
            ->from(['o' => 'orders'])
            ->groupBy('o.mode');

        if (
            !empty($filters['search']) &&
            (int)($filters['search_type'] ?? 1) === 3 &&
            !in_array('search', $excludeFilters)
        ) {
            $query->leftJoin(['u' => 'users'], 'o.user_id = u.id');
        }

        $this->applyFilters($query, $filters, $excludeFilters);
        
        $results = $query->all();
        
        $counts = [];
        foreach ($results as $result) {
            $counts[(int)$result['mode']] = (int)$result['count'];
        }
        
        return $counts;
    }

    /**
     * Получить статистику по сервисам
     * 
     * Подсчитывает количество заказов для каждого сервиса
     * с учетом переданных фильтров. Результат отсортирован по убыванию количества.
     * 
     * @param array $filters Фильтры для применения к запросу
     * @param array $excludeFilters Список фильтров, которые нужно исключить из подсчета
     * @return array Массив с данными сервисов и количеством заказов
     */
    public function getServiceCounts(array $filters = [], array $excludeFilters = []): array
    {
        $query = (new Query())
            ->select(['o.service_id', 's.name', 'COUNT(o.id) as count'])
            ->from(['o' => 'orders'])
            ->leftJoin(['s' => 'services'], 'o.service_id = s.id')
            ->groupBy(['o.service_id', 's.name'])
            ->orderBy(['count' => SORT_DESC]);
        
        if (
            !empty($filters['search']) &&
            (int)($filters['search_type'] ?? 1) === 3 &&
            !in_array('search', $excludeFilters)
        ) {
            $query->leftJoin(['u' => 'users'], 'o.user_id = u.id');
        }

        $this->applyFilters($query, $filters, $excludeFilters);
        
        return $query->all();
    }

    /**
     * Получить общее количество записей с учетом фильтров
     * 
     * Подсчитывает общее количество заказов, соответствующих фильтрам.
     * Результат кешируется в статической переменной для оптимизации.
     * 
     * @param array $filters Фильтры для отбора заказов
     * @return int Общее количество записей
     */
    public function getTotalCount(array $filters = []): int
    {
        static $totalCount = null;

        if ($totalCount !== null) {
            return $totalCount;
        }

        $query = (new Query())
            ->select('COUNT(o.id)')
            ->from(['o' => 'orders']);

        if (!empty($filters['search']) && (int)($filters['search_type'] ?? 1) === 3) {
            $query->leftJoin(['u' => 'users'], 'o.user_id = u.id');
        }

        $this->applyFilters($query, $filters);
        
        return $totalCount = (int)$query->scalar();
    }

    /**
     * Получить объект Query для экспорта
     * 
     * Возвращает настроенный объект Query для использования в экспорте.
     * 
     * @param array $filters Фильтры для применения к запросу
     * @return Query Настроенный объект запроса
     */
    public function getQueryForExport(array $filters): Query
    {
        $query = $this->buildBaseQuery();
    
        $this->applyFilters($query, $filters);
        
        return $query;
    }

    /**
     * Построить базовый запрос для выборки заказов
     * 
     * Создает базовый Query объект с необходимыми JOIN'ами
     * и выборкой всех нужных полей. Используется как основа
     * для всех других методов выборки.
     * 
     * @return Query Базовый объект запроса
     */
    private function buildBaseQuery(): Query
    {
        return (new Query())
            ->select([
                'o.id',
                'o.user_id',
                'o.link',
                'o.quantity',
                'o.service_id',
                'o.status',
                'o.created_at',
                'o.mode',
                'u.first_name',
                'u.last_name',
                's.name as service_name'
            ])
            ->from(['o' => 'orders'])
            ->leftJoin(['u' => 'users'], 'o.user_id = u.id')
            ->leftJoin(['s' => 'services'], 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC]);
    }

    /**
     * Применить фильтры к запросу
     * 
     * Добавляет условия WHERE к запросу на основе переданных фильтров.
     * Поддерживает фильтрацию по статусу, режиму, сервису и поиск
     * по различным критериям (ID заказа, ссылке, имени пользователя).
     * 
     * @param Query $query Объект запроса для модификации
     * @param array $filters Массив фильтров для применения
     * @param array $excludeFilters Список фильтров, которые нужно исключить
     * @return void
     */
    private function applyFilters(Query $query, array $filters, array $excludeFilters = []): void
    {
        // Фильтр по статусу
        if (!in_array('status', $excludeFilters) && isset($filters['status']) && $filters['status'] !== '') {
            $query->andWhere(['o.status' => (int)$filters['status']]);
        }
        
        // Фильтр по режиму
        if (!in_array('mode', $excludeFilters) && isset($filters['mode']) && $filters['mode'] !== '') {
            $query->andWhere(['o.mode' => (int)$filters['mode']]);
        }
        
        // Фильтр по сервису
        if (!in_array('service_id', $excludeFilters) && !empty($filters['service_id'])) {
            $query->andWhere(['o.service_id' => (int)$filters['service_id']]);
        }
        
        // Поиск по выбранному типу
        if (!in_array('search', $excludeFilters) && !empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $searchType = (int)($filters['search_type'] ?? 1);
            
            switch ($searchType) {
                case 1: // Order ID
                    if (is_numeric($searchTerm)) {
                        $query->andWhere(['o.id' => (int)$searchTerm]);
                    }
                    break;
                case 2: // Link
                    $query->andWhere(['like', 'o.link', $searchTerm]);
                    break;
                case 3: // Username
                    $query->andWhere([
                        'or',
                        ['like', 'CONCAT(u.first_name, " ", u.last_name)', $searchTerm],
                        ['like', 'u.first_name', $searchTerm],
                        ['like', 'u.last_name', $searchTerm],
                    ]);
                    break;
            }
        }
    }
}