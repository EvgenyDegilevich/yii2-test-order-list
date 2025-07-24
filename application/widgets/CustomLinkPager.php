<?php

namespace app\widgets;

use yii\widgets\LinkPager;
use yii\helpers\Html;

/**
 * Расширенный пагинатор с поддержкой cursor-based навигации
 * 
 * Виджет объединяет стандартную offset-based пагинацию Yii2 с эффективной
 * cursor-based навигацией. Предоставляет пользователям возможность:
 * - Быстрой навигации prev/next через cursor-based пагинацию
 * - Перехода на конкретные страницы через стандартные номера страниц
 * 
 * Особенности:
 * - Автоматический выбор оптимального типа URL для каждой кнопки
 * - Сохранение параметров поиска и фильтрации в URL
 * - Поддержка локализации и кастомизации
 * - Совместимость с существующими стилями LinkPager
 * 
 * @package app\widgets
 */
class CustomLinkPager extends LinkPager
{
    public $cursorPagination;
    public $searchParams;
    public $currentStatus;
    
    /**
     * Рендер кнопок пагинации с cursor-навигацией
     * 
     * Переопределяет стандартную логику рендеринга для интеграции
     * cursor-based кнопок prev/next в существующую разметку.
     * Сохраняет все стандартные возможности LinkPager и добавляет
     * эффективную cursor-навигацию.
     * 
     * @return string HTML разметка кнопок пагинации
     */
    protected function renderPageButtons()
    {
        $currentPage = $this->cursorPagination->getCurrentPage();
        $pageCount = $this->cursorPagination->getPageCount();
        
        // Рендерим стандартные кнопки страниц
        $buttons = parent::renderPageButtons();
        
        // Добавляем cursor-навигацию
        $prevButton = $this->renderCursorButton('prev', '&laquo;', $currentPage);
        $nextButton = $this->renderCursorButton('next', '&raquo;', $currentPage);
        
        // Вставляем cursor-кнопки в нужные места
        $buttons = preg_replace(
            ['/<li class="prev">.*?<\/li>/', '/<li class="next">.*?<\/li>/'],
            [$prevButton, $nextButton],
            $buttons
        );
        
        return $buttons;
    }
    
    /**
     * Рендер cursor-based кнопки навигации
     * 
     * Создает HTML разметку для кнопки prev или next с использованием
     * cursor-based URL для оптимальной производительности.
     * 
     * @param string $type Тип кнопки: 'prev' или 'next'
     * @param string $label HTML содержимое кнопки (обычно стрелка)
     * @param int $currentPage Номер текущей страницы
     * @return string HTML разметка кнопки
     */
    protected function renderCursorButton($type, $label, $currentPage)
    {
        $disabled = false;
        $url = '#';
        
        if ($type === 'prev') {
            $disabled = !$this->cursorPagination->hasPrev;
            $url = $disabled ? '#' : $this->cursorPagination->getPrevPageUrl($this->searchParams);
        } else {
            $disabled = !$this->cursorPagination->hasNext;
            $url = $disabled ? '#' : $this->cursorPagination->getNextPageUrl($this->searchParams);
        }
        
        $options = ['class' => $type . ($disabled ? ' disabled' : '')];
        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $type === 'prev' ? $currentPage - 1 : $currentPage + 1;
        
        return Html::tag('li', Html::a($label, $url, $linkOptions), $options);
    }
}