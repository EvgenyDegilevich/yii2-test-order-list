<?php

namespace app\modules\orders\models;

use app\models\Services;
use app\models\Users;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Orders;
use app\enums\OrderStatus;
use yii\db\ActiveQuery;
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
class OrdersSearch extends Orders
{

    /** Сценарий для поиска с дополнительной валидацией */
    const SCENARIO_SEARCH = 'search';

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
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'service_id', 'status', 'created_at', 'mode'], 'integer'],
            
            [['search'], 'string', 'max' => 255, 'on' => self::SCENARIO_SEARCH],
            [['search'], 'trim', 'on' => self::SCENARIO_SEARCH],
            [['search'], 'required', 'on' => self::SCENARIO_SEARCH],
            [['search_type'], 'integer', 'on' => self::SCENARIO_SEARCH],
            [['search_type'], 'in', 'range' => OrderSearchType::values(), 'on' => self::SCENARIO_SEARCH],
            
            [['search'], 'integer', 'min' => 1, 'on' => self::SCENARIO_SEARCH, 'when' => function($model) {
                return $model->search_type == OrderSearchType::ORDER_ID->value;
            }, 'whenClient' => "function (attribute, value) {
                return $('#orderssearch-search_type').val() == '" . OrderSearchType::ORDER_ID->value . "';
            }"],
            
            [['search'], 'url', 'on' => self::SCENARIO_SEARCH, 'when' => function($model) {
                return $model->search_type == OrderSearchType::LINK->value;
            }, 'whenClient' => "function (attribute, value) {
                return $('#orderssearch-search_type').val() == '" . OrderSearchType::LINK->value . "';
            }"],
            
            [['search'], 'string', 'min' => 2, 'max' => 50, 'on' => self::SCENARIO_SEARCH, 'when' => function($model) {
                return $model->search_type == OrderSearchType::USERNAME->value;
            }, 'whenClient' => "function (attribute, value) {
                return $('#orderssearch-search_type').val() == '" . OrderSearchType::USERNAME->value . "';
            }"],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        $scenarios = Model::scenarios();
        $scenarios[self::SCENARIO_SEARCH] = ['search', 'search_type', 'service_id', 'status', 'mode'];
        return $scenarios;
    }

    /**
     * Создать провайдер данных с примененными фильтрами поиска
     *
     * Основной метод для получения отфильтрованных данных заказов.
     * Применяет все доступные фильтры и создает пагинированный результат.
     *
     * @param array $params Параметры запроса из контроллера
     * @param string|null $formName Имя формы для загрузки данных
     * @return ActiveDataProvider Провайдер данных с примененными фильтрами
     * @throws NotFoundHttpException
     */
    public function search(array $params, ?string $formName = null): ActiveDataProvider
    {
        $query = Orders::find()
            ->with(['user', 'service'])
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ]
        ]);

        $this->checkSearchScenarioActivation($params);
        $this->load($params, $formName);
        $this->setStatus($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'service_id' => $this->service_id,
            'status' => $this->status,
            'mode' => $this->mode,
        ]);

        $this->applySearchFilters($query);

        return $dataProvider;
    }

    /**
     * Проверить и активировать сценарий поиска при необходимости
     *
     * Автоматически переключает модель в сценарий поиска, если в параметрах
     * присутствуют поисковые поля.
     *
     * @param array $params Параметры запроса
     * @return void
     */
    public function checkSearchScenarioActivation(array $params): void
    {
        if (
            !empty($params['OrdersSearch']) &&
            (
                array_key_exists('search', $params['OrdersSearch']) ||
                array_key_exists('search_type', $params['OrdersSearch'])
            )
        ) {
            $this->scenario = self::SCENARIO_SEARCH;
        }
    }

    /**
     * Получить данные для фильтра по сервисам
     *
     * Возвращает список всех доступных сервисов с количеством заказов
     * для каждого сервиса с учетом текущих фильтров. Сервисы сортируются
     * по убыванию количества заказов.
     *
     * @return array{totalCount: int, services: array<int, array{name: string, count: int}>}
     *         Массив с общим количеством и данными по сервисам
     */
    public function getServiceFilterData(): array
    {
        $totalCountQuery = Orders::find()
            ->select('id')
            ->andFilterWhere([
                'status' => $this->status,
                'mode' => $this->mode,
            ]);
        $this->applySearchFilters($totalCountQuery);
        $totalCount = $totalCountQuery->count();

        $services = Services::find()
            ->select('name')
            ->indexBy('id')
            ->column();

        $serviceCountQuery = Orders::find()
            ->select(['COUNT(*) as count'])
            ->andFilterWhere([
                'service_id' => $this->service_id,
                'status' => $this->status,
                'mode' => $this->mode,
            ])
            ->groupBy('service_id')
            ->indexBy('service_id');
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
            'services' => $result,
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
    protected function setStatus(array $params): void
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
     * Применить поисковые фильтры к запросу
     *
     * Добавляет к запросу условия поиска в зависимости от выбранного типа поиска.
     * Обрабатывает различные типы поиска: по ID, ссылке и имени пользователя.
     *
     * @param Query $query Запрос для модификации
     * @param bool $isExport Флаг экспорта (влияет на JOIN'ы)
     * @return void
     */
    private function applySearchFilters(Query $query, bool $isExport = false): void
    {
        if (empty($this->search)) {
            return;
        }

        switch ($this->search_type) {
            case OrderSearchType::ORDER_ID->value:
                $query->andWhere('id = :id', [':id' => (int)$this->search]);
                break;
            case OrderSearchType::LINK->value:
                $query->andWhere('link LIKE :link', [':link' => '%' . $this->search . '%']);
                break;
            case OrderSearchType::USERNAME->value:
                if (!$isExport) {
                    $query->leftJoin(['u' => Users::tableName()], 'u.id = orders.user_id');
                }
                $query->andWhere([
                        'OR',
                        'CONCAT(u.first_name, " ", u.last_name) LIKE :username',
                        'u.first_name LIKE :username',
                        'u.last_name LIKE :username',
                    ], [':username' => '%' . $this->search . '%']);
                break;
        }
    }

    /**
     * Получить запрос для экспорта данных
     *
     * Создает оптимизированный запрос для экспорта большого количества данных
     * в CSV формат. Использует прямой SQL запрос вместо ActiveRecord для
     * лучшей производительности.
     *
     * @param array $params Параметры фильтрации
     * @return Query Подготовленный запрос для экспорта
     * @throws NotFoundHttpException
     */
    public function getQueryForExport(array $params): Query
    {
        $this->checkSearchScenarioActivation($params);
        $this->load($params);
        $this->setStatus($params);
        $query = (new Query())
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
            ->leftJoin(['u' => 'users'], 'u.id = o.user_id')
            ->leftJoin(['s' => 'services'], 's.id = o.service_id')
            ->andFilterWhere([
                'service_id' => $this->service_id,
                'status' => $this->status,
                'mode' => $this->mode,
            ])
            ->orderBy(['o.id' => SORT_DESC]);

        $this->applySearchFilters($query, true);

        return $query;
    }
}
