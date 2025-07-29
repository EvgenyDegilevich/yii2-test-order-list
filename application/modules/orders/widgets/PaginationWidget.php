<?php

namespace app\modules\orders\widgets;

use yii\base\Widget;
use yii\data\Pagination;

class PaginationWidget extends Widget
{
    public Pagination $pagination;

    public function run()
    {
        return $this->render('pagination', [
            'pagination' => $this->pagination,
        ]);
    }
}