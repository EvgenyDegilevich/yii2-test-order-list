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
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Получить локализованный лейбл для отображения в интерфейсе
     *
     * @return string Локализованное название статуса
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => Yii::t('orders', 'status.pending'),
            self::IN_PROGRESS => Yii::t('orders', 'status.in_progress'),
            self::COMPLETED => Yii::t('orders', 'status.completed'),
            self::CANCELLED => Yii::t('orders', 'status.canceled'),
            self::FAILED => Yii::t('orders', 'status.failed'),
        };
    }

    /**
     * Получить slug для использования в URL-адресах
     *
     * Возвращает строковое представление статуса в нижнем регистре,
     * которое безопасно использовать в URL-адресах.
     *
     * @return string URL slug статуса
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
     * Возвращает массив всех URL представлений статусов.
     * Полезно для валидации входящих параметров из URL.
     *
     * @return string[] Массив всех slug'ов статусов
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
