<?php

namespace app\modules\orders\widgets;

use yii\base\Widget;
use yii\data\Pagination;

/**
 * Виджет пагинации для списка заказов
 *
 * Отображает элементы управления пагинацией: номера страниц,
 * кнопки "Предыдущая"/"Следующая" и информацию о текущей странице.
 *
 * @package app\modules\orders\widgets
 */
class PaginationWidget extends Widget
{
    /**
     * Объект пагинации
     *
     * Содержит информацию о текущей странице, общем количестве
     * записей, размере страницы и другие параметры пагинации.
     *
     * @var Pagination
     */
    public Pagination $pagination;

    /**
     * Выполнить рендеринг виджета
     *
     * Генерирует HTML-код элементов пагинации на основе
     * переданного объекта Pagination.
     *
     * @return string HTML-код виджета пагинации
     */
    public function run(): string
    {
        return $this->render('pagination', [
            'pagination' => $this->pagination,
        ]);
    }
}
