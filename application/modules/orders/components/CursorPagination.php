<?php

namespace app\modules\orders\components;

use Yii;
use yii\base\Component;
use app\enums\OrderStatus;

/**
 * Компонент гибридной пагинации с поддержкой cursor-based и offset-based режимов
 * 
 * Предоставляет эффективную cursor-based пагинацию для быстрой навигации
 * по большим наборам данных, с возможностью переключения на offset-based
 * режим для перехода на конкретные страницы.
 * 
 * Особенности:
 * - Cursor-based: эффективная навигация prev/next без проблем с производительностью
 * - Offset-based: возможность перехода на любую страницу по номеру
 * - Автоматическое определение режима на основе параметров запроса
 * - Отслеживание номера текущей страницы для обоих режимов
 * 
 * @package app\modules\orders\components
 */
class CursorPagination extends Component
{
    /**
     * Количество записей на странице
     */
    public int $pageSize = 100;
    
    /**
     * Текущий курсор (ID последней записи с предыдущей страницы)
     * Используется только в cursor-based режиме
     */
    public ?int $cursor = null;
    
    /**
     * Направление пагинации для cursor-based режима
     * Возможные значения: 'next' (вперед), 'prev' (назад)
     */
    public string $direction = 'next';
    
    /**
     * Номер страницы для offset-based пагинации
     * Если задан, включается offset-based режим
     */
    public ?int $page = null;
    
    /**
     * Имя GET параметра для курсора в URL
     */
    public string $cursorParam = 'cursor';
    
    /**
     * Имя GET параметра для направления в URL
     */
    public string $directionParam = 'direction';
    
    /**
     * Имя GET параметра для номера страницы в URL
     */
    public string $pageParam = 'page';
    
    /**
     * ID первой записи на текущей странице
     * Устанавливается после обработки результатов
     */
    public ?int $firstId = null;
    
    /**
     * ID последней записи на текущей странице
     * Устанавливается после обработки результатов
     */
    public ?int $lastId = null;
    
    /**
     * Флаг наличия следующей страницы
     */
    public bool $hasNext = false;
    
    /**
     * Флаг наличия предыдущей страницы
     */
    public bool $hasPrev = false;
    
    /**
     * Общее количество записей в наборе данных
     */
    public int $totalCount = 0;
    
    /**
     * Номер текущей страницы (начиная с 1)
     * Отслеживается для обоих режимов пагинации
     */
    public int $currentPage = 1;

    /**
     * Инициализация компонента
     * 
     * Анализирует параметры запроса и определяет режим работы пагинации.
     * Устанавливает соответствующие свойства в зависимости от наличия
     * параметров cursor, direction и page.
     * 
     * @return void
     */
    public function init(): void
    {
        parent::init();
        
        $request = Yii::$app->request;
        
        // Получаем все параметры из запроса
        $pageParam = $request->get($this->pageParam);
        $cursorParam = $request->get($this->cursorParam);
        $directionParam = $request->get($this->directionParam, 'next');
        
        // Определяем режим работы на основе параметров
        if ($pageParam !== null && $cursorParam === null) {
            // Чистый offset режим - пользователь кликнул на номер страницы
            $this->initOffsetMode((int)$pageParam);
        } else {
            // Cursor режим (с отслеживанием номера страницы)
            $this->initCursorMode($cursorParam, $directionParam, $pageParam);
        }
    }

    /**
     * Инициализировать offset-based режим
     * 
     * @param int $pageNumber Номер запрашиваемой страницы
     * @return void
     */
    private function initOffsetMode(int $pageNumber): void
    {
        $this->page = $pageNumber;
        $this->currentPage = max(1, $pageNumber);
        $this->cursor = null;
        $this->direction = 'next';
    }

    /**
     * Инициализировать cursor-based режим
     * 
     * @param string|null $cursorParam Параметр курсора из запроса
     * @param string $directionParam Параметр направления из запроса
     * @param string|null $pageParam Параметр страницы из запроса
     * @return void
     */
    private function initCursorMode(?string $cursorParam, string $directionParam, ?string $pageParam): void
    {
        $this->cursor = $cursorParam !== null ? (int)$cursorParam : null;
        $this->direction = in_array($directionParam, ['next', 'prev'], true) ? $directionParam : 'next';
        $this->page = null; // Не используем offset в cursor режиме
        
        // Определяем номер текущей страницы
        if ($pageParam !== null) {
            // Есть page в URL - используем его
            $this->currentPage = max(1, (int)$pageParam);
        } elseif ($this->cursor === null) {
            // Первая страница (нет курсора)
            $this->currentPage = 1;
        } else {
            // Cursor без page - fallback значение
            // В идеале page всегда должен присутствовать в URL
            $this->currentPage = 1;
        }
    }
    
    // /**
    //  * {@inheritdoc}
    //  */
    // public function init()
    // {
    //     parent::init();
        
    //     $request = Yii::$app->request;
        
    //     // Получаем все параметры
    //     $pageParam = $request->get($this->pageParam);
    //     $cursorParam = $request->get($this->cursorParam);
    //     $directionParam = $request->get($this->directionParam, 'next');
        
    //     // Определяем режим работы
    //     if ($pageParam && !$cursorParam) {
    //         // Чистый offset режим - клик на номер страницы
    //         $this->page = (int)$pageParam;
    //         $this->currentPage = max(1, $this->page);
    //         $this->cursor = null;
    //         $this->direction = 'next';
    //     } else {
    //         // Cursor режим (с отслеживанием номера страницы)
    //         $this->cursor = $cursorParam ? (int)$cursorParam : null;
    //         $this->direction = $directionParam;
    //         $this->page = null; // Не используем offset
            
    //         // Определяем номер страницы
    //         if ($pageParam) {
    //             // Есть page в URL - используем его
    //             $this->currentPage = max(1, (int)$pageParam);
    //         } elseif ($this->cursor === null) {
    //             // Первая страница
    //             $this->currentPage = 1;
    //         } else {
    //             // Cursor без page - попробуем определить из направления
    //             // Это fallback, в идеале page всегда должен быть в URL
    //             $this->currentPage = 1;
    //         }
    //     }
    // }

    /**
     * Проверить, используется ли offset-based пагинация
     * 
     * @return bool true если используется offset-based режим
     */
    public function isOffsetMode(): bool
    {
        return $this->page !== null;
    }

    /**
     * Обработать результаты запроса и установить состояние пагинации
     * 
     * Анализирует полученные данные, определяет наличие следующих/предыдущих
     * страниц и устанавливает соответствующие флаги и идентификаторы.
     * 
     * @param array $results Массив данных из репозитория
     * @return array Обработанные результаты (без лишних записей)
     */
    public function processResults(array $results): array
    {
        if ($this->isOffsetMode()) {
            return $this->processOffsetResults($results);
        }
        
        return $this->processCursorResults($results);
    }

    /**
     * Обработать результаты cursor-based пагинации
     * 
     * В cursor режиме запрашивается pageSize + 1 запись для определения
     * наличия следующих данных. Лишняя запись удаляется из результата.
     * 
     * @param array $results Результаты запроса
     * @return array Обработанные результаты
     */
    private function processCursorResults(array $results): array
    {
        $hasMore = count($results) > $this->pageSize;
        if ($hasMore) {
            array_pop($results); // Убираем лишнюю запись
        }

        // Устанавливаем флаги пагинации
        if ($this->direction === 'next') {
            $this->hasNext = $hasMore;
            $this->hasPrev = $this->currentPage > 1;
        } else {
            $this->hasNext = $this->currentPage < $this->getPageCount();
            $this->hasPrev = $hasMore;
        }

        $this->setFirstAndLastIds($results);

        return $results;
    }

    /**
     * Обработать результаты offset-based пагинации
     * 
     * @param array $results Результаты запроса
     * @return array Обработанные результаты
     */
    private function processOffsetResults(array $results): array
    {
        $totalPages = $this->getPageCount();
        
        $this->hasNext = $this->currentPage < $totalPages;
        $this->hasPrev = $this->currentPage > 1;

        $this->setFirstAndLastIds($results);

        return $results;
    }

    /**
     * Установить ID первой и последней записи на странице
     * 
     * @param array $results Результаты запроса
     * @return void
     */
    private function setFirstAndLastIds(array $results): void
    {
        if (!empty($results)) {
            $this->firstId = (int)$results[0]['id'];
            $this->lastId = (int)end($results)['id'];
        }
    }

    /**
     * Получить URL для следующей страницы, всегда cursor режим
     *
     * @param array $params Дополнительные GET параметры для URL
     * @return string|null URL следующей страницы или null если её нет
     */
    public function getNextPageUrl(array $params = []): ?string
    {
        if (!$this->hasNext) {
            return null;
        }
        
        if ($this->lastId === null) return null;
        $params[$this->cursorParam] = $this->lastId;
        $params[$this->directionParam] = 'next';
        $params[$this->pageParam] = $this->currentPage + 1;
        
        return $this->buildUrl($params);
    }

    /**
     * Получить URL для предыдущей страницы, всегда cursor режим
     *
     * @param array $params Дополнительные GET параметры для URL
     * @return string|null URL предыдущей страницы или null если её нет
     */
    public function getPrevPageUrl(array $params = []): ?string
    {
        if (!$this->hasPrev) {
            return null;
        }
        
        if ($this->currentPage <= 2) {
            return $this->getFirstPageUrl($params);
        } else {
            if ($this->firstId === null) return null;
            $params[$this->cursorParam] = $this->firstId;
            $params[$this->directionParam] = 'prev';
            $params[$this->pageParam] = $this->currentPage - 1;
        }
        
        return $this->buildUrl($params);
    }

    /**
     * Получить URL для первой страницы
     * 
     * Создает URL без параметров пагинации для перехода на первую страницу.
     * 
     * @param array $params Дополнительные GET параметры для URL
     * @return string URL первой страницы
     */
    public function getFirstPageUrl(array $params = []): string
    {
        unset($params[$this->cursorParam], $params[$this->directionParam], $params[$this->pageParam]);
        return $this->buildUrl($params);
    }

    /**
     * Построить URL с параметрами
     * 
     * Если в параметрах есть статус, преобразует его в slug для URL.
     * 
     * @param array $params GET параметры для URL
     * @return string Сгенерированный URL
     */
    private function buildUrl(array $params): string
    {
        $route = [Yii::$app->controller->route];
        
        // Добавляем статус в маршрут если есть
        if (!empty($params['status'])) {
            $statusValue = $params['status'];
            $statusEnum = OrderStatus::tryFrom((int)$statusValue);
            if ($statusEnum) {
                $route = ['/orders/' . $statusEnum->getSlug()];
                unset($params['status']);
            }
        }
        
        return Yii::$app->urlManager->createUrl(array_merge($route, $params));
    }

    /**
     * Установить общее количество записей
     * 
     * Используется для расчета количества страниц и определения
     * доступности навигации в offset режиме.
     * 
     * @param int $count Общее количество записей
     * @return void
     */
    public function setTotalCount(int $count): void
    {
        $this->totalCount = max(0, $count);
    }

    /**
     * Получить текущий диапазон записей на странице
     * 
     * Возвращает позиции первой и последней записи на текущей странице
     * для отображения информации типа "Показано 1-100 из 1000".
     * 
     * @return array{0: int, 1: int} Массив [начальная_позиция, конечная_позиция]
     */
    public function getCurrentRange(): array
    {
        if ($this->totalCount === 0) {
            return [0, 0];
        }
        
        $start = ($this->currentPage - 1) * $this->pageSize + 1;
        $end = min($start + $this->pageSize - 1, $this->totalCount);
        
        return [$start, $end];
    }

    /**
     * Получить общее количество страниц
     * 
     * @return int Количество страниц (минимум 1)
     */
    public function getPageCount(): int
    {
        if ($this->totalCount <= 0) {
            return 1;
        }
        
        return (int)ceil($this->totalCount / $this->pageSize);
    }

    /**
     * Получить номер текущей страницы
     * 
     * @return int Номер текущей страницы (начиная с 1)
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
}