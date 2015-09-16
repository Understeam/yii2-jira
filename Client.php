<?php

namespace understeam\jira;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\BufferStream;
use understeam\httpclient\Event;
use Yii;
use yii\base\Component;
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
class Client extends Component
{

    public $jiraUrl;

    public $username;
    public $password;

    public $httpClientId = 'httpclient';


    public function getApiEndpointUrl()
    {
        return rtrim($this->jiraUrl, '/') . '/rest/api/2/';
    }

    public function getUrlOfPath($path)
    {
        return $this->getApiEndpointUrl() . ltrim($path, '/');
    }

    public function get($path, $params = [])
    {
        if (!empty($params)) {
            $params = http_build_query($params);
            $path .= '?' . $params;
        }
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
        $url = $this->getUrlOfPath($path);
        try {
            $result = $this->httpClient->request($url, $method, function (Event $event) use ($body) {
                $request = $event->message;
                $authString = base64_encode($this->username . ':' . $this->password);
                $request->addHeader("Authorization", "Basic " . $authString);
                $request->addHeader("Accept", "application/json");
                if (!empty($body)) {
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
            $string = $e->getResponse()->getBody()->__toString();
            $result = Json::decode($string);
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