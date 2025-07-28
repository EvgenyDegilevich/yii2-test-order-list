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
        $params = Yii::$app->request->get('OrdersSearch');

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

        return Url::to(array_merge($route, ['OrdersSearch' => $params]));
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
            'label' => Yii::t('orders', 'All orders'),
            'url' => ['/orders'],
            'active' => !isset($currentStatus),
            'options' => !isset($currentStatus) ? ['class' => 'active'] : [],
            'linkOptions' => ['class' => false],
        ];

        foreach (OrderStatus::cases() as $statusEnum) {
            $statusSlug = $statusEnum->getSlug();

            $navItems[] = [
                'label' => $statusEnum->getLabel(),
                'url' => ['/orders/' . $statusSlug],
                'active' => $currentStatus === $statusEnum->value,
                'options' => $currentStatus === $statusEnum->value ? ['class' => 'active'] : [],
                'linkOptions' => ['class' => false],
            ];
        }

        return $navItems;
    }
}
