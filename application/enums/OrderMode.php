<?php

namespace app\enums;

use Yii;

/**
 * Перечисление режимов заказа
 * 
 * Определяет возможные режимы выполнения заказа:
 * - MANUAL: ручной режим
 * - AUTO: автоматический режим
 * 
 * @package app\enums
 */
enum OrderMode: int
{
    case MANUAL = 0;
    case AUTO = 1;

    /**
     * Получить все возможные значения перечисления
     * 
     * @return int[]
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Получить локализованный лейбл для отображения
     * 
     * @return string
     */
    public function getLabel(): string
    {
        return match($this) {
            self::MANUAL => Yii::t('orders', 'Manual'),
            self::AUTO => Yii::t('orders', 'Auto'),
        };
    }
}
