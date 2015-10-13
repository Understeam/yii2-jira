<?php

namespace understeam\jira;

/**
 * Comment representation model
 *
 * @property Issue $issue
 * @property string $key
 *
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class Comment
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
     * @var Issue
     */
    protected $_issue;

    /**
     * @var string
     */
    public $body;

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
    public function getKey()
    {
        return $this->_key;
    }


}