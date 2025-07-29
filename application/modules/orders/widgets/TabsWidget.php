<?php

namespace app\modules\orders\widgets;

use app\helpers\OrderHelper;
use app\modules\orders\models\OrdersSearch;
use yii\base\Widget;

/**
 * Виджет навигационных вкладок для фильтрации заказов по статусам
 *
 * Отображает горизонтальное меню с вкладками для быстрой фильтрации
 * заказов по статусам. Включает вкладку "Все заказы" и отдельные
 * вкладки для каждого статуса заказа с подсветкой активной вкладки.
 *
 * @package app\modules\orders\widgets
 */
class TabsWidget extends Widget
{
    /**
     * Модель поиска заказов
     *
     * Используется для определения текущего активного статуса
     * и передачи в представление для формирования навигации.
     *
     * @var OrdersSearch
     */
    public OrdersSearch $model;

    /**
     * Выполнить рендеринг виджета
     *
     * Генерирует HTML-код навигационных вкладок с использованием
     * OrderHelper для получения списка доступных статусов.
     *
     * @return string HTML-код виджета
     */
    public function run(): string
    {
        return $this->render('tabs', [
            'navItems' => OrderHelper::getNavItems($this->model->status),
            'model' => $this->model,
        ]);
    }
}
