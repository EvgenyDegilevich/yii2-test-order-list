<?php

namespace app\enums;

use Yii;

/**
 * Перечисление статусов заказа
 * 
 * Определяет возможные состояния заказа в системе:
 * - PENDING: заказ ожидает обработки
 * - IN_PROGRESS: заказ в процессе выполнения
 * - COMPLETED: заказ успешно выполнен
 * - CANCELLED: заказ отменен
 * - FAILED: заказ завершился с ошибкой
 * 
 * @package app\enums
 */
enum OrderStatus: int
{
    case PENDING = 0;
    case IN_PROGRESS = 1;
    case COMPLETED = 2;
    case CANCELLED = 3;
    case FAILED = 4;

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
            self::PENDING => Yii::t('orders', 'Pending'),
            self::IN_PROGRESS => Yii::t('orders', 'In Progress'),
            self::COMPLETED => Yii::t('orders', 'Completed'),
            self::CANCELLED => Yii::t('orders', 'Cancelled'),
            self::FAILED => Yii::t('orders', 'Failed'),
        };
    }

    /**
     * Получить slug для использования в URL
     * 
     * @return string Slug статуса в нижнем регистре для URL-адресов
     */
    public function getSlug(): string
    {
        return match($this) {
            self::PENDING => 'pending',
            self::IN_PROGRESS => 'inprogress',
            self::COMPLETED => 'completed',
            self::CANCELLED => 'cancelled',
            self::FAILED => 'failed',
        };
    }

    /**
     * Получить статус по slug
     * 
     * Преобразует строковый slug обратно в экземпляр перечисления.
     * 
     * @param string $slug Slug статуса
     * @return self|null Экземпляр перечисления или null, если slug не найден
     */
    public static function fromSlug(string $slug): ?self
    {
        return match($slug) {
            'pending' => self::PENDING,
            'inprogress' => self::IN_PROGRESS,
            'completed' => self::COMPLETED,
            'cancelled' => self::CANCELLED,
            'failed' => self::FAILED,
            default => null,
        };
    }
}
