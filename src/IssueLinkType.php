<?php

namespace understeam\jira;

class IssueLinkType extends Model
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $self;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $inward;

	/**
	 * @var string
	 */
	public $outward;

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
		$status->self = $data['self'];
		$status->name = $data['name'];
		$status->inward = $data['inward'];
		$status->outward = $data['outward'];

		return $status;
	}

	public static function populateAll($data)
	{
		$statuses = [];
		foreach ($data as $row) {
			$statuses[$row['id']] = self::populate($row);
		}

		return $statuses;
	}
}