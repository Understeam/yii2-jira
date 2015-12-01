<?php
/**
 * @link https://github.com/AnatolyRugalev
 * @copyright Copyright (c) AnatolyRugalev
 * @license https://tldrlegal.com/license/gnu-general-public-license-v3-(gpl-3)
 */
namespace understeam\jira;

use Yii;
use yii\base\Model;

/**
 * Status representation class
 *
 * @property StatusCategory $statusCategory
 *
 * @author Bennet Klarhölter
 * @link https://github.com/boehsermoe
 */
class Status extends Model
{

    private static $statuses = array();

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
    public $description;

    /**
     * @var string
     */
    public $iconUrl;

    /**
     * Rest url to the status
     * @var string
     */
    public $self;

    /**
     * @var string
     */
    public $colorName;

    /**
     * @var StatusCategory
     */
    protected $_statusCategory;

    public static function get($data)
    {
        if (!isset($data['id'])) {
            return null;
        }


        $id = $data['id'];


        if (array_key_exists($id, self::$statuses)) {
            $status = self::$statuses[$id];
        } else {
            $status = self::populate($data);
            self::$statuses[$status->id] = $status;
        }


        return $status;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function populate($data)
    {
        if (!is_array($data) || !isset($data['id'])) {
            return null;
        }

        $status = new self;
        $status->id = $data['id'];
        $status->name = $data['name'];
        $status->description = $data['description'];
        $status->iconUrl = $data['iconUrl'];
        $status->self = $data['self'];
        $status->colorName = $data['colorName'];
        $status->_statusCategory = StatusCategory::get($data['statusCategory']);

        return $status;
    }

    public static function populateAll($data)
    {
        $statuses = [];
        foreach ($data as $row) {
            $statuses[$row['name']] = self::populate($row);
        }

        return $statuses;
    }

    public function getStatusCategory()
    {
        return $this->_statusCategory;
    }

}
