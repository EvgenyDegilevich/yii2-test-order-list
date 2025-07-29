<?php

namespace app\modules\orders\validators;

use Yii;
use yii\base\Model;
use yii\validators\Validator;
use app\enums\OrderSearchType;

/**
 * Валидатор для поисковых запросов заказов
 *
 * Выполняет специализированную валидацию поисковых запросов в зависимости
 * от выбранного типа поиска. Поддерживает валидацию поиска по ID заказа,
 * ссылке и имени пользователя с соответствующими правилами для каждого типа.
 *
 * @package app\modules\orders\validators
 */
class SearchValidator extends Validator
{
    /**
     * Имя атрибута, содержащего тип поиска
     * @var string
     */
    public string $searchTypeAttribute = 'search_type';

    /**
     * Валидировать поисковый атрибут
     *
     * Основной метод валидации, который определяет тип поиска и вызывает
     * соответствующий специализированный метод валидации.
     *
     * @param Model $model Модель для валидации
     * @param string $attribute Имя валидируемого атрибута
     * @return void
     */
    public function validateAttribute($model, $attribute): void
    {
        $searchValue = $model->$attribute;
        $searchType = $model->{$this->searchTypeAttribute};

        if (empty($searchValue)) {
            return;
        }

        if (!OrderSearchType::tryFrom($searchType)) {
            $this->addError($model, $this->searchTypeAttribute, Yii::t('orders', 'validator.search_type.invalid'));
        }

        match ((int)$searchType) {
            OrderSearchType::ORDER_ID->value => $this->validateOrderId($model, $attribute, $searchValue),
            OrderSearchType::LINK->value => $this->validateLink($model, $attribute, $searchValue),
            OrderSearchType::USERNAME->value => $this->validateUsername($model, $attribute, $searchValue),
            default => null,
        };
    }

    /**
     * Валидация поиска по ID заказа
     *
     * Проверяет, что значение является числовым и положительным.
     *
     * @param Model $model Модель для валидации
     * @param string $attribute Имя атрибута
     * @param mixed $value Значение для валидации
     * @return void
     */
    private function validateOrderId(Model $model, string $attribute, mixed $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($model, $attribute, Yii::t('orders', 'validator.order_id.not_numeric'));
            return;
        }

        if ((int)$value < 1) {
            $this->addError($model, $attribute, Yii::t('orders', 'validator.order_id.positive'));
        }
    }

    /**
     * Валидация поиска по ссылке
     *
     * Проверяет, что значение является корректным URL-адресом.
     *
     * @param Model $model Модель для валидации
     * @param string $attribute Имя атрибута
     * @param mixed $value Значение для валидации
     * @return void
     */
    private function validateLink(Model $model, string $attribute, string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($model, $attribute, Yii::t('orders', 'validator.link.invalid'));
        }
    }

    /**
     * Валидация поиска по имени пользователя
     *
     * Проверяет длину введенного значения (от 2 до 50 символов).
     *
     * @param Model $model Модель для валидации
     * @param string $attribute Имя атрибута
     * @param mixed $value Значение для валидации
     * @return void
     */
    private function validateUsername(Model $model, string $attribute, string $value): void
    {
        $length = mb_strlen($value);

        match (true) {
            $length < 2 => $this->addError($model, $attribute, Yii::t('orders', 'validator.username.too_short')),
            $length > 50 => $this->addError($model, $attribute, Yii::t('orders', 'validator.username.too_long')),
            default => null,
        };
    }
}