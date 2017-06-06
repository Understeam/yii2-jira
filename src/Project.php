<?php

namespace understeam\jira;

use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Project representation model
 *
 * @property Client $client
 *
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

    /**
     * @var IssueType[]
     */
    public $issueTypes;

    /**
     * @var array
     */
    public $components;

    /**
     * @var string
     */
    public $projectTypeKey;

	/**
	 * @var string
	 */
    public $projectCategory;

    /** @var Client */
    protected $_client;

    /**
     * @param Client $client
     * @param array $data
     * @return Project
     */
    public static function populate(Client $client, $data)
    {
        if (!is_array($data) || !isset($data['id'])) {
            return null;
        }
        $project = new self;
        $project->_client = $client;
        $project->id = (int)$data['id'];
        $project->key = $data['key'];
        $project->name = $data['name'];
        $project->issueTypes = IssueType::populateAll($project, $data['issueTypes']);
        $project->components = ArrayHelper::index($data['components'], 'name');
        $project->projectTypeKey = $data['projectTypeKey'];
        $project->projectCategory = $data['projectCategory']['name'];

        return $project;
    }

	public static function populateAll(Client $client, $data)
	{
		$projects = [];
		foreach ($data as $row) {
			$projects[$row['id']] = self::populate($client, $row);
		}

		return $projects;
	}

    public function getMetaData($issueTypeName)
    {
        $data = $this->client->get('issue/createmeta',
            ['projectKeys' => $this->key, 'expand' => 'projects.issuetypes.fields']);
        if (isset($data['projects'][0])) {
            $data = ArrayHelper::index($data['projects'][0]['issuetypes'], 'name');
        } else {
            return [];
        }
        if (isset($data[$issueTypeName])) {
            return $data[$issueTypeName];
        } else {
            return [];
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param string|IssueType $issueType
     * @return Issue
     */
    public function createIssue($issueType)
    {
        if (is_string($issueType)) {
            $issueType = $this->getIssueType($issueType);
        }

        return Issue::create($this, $issueType);
    }

    public function search($jql)
    {
        $jql = "project = " . $this->key . " AND " . $jql;
        $result = $this->client->post('search', ['jql' => $jql]);
        if (isset($result['errorMessages'])) {
            throw new Exception("Jira search error: " . $result['errorMessages'][0]);
        }

        return $result;
    }

    public function findIssue($jql)
    {
        $result = $this->search($jql);
        if (!isset($result['total']) || $result['total'] == 0) {
            return null;
        }

        return Issue::populate($this, $result['issues'][0]);
    }

    public function findIssues($jql)
    {
        $result = $this->search($jql);
        if (!isset($result['total']) || $result['total'] == 0) {
            return [];
        }

        return Issue::populateAll($this, $result['issues']);
    }

    public function findIssueByCustomField($name, $value, $operator = "~")
    {
        $jql = "'{$name}' {$operator} '" . Client::escapeValue($value) . "'";

        return $this->findIssue($jql);
    }

    public function getIssue($key)
    {
        $data = $this->client->get('issue/' . $key);
        if (isset($data['id'])) {
            $issue = Issue::populate($this, $data);

            return $issue;
        }

        return null;
    }

    /**
     * @param $name
     * @return IssueType
     */
    public function getIssueType($name)
    {
        if (!isset($this->issueTypes[$name])) {
            throw new InvalidParamException("Issue type \"{$name}\" does not exist in project \"{$this->name}\"");
        }

        return $this->issueTypes[$name];
    }

}
