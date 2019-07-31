<?php

namespace understeam\jira;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\BufferStream;
use understeam\httpclient\Event;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Client component for Jira REST API
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

	public $cacheDuration = 30;

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

	public function getProject($key)
	{
		$data = $this->get("project/{$key}");
		if (!isset($data['id'])) {
			return null;
		} else {
			return Project::populate($this, $data);
		}
	}

	public function request($method, $path, $body = [])
	{
		$url = $this->getUrlOfPath($path);

		if (is_array($body) && !empty($body)) {
			$body = Json::encode($body);
		}

		$cacheKey = md5($method . $url. $body);
		$result = Yii::$app->cache->get($cacheKey);
		if ($result !== false)
		{
			return $result;
		}

		try {
			$result = $this->httpClient->request($url, $method, function (Event $event) use ($body) {
					$request = $event->message;
					$authString = base64_encode($this->username . ':' . $this->password);
					$request->addHeader("Authorization", "Basic " . $authString);
					$request->addHeader("Accept", "application/json");
					$request->addHeader("Content-Type", "application/json");
					if (!empty($body)) {
						$stream = new BufferStream();
						$stream->write($body);
						$request->setBody($stream);
					}
				});
			if (is_string($result)) {
				$result = Json::decode($result);
			}
			\Yii::trace($url . "\n" . $body, __CLASS__);

		} catch (RequestException $e) {

			$result = $e->getResponse()->getBody()->__toString();

			$contentType = $e->getResponse()->getHeader('Content-Type')[0];
			if (strpos($contentType, 'application/json') !== false)
			{
				$result = Json::decode($result);
			}

			\Yii::error($result, __CLASS__);
		}

		Yii::$app->cache->set($cacheKey, $result, $this->cacheDuration);

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

	public static function escapeValue($value) {
		return strtr($value, [
				'/' => '\u002f',
				'.' => '\u002e',
			]);
	}

}
