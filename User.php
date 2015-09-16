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

    /**
     * @param array $data
     * @return User
     */
    public static function populateOne($data)
    {
        if (!is_array($data) || !isset($data['name'])) {
            return null;
        }
        $user = new self;
        $user->name = $data['name'];
        $user->displayName = $data['displayName'];
        $user->email = $data['emailAddress'];
        return $user;
    }
}