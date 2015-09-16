<?php

namespace understeam\jira;

use yii\base\Model;

/**
 * Project representation model
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class Project extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $name;

}