<?php

namespace app\modules\orders\widgets;

use app\modules\orders\models\OrdersSearch;
use yii\base\Widget;

/**
 * Виджет таблицы заказов
 *
 * Отображает табличное представление списка заказов с возможностью
 * сортировки, фильтрации и отображения детальной информации.
 * Включает колонки для всех основных атрибутов заказа.
 *
 * @package app\modules\orders\widgets
 */
class OrdersTableWidget extends Widget
{
    /**
     * Массив данных заказов для отображения
     *
     * Содержит форматированные данные заказов, готовые для
     * отображения в таблице. Каждый элемент представляет один заказ.
     *
     * @var array
     */
    public array $orders;

    /**
     * Модель поиска заказов
     *
     * Используется для получения информации о текущих фильтрах
     * и параметрах поиска для корректного отображения состояния таблицы.
     *
     * @var OrdersSearch
     */
    public OrdersSearch $model;

    /**
     * Выполнить рендеринг виджета
     *
     * Генерирует HTML-код таблицы заказов с передачей данных
     * и модели в представление для рендеринга.
     *
     * @return string HTML-код таблицы заказов
     */
    public function run(): string
    {
        return $this->render('ordersTable', [
            'orders' => $this->orders,
            'model' => $this->model,
        ]);
    }
}
