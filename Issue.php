<?php

namespace understeam\jira;

use yii\base\Model;

/**
 * Issue representation model
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class Issue extends Model
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
     * @var Project
     */
    public $project;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var int
     */
    public $created;

}