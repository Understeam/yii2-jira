<?php
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\BufferStream;
use understeam\httpclient\Event;
use yii\helpers\Json;

/**
 * Client component for JIRA REST API
 *
 * Api docs: https://docs.atlassian.com/jira/REST/latest/
 *
 * @property \understeam\httpclient\Client $httpClient
 *
 * @author Anatoly Rugalev <arugalev@enaza.ru>
 */
class Client extends \yii\base\Component
{

    public $jiraUrl;

    public $httpClientId = 'httpclient';


    public function getApiEndpointUrl()
    {
        return rtrim($this->jiraUrl, '/') . '/jira/rest/api/2/';
    }

    public function getUrlOfPath($path)
    {
        return $this->getApiEndpointUrl() . ltrim($path, '/');
    }

    public function get($path)
    {
        return $this->request('GET', $path);
    }

    public function post($path, $body = [])
    {
        return $this->request('POST', $path, $body);
    }

    public function delete($path, $body = [])
    {
        return $this->request('DELETE', $path, $body);
    }

    public function put($path, $body = [])
    {
        return $this->request('PUT', $path, $body);
    }

    public function request($method, $path, $body = [])
    {
        try {
            $result = $this->httpClient->request($this->getUrlOfPath($path), $method, function (Event $event) use ($body) {
                if (!empty($body)) {
                    $request = $event->message;
                    $body = new BufferStream();
                    if (is_array($body)) {
                        $body = Json::encode($body);
                    }
                    $body->write($body);
                    $request->setBody($body);
                }
            });
            if (is_string($result)) {
                $result = Json::decode($result);
            }
        } catch (RequestException $e) {
            $result = Json::decode($e->getResponse()->getBody()->__toString());
        }
        return $result;
    }

    /**
     * @return \understeam\httpclient\Client
     * @throws \yii\base\InvalidConfigException
     */
    public function getHttpClient()
    {
        return Yii::$app->get($this->httpClientId);
    }

}