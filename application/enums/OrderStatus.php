<?php

namespace app\enums;

use Yii;

/**
 * Перечисление статусов заказа
 *
 * Определяет возможные состояния заказа в системе и предоставляет методы
 * для работы со статусами, включая локализацию и URL представление.
 *
 * @package app\enums
 */
enum OrderStatus: int
{
    /** Заказ ожидает обработки */
    case PENDING = 0;

    /** Заказ находится в процессе выполнения */
    case IN_PROGRESS = 1;

    /** Заказ успешно выполнен */
    case COMPLETED = 2;

    /** Заказ отменен */
    case CANCELLED = 3;

    /** Заказ завершился с ошибкой */
    case FAILED = 4;

    /**
     * Получить все возможные числовые значения перечисления
     *
     * @return int[] Массив числовых значений статусов
     *
     * @example
     * ```php
     * $values = OrderStatus::values(); // [0, 1, 2, 3, 4]
     * ```
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Получить локализованный лейбл для отображения в интерфейсе
     *
     * @return string Локализованное название статуса
     *
     * @example
     * ```php
     * $label = OrderStatus::PENDING->getLabel(); // "Ожидает" (если локаль ru)
     * ```
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => Yii::t('orders', 'Pending'),
            self::IN_PROGRESS => Yii::t('orders', 'In progress'),
            self::COMPLETED => Yii::t('orders', 'Completed'),
            self::CANCELLED => Yii::t('orders', 'Cancelled'),
            self::FAILED => Yii::t('orders', 'Failed'),
        };
    }

    /**
     * Получить slug для использования в URL-адресах
     *
     * Возвращает строковое представление статуса в нижнем регистре,
     * которое безопасно использовать в URL-адресах.
     *
     * @return string URL slug статуса
     *
     * @example
     * ```php
     * $slug = OrderStatus::IN_PROGRESS->getSlug(); // "inprogress"
     * ```
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
     * Получить все доступные slug'и статусов
     *
     * @return string[] Массив всех slug'ов статусов
     *
     * @example
     * ```php
     * $slugs = OrderStatus::getSlugs(); // ['pending', 'inprogress', ...]
     * ```
     */
    public static function getSlugs(): array
    {
        return array_map(fn($case) => $case->getSlug(), self::cases());
    }

    /**
     * Создать экземпляр статуса из slug'а
     *
     * Преобразует строковый slug обратно в экземпляр перечисления.
     * Полезно для восстановления статуса из URL параметров.
     *
     * @param string $slug URL-friendly представление статуса
     * @return self|null Экземпляр перечисления или null, если slug не найден
     *
     * @example
     * ```php
     * $status = OrderStatus::fromSlug('pending'); // OrderStatus::PENDING
     * ```
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
