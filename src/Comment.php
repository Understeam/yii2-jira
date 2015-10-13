<?php

namespace understeam\jira;

use yii\base\Model;

/**
 * Comment representation model
 *
 * @property Issue $issue
 * @property string $id
 *
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class Comment extends Model
{
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var Issue
     */
    protected $_issue;

    /**
     * @var string
     */
    public $body;

    public static function create(Issue $issue)
    {
        $comment = new self;
        $comment->_issue = $issue;

        return $comment;
    }

    /**
     * @return Issue
     */
    public function getIssue()
    {
        return $this->_issue;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param Issue $issue
     * @param array $data
     * @return Comment[]
     */
    public static function populateAll(Issue $issue, $data)
    {
        if (empty($data)) {
            return [];
        }
        $comments = [];
        foreach ($data as $commentData) {
            $comments[] = self::populate($issue, $commentData);
        }

        return $comments;
    }

    /**
     * @param Issue $issue
     * @param array $data
     * @return Comment
     */
    public static function populate(Issue $issue, $data)
    {
        if (!is_array($data) || !isset($data['id'])) {
            return null;
        }
        $comment = new self;
        $comment->_issue = $issue;
        $comment->_id = $data['id'];
        $comment->body = $data['body'];

        return $comment;
    }

    public function serialize()
    {
        return [
            'body' => $this->body,
        ];
    }

    public function delete()
    {

    }


    public function refresh($data = null)
    {
        if (!is_array($data)) {
            $data = $this->issue->getComment($this->id)->attributes;
        }
        if ($this->id) {
            $this->setAttributes($data, false);
        }

        return false;
    }

    public function save()
    {
        if (!$this->id) {
            return $this->insert();
        } else {
            return $this->update();
        }
    }

    public function insert()
    {
        $result = $this->issue->project->client->post('issue/' . $this->issue->key . '/comment', $this->serialize());
        if (!empty($result['errors'])) {
            $this->addErrors($result['errors']);

            return false;
        }
        $this->refresh($result);
        return true;
    }

    public function update()
    {
        $result = $this->issue->project->client->put('issue/' . $this->issue->key . '/comment/' . $this->id, $this->serialize());
        if (!empty($result['errors'])) {
            $this->addErrors($result['errors']);

            return false;
        }
        $this->refresh($result);
        return true;
    }

}