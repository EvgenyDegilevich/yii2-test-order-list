<?php

namespace app\modules\orders\widgets;

use yii\base\Widget;

class DropdownFilterWidget extends Widget
{
    const TYPE_SERVICE = 'service_id';
    const TYPE_MODE = 'mode';

    public string $type;
    public array $items = [];
    public ?int $selectedValue = null;
    public ?int $totalCount = null;
    public string $label;

    public function run()
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