<?php

namespace app\modules\orders\validators;

use Yii;
use yii\base\Model;
use yii\validators\Validator;
use app\enums\OrderSearchType;

/**
 * Валидатор для поисковых запросов заказов
 *
 * @package app\modules\orders\validators
 */
class SearchValidator extends Validator
{
    public string $searchTypeAttribute = 'search_type';

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute): void
    {
        $searchValue = $model->$attribute;
        $searchType = $model->{$this->searchTypeAttribute};

        if (empty($searchValue)) {
            return;
        }

        if (!OrderSearchType::tryFrom($searchType)) {
            $this->addError($model, $this->searchTypeAttribute, Yii::t('app', 'validator.search_type.invalid'));
        }

        match ($searchType) {
            OrderSearchType::ORDER_ID->value => $this->validateOrderId($model, $attribute, $searchValue),
            OrderSearchType::LINK->value => $this->validateLink($model, $attribute, $searchValue),
            OrderSearchType::USERNAME->value => $this->validateUsername($model, $attribute, $searchValue),
            default => null,
        };
    }

    /**
     * Валидация поиска по ID заказа
     *
     * @param Model $model
     * @param string $attribute
     * @param string $value
     */
    private function validateOrderId(Model $model, string $attribute, mixed $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($model, $attribute, Yii::t('app', 'validator.order_id.not_numeric'));
            return;
        }

        if ((int)$value < 1) {
            $this->addError($model, $attribute, Yii::t('app', 'validator.order_id.positive'));
        }
    }

    /**
     * Валидация поиска по ссылке
     *
     * @param mixed $model
     * @param string $attribute
     * @param string $value
     */
    private function validateLink(Model $model, string $attribute, string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($model, $attribute, Yii::t('app', 'validator.link.invalid'));
        }
    }

    /**
     * Валидация поиска по имени пользователя
     *
     * @param mixed $model
     * @param string $attribute
     * @param string $value
     */
    private function validateUsername(Model $model, string $attribute, string $value): void
    {
        $length = mb_strlen($value);

        match (true) {
            $length < 2 => $this->addError($model, $attribute, Yii::t('app', 'validator.username.too_short')),
            $length > 50 => $this->addError($model, $attribute, Yii::t('app', 'validator.username.too_long')),
            default => null,
        };
    }
}