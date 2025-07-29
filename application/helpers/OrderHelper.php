<?php

namespace app\helpers;

use Yii;
use app\enums\OrderStatus;
use app\enums\OrderMode;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Вспомогательный класс для работы с заказами
 *
 * Предоставляет статические методы для форматирования данных заказов,
 * создания URL-адресов для фильтрации, экспорта в CSV и построения
 * навигационных элементов интерфейса.
 *
 * Основные возможности:
 * - Экспорт заказов в CSV формат
 * - Генерация URL для фильтров
 * - Создание навигационных элементов
 * - Форматирование данных для отображения
 *
 * @package app\helpers
 */
class OrderHelper
{
    /**
     * Форматировать данные заказа для экспорта в CSV
     *
     * Преобразует данные заказа в плоский массив значений,
     * подходящий для записи в CSV файл. Порядок значений
     * соответствует заголовкам из метода getCsvHeaders().
     *
     * @param array $orderData Массив данных заказа из базы данных
     * @return array Массив значений для CSV строки
     * @throws InvalidConfigException
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
            Yii::t('orders', 'table.header.column.id'),
            Yii::t('orders', 'table.header.column.user'),
            Yii::t('orders', 'table.header.column.link'),
            Yii::t('orders', 'table.header.column.quantity'),
            Yii::t('orders', 'table.header.column.service'),
            Yii::t('orders', 'table.header.column.status'),
            Yii::t('orders', 'table.header.column.mode'),
            Yii::t('orders', 'table.header.column.created'),
        ];
    }

    /**
     * Создать URL для фильтрации заказов
     *
     * Генерирует URL-адрес с обновленными параметрами фильтрации,
     * сохраняя существующие параметры и корректно обрабатывая
     * статусные маршруты (например, /orders/pending).
     *
     * Логика работы:
     * - Если filterValue = null, удаляет фильтр из параметров
     * - Если filterValue задан, устанавливает новое значение фильтра
     * - Сохраняет статусный маршрут, если он присутствует в URL
     * - Объединяет с существующими параметрами поиска
     *
     * @param string $filterType Тип фильтра (например, 'mode', 'service_id')
     * @param int|null $filterValue Значение фильтра или null для сброса
     * @return string Сгенерированный URL-адрес
     */
    public static function createFilterUrl(string $filterType, ?int $filterValue = null): string
    {
        $params = Yii::$app->request->get();

        if ($filterValue === null) {
            unset($params[$filterType]);
        } else {
            $params[$filterType] = $filterValue;
        }

        $route = ['/orders'];
        $status = Yii::$app->request->get('status');

        if (!empty($status)) {
            $statusEnum = OrderStatus::fromSlug($status);
            if ($statusEnum) {
                $route = ['/orders/' . $statusEnum->getSlug()];
                unset($params['status']);
            }
        }

        return Url::to(array_merge($route, $params));
    }

    /**
     * Получить навигационные элементы для отображения
     * 
     * Формирует массив навигационных элементов для вкладок.
     * 
     * @param int|null $currentStatus Текущий статус заказа (если задан)
     * @return array Массив навигационных элементов
     */
    public static function getNavItems(?int $currentStatus): array
    {
        $navItems[] = [
            'label' => Yii::t('orders', 'filter.all_orders'),
            'url' => ['/orders'],
            'active' => !isset($currentStatus)
        ];

        foreach (OrderStatus::cases() as $statusEnum) {
            $statusSlug = $statusEnum->getSlug();

            $navItems[] = [
                'label' => $statusEnum->getLabel(),
                'url' => ['/orders/' . $statusSlug],
                'active' => $currentStatus === $statusEnum->value
            ];
        }

        return $navItems;
    }

    /**
     * Форматировать данные заказа для отображения в интерфейсе
     *
     * Преобразует сырые данные заказа из базы данных в структурированный
     * массив с форматированными значениями, готовыми для отображения
     * пользователю. Включает локализованные статусы, режимы и форматированные даты.
     *
     * @param array $orderData Массив данных заказа из базы данных
     * @return array Ассоциативный массив с форматированными данными для отображения
     * @throws InvalidConfigException
     */
    public static function formatForDisplay(array $orderData): array
    {
        $status = OrderStatus::tryFrom((int)$orderData['status']);
        $mode = OrderMode::tryFrom((int)$orderData['mode']);

        return [
            'id' => (int)$orderData['id'],
            'link' => $orderData['link'],
            'quantity' => (int)$orderData['quantity'],
            'service_id' => (int)$orderData['service_id'],
            'user_full_name' => trim(($orderData['first_name'] ?? '') . ' ' . ($orderData['last_name'] ?? '')),
            'service_name' => $orderData['service_name'] ?? '',
            'status_label' => $status?->getLabel() ?? '',
            'mode_label' => $mode?->getLabel() ?? '',
            'formatted_date' => $orderData['created_at'] ? Yii::$app->formatter->asDate($orderData['created_at'], 'Y-m-d') : '',
            'formatted_time' => $orderData['created_at'] ? Yii::$app->formatter->asTime($orderData['created_at'], 'H:i:s') : '',
        ];
    }

    /**
     * Форматировать массив заказов для отображения в интерфейсе
     *
     * Применяет форматирование к массиву заказов, преобразуя каждый элемент
     * с помощью метода formatForDisplay(). Удобно для массовой обработки
     * результатов поиска или списков заказов.
     *
     * @param array $data Массив данных заказов из базы данных
     * @return array Массив форматированных данных заказов
     * @throws InvalidConfigException
     */
    public static function formatResultsForDisplay(array $data): array
    {
        $formattedResults = [];

        foreach ($data as $item) {
            $formattedResults[] = OrderHelper::formatForDisplay($item);
        }

        return $formattedResults;
    }
}
