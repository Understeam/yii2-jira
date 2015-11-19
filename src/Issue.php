<?php

namespace understeam\jira;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Issue representation model
 *
 * @property Project $project
 * @property string $key
 *
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
    protected $_key;

    /**
     * @var Project
     */
    protected $_project;

    /**
     * @var User
     */
    public $reporter;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $status;

    /**
     * @var IssueType
     */
    public $issueType;

    /**
     * @var array
     */
    public $components;

    /**
     * @var int
     */
    public $created;

    /** @var array */
    public $customFields = [];

    public static function create(Project $project, IssueType $issueType)
    {
        $issue = new self([
            'issueType' => $issueType,
        ]);
        $issue->_project = $project;

        return $issue;
    }

    /**
     * @param Project $project
     * @param array $data
     * @return Issue[]
     */
    public static function populateAll(Project $project, $data)
    {
        if (empty($data)) {
            return [];
        }
        $issues = [];
        foreach ($data as $issueData) {
            $issues[] = self::populate($project, $issueData);
        }

        return $issues;
    }

    /**
     * @param Project $project
     * @param array $data
     * @return Issue
     */
    public static function populate(Project $project, $data)
    {
        if (!is_array($data) || !isset($data['id'])) {
            return null;
        }
	    $issue = new self;
        $issue->_project = $project;
        $issue->id = (int)$data['id'];
        $issue->_key = $data['key'];
	    $issue->summary = $data['fields']['summary'];
	    $issue->status = Status::get($data['fields']['status']);
	    $issue->description = $data['fields']['description'];
	    $issue->issueType = $project->getIssueType($data['fields']['issuetype']['name']);
	    $issue->components = ArrayHelper::index($data['fields']['components'], 'name');
	    $issue->created = strtotime($data['fields']['created']);
	    $issue->customFields = [];

	    foreach ($issue->issueType->getCustomFieldsMap() as $name => $id) {
            if (isset($data['fields']['customfield_' . $id])) {
                $issue->customFields[$name] = $data['fields']['customfield_' . $id];
            }
        }

        return $issue;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    public function save()
    {
        if (!$this->key) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    public function refresh()
    {
        if ($this->key) {
            $this->setAttributes($this->project->getIssue($this->key)->attributes, false);
        }

        return false;
    }

    protected function insert()
    {
        $result = $this->project->client->post('issue', $this->serialize());
        if (!empty($result['errors'])) {
            $this->addErrors($result['errors']);

            return false;
        }
        $this->refresh();

        return true;
    }

    protected function update()
    {
        $result = $this->project->client->put('issue/' . $this->key, $this->serialize());
        if (isset($result['errors'])) {
            $this->addErrors($result['errors']);

            return false;
        }
        $this->refresh();

        return true;
    }

    public function serialize()
    {
        $fields = [
            'project' => [
                'id' => $this->project->id,
            ],
            'issuetype' => $this->issueType,
            'components' => is_array($this->components) ? array_values($this->components) : [],
        ];
        if ($this->description) {
            $fields['description'] = $this->description;
        }
        if ($this->summary) {
            $fields['summary'] = $this->summary;
        }
        foreach ($this->issueType->getCustomFieldsMap() as $name => $id) {
            if(isset($this->customFields[$name])) {
                $fields['customfield_' . $id] = $this->customFields[$name];
            }
        }
        return [
            'fields' => $fields,
        ];
    }
}