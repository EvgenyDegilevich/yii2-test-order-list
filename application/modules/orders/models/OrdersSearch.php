<?php

namespace app\modules\orders\models;

use app\helpers\OrderHelper;
use app\models\Services;
use app\modules\orders\validators\SearchValidator;
use yii\base\InvalidConfigException;
use yii\base\Model;
use app\enums\OrderStatus;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use app\enums\OrderSearchType;

/**
 * Модель поиска заказов
 *
 * Представляет модель для формы поиска и фильтрации заказов.
 * Расширяет базовую модель Orders, добавляя функциональность поиска
 * по различным критериям с валидацией и построением запросов.
 *
 * @property string|null $search Поисковый запрос
 * @property int|null $search_type Тип поиска
 *
 * @package app\modules\orders\models
 */
class OrdersSearch extends Model
{
    const PAGE_SIZE = 100;

    /**
     * Поисковый запрос пользователя
     * @var string|null
     */
    public $search;

    /**
     * Тип поиска
     * @var int|null
     */
    public $search_type;

    /**
     * Статус заказа для фильтрации
     * @var int|null
     */
    public $status;

    /**
     * Идентификатор сервиса для фильтрации
     * @var int|null
     */
    public $service_id;

    /**
     * Режим заказа для фильтрации
     * @var int|null
     */
    public $mode;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['service_id', 'status', 'mode'], 'integer'],
            
            [['search'], 'string', 'max' => 255],
            [['search'], 'trim'],
            [['search'], 'required', 'when' => function($model) {
                return !empty($model->search_type);
            }],
            [['search_type'], 'integer'],

            [['search'], SearchValidator::class],
        ];
    }

    /**
     * @inheritdoc
     * @throws NotFoundHttpException
     */
    public function load($data, $formName = null): bool
    {
        $loaded = parent::load($data, $formName);

        $this->setStatus($data);

        return $loaded;
    }

    /**
     * Получить базовый SQL запрос для поиска заказов
     *
     * Создает основной запрос с необходимыми JOIN'ами для получения
     * данных заказов вместе с информацией о пользователях и сервисах.
     * Результат сортируется по убыванию ID заказа.
     *
     * @return Query Базовый запрос для дальнейшей модификации
     */
    private function getBaseQuery(): Query
    {
        return (new Query())
            ->select([
                'o.id',
                'o.user_id',
                'o.link',
                'o.quantity',
                'o.service_id',
                'o.status',
                'o.created_at',
                'o.mode',
                'u.first_name',
                'u.last_name',
                's.name as service_name'
            ])
            ->from(['o' => 'orders'])
            ->leftJoin(['u' => 'users'], 'o.user_id = u.id')
            ->leftJoin(['s' => 'services'], 'o.service_id = s.id')
            ->orderBy(['o.id' => SORT_DESC]);
    }

    /**
     * Применить базовые фильтры к запросу
     *
     * Добавляет к запросу условия фильтрации по основным полям заказа:
     * сервису, статусу и режиму обработки.
     *
     * @param Query $query Запрос для модификации
     * @return void
     */
    private function applyBaseFilters(Query $query): void
    {
        $query->andFilterWhere([
            'o.service_id' => $this->service_id,
            'o.status' => $this->status,
            'o.mode' => $this->mode,
        ]);
    }

    /**
     * Применить поисковые фильтры к запросу
     *
     * Добавляет к запросу условия поиска в зависимости от выбранного типа поиска.
     * Обрабатывает различные типы поиска: по ID, ссылке и имени пользователя.
     *
     * @param Query $query Запрос для модификации
     * @return void
     */
    private function applySearchFilters(Query $query): void
    {
        if (empty($this->search) || $this->hasErrors()) {
            return;
        }

        switch ($this->search_type) {
            case OrderSearchType::ORDER_ID->value:
                $query->andWhere(['o.id' => (int)$this->search]);
                break;
            case OrderSearchType::LINK->value:
                $query->andWhere(['like', 'o.link', $this->search]);
                break;
            case OrderSearchType::USERNAME->value:
                $query->andWhere([
                    'OR',
                    ['like', 'CONCAT(u.first_name, " ", u.last_name)', $this->search],
                    ['like', 'u.first_name', $this->search],
                    ['like', 'u.last_name', $this->search],
                ]);
                break;
        }
    }

    /**
     * Выполнить поиск заказов с пагинацией
     *
     * Строит и выполняет запрос с примененными фильтрами, добавляет
     * пагинацию и возвращает форматированные данные для отображения.
     *
     * @return array Массив с данными и объектом пагинации
     * @throws InvalidConfigException
     */
    public function search(): array
    {
        $query = $this->getBaseQuery();
        $this->applyBaseFilters($query);
        $this->applySearchFilters($query);

        $pagination = new Pagination([
            'totalCount' => $query->count('o.id'),
            'pageSize' => self::PAGE_SIZE,
            'pageSizeParam' => false
        ]);

        $data = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [OrderHelper::formatResultsForDisplay($data), $pagination];
    }

    /**
     * Получить данные для фильтра по сервисам
     *
     * Возвращает список всех доступных сервисов с количеством заказов
     * для каждого сервиса с учетом текущих фильтров. Сервисы сортируются
     * по убыванию количества заказов.
     *
     * @return array Массив с общим количеством и данными по сервисам
     */
    public function getServiceFilterData(): array
    {
        $totalCountQuery = $this->getBaseQuery();
        $totalCountQuery->andFilterWhere([
            'o.status' => $this->status,
            'o.mode' => $this->mode,
        ]);
        $this->applySearchFilters($totalCountQuery);
        $totalCount = $totalCountQuery->count();

        $services = Services::find()
            ->select('name')
            ->indexBy('id')
            ->column();

        $serviceCountQuery = $this->getBaseQuery();
        $serviceCountQuery
            ->select(['COUNT(*) as count'])
            ->groupBy('o.service_id')
            ->indexBy('o.service_id')
            ->orderBy(['count' => SORT_DESC]);
        $this->applyBaseFilters($serviceCountQuery);
        $this->applySearchFilters($serviceCountQuery);
        $serviceCount = $serviceCountQuery->column();

        $result = [];
        foreach ($services as $id => $name) {
            $result[$id] = [
                'name' => $name,
                'count' => $serviceCount[$id] ?? 0,
            ];
        }

        uasort($result, fn($a, $b) => $b['count'] - $a['count']);

        return [
            'totalCount' => $totalCount,
            'items' => $result,
        ];
    }

    /**
     * Установить статус из URL параметра
     *
     * Преобразует строковый slug статуса из URL обратно в числовое значение
     * и устанавливает его в модель. Выбрасывает исключение для неизвестных статусов.
     *
     * @param array $params Параметры запроса
     * @return void
     * @throws NotFoundHttpException Если статус не найден
     */
    public function setStatus(array $params): void
    {
        if (!empty($params['status'])) {
            $statusEnum = OrderStatus::fromSlug($params['status']);
            if ($statusEnum === null) {
                throw new NotFoundHttpException('Статус "' . $params['status'] . '" не найден.');
            }
            $this->status = $statusEnum->value;
        }
    }

    /**
     * Получить запрос для экспорта данных
     *
     * Создает оптимизированный запрос для экспорта большого количества данных
     * в CSV формат. Использует прямой SQL запрос вместо ActiveRecord для
     * лучшей производительности.
     *
     * @return Query Подготовленный запрос для экспорта
     */
    public function getQueryForExport(): Query
    {
        $query = $this->getBaseQuery();
        $this->applyBaseFilters($query);
        $this->applySearchFilters($query);

        return $query;
    }
}
