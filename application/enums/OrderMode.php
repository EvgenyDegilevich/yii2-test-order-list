<?php

namespace app\enums;

use Yii;

/**
 * Перечисление режимов заказа
 *
 * Определяет возможные режимы выполнения заказа в системе.
 * Используется для указания того, каким образом должен обрабатываться заказ.
 *
 * @package app\enums
 */
enum OrderMode: int
{
    /** Ручной режим обработки заказа */
    case MANUAL = 0;

    /** Автоматический режим обработки заказа */
    case AUTO = 1;

    /**
     * Получить все возможные числовые значения перечисления
     *
     * Возвращает массив всех числовых значений, которые могут принимать
     * экземпляры данного перечисления.
     *
     * @return int[] Массив числовых значений
     *
     * @example
     * ```php
     * $values = OrderMode::values(); // [0, 1]
     * ```
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Получить локализованный лейбл для отображения в интерфейсе
     *
     * Возвращает переведенное на текущий язык название режима заказа,
     * которое можно использовать для отображения пользователю.
     *
     * @return string Локализованное название режима
     *
     * @example
     * ```php
     * $label = OrderMode::MANUAL->getLabel(); // "Ручной" (если локаль ru)
     * ```
     */
    public function getLabel(): string
    {
        return match($this) {
            self::MANUAL => Yii::t('orders', 'Manual'),
            self::AUTO => Yii::t('orders', 'Auto'),
        };
    }
}
