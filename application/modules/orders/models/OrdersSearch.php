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
 * OrdersSearch represents the model behind the search form of `app\models\Orders`.
 */
class OrdersSearch extends Orders
{
    const SCENARIO_SEARCH = 'search';

    public $search;
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
    public function scenarios()
    {
        $scenarios = Model::scenarios();
        $scenarios[self::SCENARIO_SEARCH] = ['search', 'search_type', 'service_id', 'status', 'mode'];
        return $scenarios;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
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

    public function getQueryForExport(array $params)
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
