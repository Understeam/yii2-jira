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
 * IssueType representation class
 *
 * @author Anatoly Rugalev
 * @link https://github.com/AnatolyRugalev
 */
class StatusCategory extends Model
{
	private static $statusCategories = array();

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

	/**
	 * @var string
	 */
	public $colorName;

	/**
	 * Rest url to the status
	 * @var string
	 */
	public $self;

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
		if (array_key_exists($id, self::$statusCategories)) {
			$statusCategory = self::$statusCategories[$id];
		}
		else {
			$statusCategory = self::populate($data);
			self::$statusCategories[$statusCategory->id] = $statusCategory;
		}

		return $statusCategory;
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
        $statusCategory = new self;
        $statusCategory->id = $data['id'];
	    $statusCategory->key = $data['key'];
	    $statusCategory->name = $data['name'];
	    $statusCategory->colorName = $data['colorName'];
	    $statusCategory->self = $data['self'];

        return $statusCategory;
    }

    public static function populateAll($data)
    {
        $issueTypes = [];
        foreach ($data as $row) {
            $issueTypes[$row['key']] = self::populate($row);
        }

        return $issueTypes;
    }

}
