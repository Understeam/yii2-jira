<?php

namespace understeam\jira;

use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

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
     * @var array
     */
    public $issueTypes;

    /**
     * @var array
     */
    public $components;

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
        $project->issueTypes = ArrayHelper::index($data['issueTypes'], 'name');
        $project->components = ArrayHelper::index($data['components'], 'name');
        return $project;
    }

    public function getMetaData($issueTypeName)
    {
        $data = $this->client->get('issue/createmeta', ['projectKeys' => $this->key, 'expand' => 'projects.issuetypes.fields']);
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

    public function createIssue($issueTypeName)
    {
        if (!isset($this->issueTypes[$issueTypeName])) {
            throw new InvalidParamException("Issue type \"{$issueTypeName}\" does not exist in project \"{$this->name}\"");
        }
        return Issue::create($this, $this->issueTypes[$issueTypeName]);
    }

    public function getIssue($key)
    {
        $data = $this->client->get('issue/' . $key);
        if (isset($data['id'])) {
            $issue = Issue::populateOne($this, $data);
            return $issue;
        }
        return null;
    }

}