<?php

namespace app\helpers;

use Yii;
use app\enums\OrderStatus;
use app\enums\OrderMode;
use yii\helpers\Url;

/**
 * Вспомогательный класс для работы с заказами
 * 
 * Предоставляет методы для форматирования данных заказов,
 * создания URL для фильтров и обработки логики фильтрации.
 * 
 * @package app\helpers
 */
class OrderHelper
{
    /**
     * Форматировать данные заказа для отображения в GridView
     * 
     * Преобразует сырые данные заказа из базы данных в структуру,
     * подготовленную для отображения в интерфейсе пользователя.
     * Включает форматирование дат, получение локализованных меток
     * и подготовку составных полей.
     * 
     * @param array $orderData Массив данных заказа из базы данных
     * @return array Отформатированные данные для отображения
     */
    public static function formatForDisplay(array $orderData): array
    {
        $status = OrderStatus::tryFrom((int)$orderData['status']);
        $mode = OrderMode::tryFrom((int)$orderData['mode']);
        
        return [
            'id' => (int)$orderData['id'],
            'user_id' => (int)$orderData['user_id'],
            'link' => $orderData['link'],
            'quantity' => (int)$orderData['quantity'],
            'service_id' => (int)$orderData['service_id'],
            'status' => (int)$orderData['status'],
            'created_at' => (int)$orderData['created_at'],
            'mode' => (int)$orderData['mode'],
            'user_full_name' => trim(($orderData['first_name'] ?? '') . ' ' . ($orderData['last_name'] ?? '')),
            'service_name' => $orderData['service_name'] ?? '',
            'status_label' => $status?->getLabel() ?? '',
            'mode_label' => $mode?->getLabel() ?? '',
            'formatted_date' => $orderData['created_at'] ? Yii::$app->formatter->asDate($orderData['created_at'], 'Y-m-d') : '',
            'formatted_time' => $orderData['created_at'] ? Yii::$app->formatter->asTime($orderData['created_at'], 'H:i:s') : '',
        ];
    }

    /**
     * Форматировать данные заказа для экспорта в CSV
     * 
     * Преобразует данные заказа в плоский массив значений,
     * подходящий для записи в CSV файл. Порядок значений
     * соответствует заголовкам из метода getCsvHeaders().
     * 
     * @param array $orderData Массив данных заказа из базы данных
     * @return array Массив значений для CSV строки
     */
    public static function formatForCsv(array $orderData): array
    {
        $status = OrderStatus::tryFrom((int)$orderData['status']);
        $mode = OrderMode::tryFrom((int)$orderData['mode']);
        
        return [
            $orderData['id'],
            trim(($orderData['first_name'] ?? '') . ' ' . ($orderData['last_name'] ?? '')),
            $orderData['link'],
            $orderData['quantity'],
            $orderData['service_name'] ?? '',
            $status?->getLabel() ?? '',
            $mode?->getLabel() ?? '',
            $orderData['created_at'] ? Yii::$app->formatter->asDatetime($orderData['created_at'], 'Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Получить заголовки столбцов для CSV экспорта
     * 
     * Возвращает локализованные названия столбцов для CSV файла.
     * Порядок заголовков соответствует порядку значений в formatForCsv().
     * 
     * @return string[] Массив локализованных заголовков столбцов
     */
    public static function getCsvHeaders(): array
    {
        return [
            Yii::t('orders', 'ID'),
            Yii::t('orders', 'User'),
            Yii::t('orders', 'Link'),  
            Yii::t('orders', 'Quantity'),
            Yii::t('orders', 'Service'),
            Yii::t('orders', 'Status'),
            Yii::t('orders', 'Mode'),
            Yii::t('orders', 'Created At'),
        ];
    }

    /**
     * Получить доступные типы поиска
     * 
     * Возвращает ассоциативный массив типов поиска с их
     * локализованными названиями для использования в интерфейсе.
     * 
     * @return array<int, string> Массив типов поиска [ID => название]
     */
    public static function getSearchTypes(): array
    {
        return [
            1 => Yii::t('orders', 'Order ID'),
            2 => Yii::t('orders', 'Link'),
            3 => Yii::t('orders', 'Username'),
        ];
    }

    /**
     * Создать URL для фильтра по сервису
     * 
     * Генерирует URL с учетом текущих параметров и нового фильтра по сервису.
     * При изменении фильтра сбрасывается пагинация (cursor/direction).
     * Если serviceId равен null, фильтр по сервису удаляется.
     * 
     * @param int|null $serviceId ID сервиса для фильтрации или null для сброса
     * @param array $currentParams Текущие GET параметры запроса
     * @return string Сгенерированный URL
     */
    public static function createServiceFilterUrl(?int $serviceId, array $currentParams): string
    {
        $params = $currentParams;

        if ($serviceId === null) {
            unset($params['service_id']);
        } else {
            $params['service_id'] = $serviceId;
        }

        unset($params['cursor'], $params['direction'], $params['page']);

        $route = ['/orders'];
        if (!empty($params['status'])) {
            $statusEnum = OrderStatus::tryFrom($params['status']);
            if ($statusEnum) {
                $route = ['/orders/' . $statusEnum->getSlug()];
                unset($params['status']);
            }
        }

        return Url::to(array_merge($route, $params));
    }

    /**
     * Создать URL для фильтра по режиму заказа
     * 
     * Генерирует URL с учетом текущих параметров и нового фильтра по режиму.
     * При изменении фильтра сбрасывается пагинация (cursor/direction).
     * Если mode равен null, фильтр по режиму удаляется.
     * 
     * @param int|null $mode Режим заказа для фильтрации или null для сброса
     * @param array $currentParams Текущие GET параметры запроса
     * @return string Сгенерированный URL
     */
    public static function createModeFilterUrl(?int $mode, array $currentParams): string
    {
        $params = $currentParams;

        if ($mode === null) {
            unset($params['mode']);
        } else {
            $params['mode'] = $mode;
        }

        unset($params['cursor'], $params['direction'], $params['page']);

        $route = ['/orders'];
        if (!empty($params['status'])) {
            $statusEnum = OrderStatus::tryFrom($params['status']);
            if ($statusEnum) {
                $route = ['/orders/' . $statusEnum->getSlug()];
                unset($params['status']);
            }
        }

        return Url::to(array_merge($route, $params));
    }

    /**
     * Обработать логику фильтров и очистить зависимые параметры
     * 
     * Выполняет следующую логику:
     * - При активном поиске очищает все фильтры кроме статуса
     * - При изменении статуса очищает зависимые фильтры (mode, service_id)
     * - Сохраняет текущий статус в сессии для отслеживания изменений
     * 
     * @param array $searchParams Параметры поиска и фильтрации (передается по ссылке)
     * @return void
     */
    public static function handleFilter(array &$searchParams): void
    {
        // Если есть поиск, очищаем все фильтры кроме статуса
        if (!empty($searchParams['search'])) {
            $searchParams = [
                'search' => $searchParams['search'],
                'status' => $searchParams['status'] ?? null,
                'search_type' => $searchParams['search_type'] ?? 1,
            ];
        }

        // Если изменился статус, очищаем зависимые фильтры
        $currentStatus = $searchParams['status'] ?? null;
        $previousStatus = Yii::$app->session->get('orders.previous_status');

        if ($currentStatus !== $previousStatus) {
            unset($searchParams['mode']);
            unset($searchParams['service_id']);
            Yii::$app->session->set('orders.previous_status', $currentStatus);
        }
    }
}
