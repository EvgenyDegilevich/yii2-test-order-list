<?php

namespace app\modules\orders\widgets;

use yii\base\Widget;

/**
 * Виджет выпадающего фильтра
 *
 * Универсальный виджет для создания выпадающих списков фильтрации.
 * Поддерживает различные типы фильтров (по сервису, режиму обработки)
 * с отображением количества элементов и выделением выбранного значения.
 *
 * @package app\modules\orders\widgets
 */
class DropdownFilterWidget extends Widget
{
    /**
     * Тип фильтра по сервису
     */
    const TYPE_SERVICE = 'service_id';

    /**
     * Тип фильтра по режиму обработки
     */
    const TYPE_MODE = 'mode';

    /**
     * Тип фильтра
     *
     * Определяет, какой тип фильтра отображается.
     * Используется для генерации корректных URL и CSS классов.
     *
     * @var string
     */
    public string $type;

    /**
     * Элементы для отображения в выпадающем списке
     *
     * Ассоциативный массив, где ключи - это значения фильтра,
     * а значения - массивы с информацией о названии и количестве.
     *
     * @var array
     */
    public array $items = [];

    /**
     * Выбранное значение фильтра
     *
     * ID текущего выбранного элемента фильтра или null,
     * если фильтр не применен.
     *
     * @var int|null
     */
    public ?int $selectedValue = null;

    /**
     * Общее количество элементов
     *
     * Общее количество заказов без применения данного фильтра.
     * Используется для отображения опции "Все".
     *
     * @var int|null
     */
    public ?int $totalCount = null;

    /**
     * Подпись фильтра
     *
     * Локализованное название фильтра для отображения пользователю.
     *
     * @var string
     */
    public string $label;

    /**
     * Выполнить рендеринг виджета
     *
     * Генерирует HTML-код выпадающего фильтра с передачей
     * всех необходимых данных в представление.
     *
     * @return string HTML-код выпадающего фильтра
     */
    public function run(): string
    {
        return $this->render('dropdownFilter', [
            'type' => $this->type,
            'items' => $this->items,
            'selectedValue' => $this->selectedValue,
            'totalCount' => $this->totalCount,
            'label' => $this->label,
        ]);
    }
}