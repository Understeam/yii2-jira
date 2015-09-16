<?php

namespace understeam\jira;

use yii\base\Model;

/**
 * User representation model
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class User extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $email;

}