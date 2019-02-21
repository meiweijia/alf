<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class WechatMenu extends Model
{
    use ModelTree, AdminBuilder;

    const TYPE_CLICK = 'click';
    const TYPE_VIEW = 'view';

    public static $typeMap = [
        self::TYPE_CLICK,
        self::TYPE_VIEW,
        null
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTitleColumn('name');
    }
}
