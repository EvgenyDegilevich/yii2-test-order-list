<?php

namespace app\modules\orders\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use app\enums\OrderStatus;
use app\services\OrderService;
use app\modules\orders\components\CursorPagination;
use app\helpers\OrderHelper;
use app\services\OrderExportService;

/**
 * Контроллер для управления заказами
 * 
 * Обрабатывает основные операции с заказами:
 * - Просмотр списка заказов с фильтрацией и пагинацией
 * - Экспорт данных в различных форматах
 * 
 * Поддерживает два режима пагинации:
 * - Cursor-based для эффективной навигации по большим данным
 * - Offset-based для перехода на конкретные страницы
 * 
 * @package app\modules\orders\controllers
 * @author Ваше имя
 * @since 1.0.0
 */
class DefaultController extends Controller
{
    /**
     * Макет для отображения страниц модуля заказов
     */
    public $layout = 'orders';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'export' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Отобразить список заказов с фильтрацией и пагинацией
     * 
     * Основное действие модуля для просмотра заказов. Поддерживает:
     * - Фильтрацию по статусу через URL (например: /orders/pending)
     * - Поиск по различным критериям (ID, ссылка, имя пользователя)
     * - Гибридную пагинацию (cursor-based и offset-based)
     * 
     * @param string|null $status Slug статуса для фильтрации (pending, completed, etc.)
     * @param int|null $cursor ID записи для cursor-based пагинации
     * @param string $direction Направление пагинации: 'next' или 'prev'
     * @param int|null $page Номер страницы для offset-based пагинации
     * @return string HTML содержимое страницы
     * 
     * @throws NotFoundHttpException Если указан несуществующий статус
     * @throws BadRequestHttpException Если переданы некорректные параметры
     */
    public function actionIndex(
        ?string $status = null,
        ?int $cursor = null,
        string $direction = 'next',
        ?int $page = null
    ): string {
        try {
            // Получаем и обрабатываем параметры поиска и фильтрации
            $searchParams = $this->prepareSearchParams($status);
            
            // Настраиваем компонент пагинации
            $cursorPagination = $this->createPaginationComponent($cursor, $direction, $page);
            
            // Получаем данные через сервисный слой
            $orderService = new OrderService();
            $models = $orderService->search($searchParams, $cursorPagination);
            $filterData = $orderService->getFilterData($searchParams);

            return $this->render('index', [
                'models' => $models,
                'currentStatus' => $status,
                'filterData' => $filterData,
                'cursorPagination' => $cursorPagination,
                'searchParams' => $searchParams,
            ]);
        } catch (NotFoundHttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Yii::error(
                'Ошибка при отображении списка заказов: ' . $e->getMessage(),
                __METHOD__
            );
            throw new BadRequestHttpException('Не удалось загрузить список заказов');
        }
    }

    /**
     * Подготовить параметры поиска и фильтрации
     * 
     * Обрабатывает параметры из URL и GET запроса:
     * - Преобразует slug статуса в числовое значение
     * - Валидирует статус и выбрасывает 404 при ошибке
     * - Применяет логику очистки зависимых фильтров
     * 
     * @param string|null $status Slug статуса из URL
     * @return array Обработанные параметры поиска
     * 
     * @throws NotFoundHttpException Если статус не найден
     */
    private function prepareSearchParams(?string $status): array
    {
        $searchParams = Yii::$app->request->queryParams;

        // Обрабатываем статус из URL slug
        if ($status !== null) {
            $statusEnum = OrderStatus::fromSlug($status);
            if ($statusEnum === null) {
                throw new NotFoundHttpException('Статус "' . $status . '" не найден.');
            }
            $searchParams['status'] = $statusEnum->value;
        }

        // Применяем логику обработки фильтров
        OrderHelper::handleFilter($searchParams);

        return $searchParams;
    }

    /**
     * Создать и настроить компонент пагинации
     * 
     * Инициализирует CursorPagination с переданными параметрами
     * и валидирует корректность параметров пагинации.
     * 
     * @param int|null $cursor ID для cursor-based пагинации
     * @param string $direction Направление пагинации
     * @param int|null $page Номер страницы для offset-based пагинации
     * @return CursorPagination Настроенный компонент пагинации
     * 
     * @throws BadRequestHttpException Если параметры пагинации некорректны
     */
    private function createPaginationComponent(?int $cursor, string $direction, ?int $page): CursorPagination
    {
        // Валидация направления пагинации
        if (!in_array($direction, ['next', 'prev'], true)) {
            throw new BadRequestHttpException('Некорректное направление пагинации');
        }

        // Валидация номера страницы
        if ($page !== null && $page < 1) {
            throw new BadRequestHttpException('Номер страницы должен быть больше 0');
        }

        // Валидация курсора
        if ($cursor !== null && $cursor < 1) {
            throw new BadRequestHttpException('Некорректное значение курсора');
        }

        return new CursorPagination([
            'pageSize' => 100,
            'cursor' => $cursor,
            'direction' => $direction,
            'page' => $page,
        ]);
    }

    /**
     * Экспортировать заказы в CSV формат
     * 
     * Выполняет потоковый экспорт отфильтрованных заказов в CSV файл.
     * Использует те же фильтры, что и на странице списка заказов.
     * Файл генерируется на лету и отправляется в браузер для скачивания.
     * 
     * @return string Содержимое CSV файла
     * 
     * @throws BadRequestHttpException Если произошла ошибка при экспорте
     */
    public function actionExport(): string
    {
        try {
            return OrderExportService::toCsv();
        } catch (\Throwable $e) {
            Yii::error('Ошибка при экспорте заказов: ' . $e->getMessage(), __METHOD__);
            throw new BadRequestHttpException('Не удалось выполнить экспорт данных');
        }
    }
}
