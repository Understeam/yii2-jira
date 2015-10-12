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
 * @property Project $project
 *
 * @author Anatoly Rugalev
 * @link https://github.com/AnatolyRugalev
 */
class IssueType extends Model
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
    public $description;

    /**
     * @var bool
     */
    public $subtask;

    /**
     * @var Project
     */
    protected $_project;

    protected $_customFields;
    protected $_customFieldsMap = [];


    /**
     * @param Project $project
     * @param array $data
     * @return IssueType
     */
    public static function populate(Project $project, $data)
    {
        if (!is_array($data) || !isset($data['id'])) {
            return null;
        }
        $issueType = new self;
        $issueType->_project = $project;
        $issueType->id = $data['id'];
        $issueType->name = $data['name'];
        $issueType->description = $data['description'];
        $issueType->subtask = $data['subtask'];

        return $issueType;
    }

    public static function populateAll(Project $project, $data)
    {
        $issueTypes = [];
        foreach ($data as $row) {
            $issueTypes[$row['name']] = self::populate($project, $row);
        }

        return $issueTypes;
    }

    public function getCustomFields()
    {
        if (isset($this->_customFields)) {
            return $this->_customFields;
        }
        $metaData = $this->project->client->get('issue/createmeta', [
            'expand' => 'projects.issuetypes.fields',
            'projectKeys' => $this->project->key,
        ]);
        $this->_customFields = [];
        if (isset($metaData['projects'][0]['issuetypes'][0]['fields'])) {
            $fields = $metaData['projects'][0]['issuetypes'][0]['fields'];
            foreach ($fields as $name => $config) {
                if (strpos($name, 'customfield_') !== 0) {
                    continue;
                }
                $id = substr($name, 12);
                $this->_customFields[$id] = $config;
                $this->_customFieldsMap[$config['name']] = $id;
            }
        }

        return $this->_customFields;
    }

    public function getCustomFieldsMap()
    {
        $this->getCustomFields();

        return $this->_customFieldsMap;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->_project;
    }

}
