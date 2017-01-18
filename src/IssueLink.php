<?php

namespace understeam\jira;

use yii\base\Model;

class IssueLink extends Model
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
	 * @var IssueLinkType
	 */
	private $_type;

	/**
	 * @var Issue
	 */
	private $_inwardIssue;

	/**
	 * @var Issue
	 */
	private $_outwardIssue;

	/**
	 * @var Project
	 */
	private $_project;

	/**
	 * @param array $data
	 * @return static
	 */
	public static function populate(Project $project, $data)
	{
		if (!is_array($data) || !isset($data['id'])) {
			return null;
		}
		
		$status = new self;
		$status->id = $data['id'];
		$status->self = $data['self'];
		$status->_type = $data['type'];
		$status->_inwardIssue = $data['inwardIssue'];
		$status->_outwardIssue = $data['outwardIssue '];
		$status->_project = $project;

		return $status;
	}

	public static function populateAll(Project $project, $data)
	{
		$statuses = [];
		foreach ($data as $row) {
			$statuses[$row['id']] = self::populate($project, $row);
		}

		return $statuses;
	}

	/**
	 * @return Issue
	 */
	public function getInwardIssue()
	{
		if (!($this->_inwardIssue instanceof Issue)) {
			$this->_inwardIssue = Issue::populate($this->_project, $this->_inwardIssue);
		}

		return $this->_inwardIssue;
	}

	/**
	 * @return Issue
	 */
	public function getOutwardIssue()
	{
		if (!($this->_outwardIssue instanceof Issue)) {
			$this->_outwardIssue = Issue::populate($this->_project, $this->_outwardIssue);
		}

		return $this->_outwardIssue;
	}
}