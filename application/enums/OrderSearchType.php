<?php

namespace app\enums;

use Yii;

/**
 * Перечисление типов поиска заказов
 *
 * Определяет различные критерии, по которым можно выполнять поиск заказов
 * в системе. Каждый тип поиска соответствует определенному полю или атрибуту заказа.
 *
 * @package app\enums
 */
enum OrderSearchType: int
{
    /** Поиск по уникальному идентификатору заказа */
    case ORDER_ID = 1;

    /** Поиск по ссылке, связанной с заказом */
    case LINK = 2;

    /** Поиск по имени пользователя, создавшего заказ */
    case USERNAME = 3;

    /**
     * Получить локализованное название типа поиска
     *
     * Возвращает переведенное на текущий язык название типа поиска,
     * которое отображается пользователю в интерфейсе.
     *
     * @return string Локализованное название типа поиска
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ORDER_ID => Yii::t('orders', 'search.type.order_id'),
            self::LINK => Yii::t('orders', 'search.type.link'),
            self::USERNAME => Yii::t('orders', 'search.type.username'),
        };
    }

    /**
     * Получить все доступные типы поиска с локализованными названиями
     *
     * Возвращает ассоциативный массив всех типов поиска, где ключи - это
     * числовые значения типов, а значения - их локализованные названия.
     * Удобно использовать для создания выпадающих списков в формах.
     *
     * @return array<int, string> Ассоциативный массив [ID => название]
     */
    public static function getSearchTypes(): array
    {
        $types = [];
        foreach (self::cases() as $case) {
            $types[$case->value] = $case->getLabel();
        }
        return $types;
    }

    /**
     * Получить все возможные числовые значения перечисления
     *
     * @return int[] Массив числовых значений типов поиска
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}