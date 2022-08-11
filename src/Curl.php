<?php


namespace fl\curl;


class Curl
{
	protected int $id;

	protected $handle;

	protected string $responseClass = Response::class;

	protected ?ResponseInterface $lastResponse = null;

	protected array  $options = [];
	protected array  $headers = [];
	protected array  $cookies = [];
	protected array  $query   = [];

	/** @var string|array  */
	protected $body = null;

	protected array $defaultOptions = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => true,
		CURLINFO_HEADER_OUT    => true,
	];


	public function __construct(array $options = [], bool $isDebug = false)
	{
		$this->defaultOptions[CURLOPT_SSL_VERIFYPEER] = !$isDebug;
		$this->defaultOptions[CURLOPT_SSL_VERIFYHOST] = $isDebug ? 0 : 2;

		$this->setOptions(array_merge($this->defaultOptions, $options));

		$this->handle = curl_init();
		$this->id     = (int) $this->handle;
	}


	public function __clone()
	{
		$this->handle = curl_init();
		$this->id     = (int) $this->handle;

		$this->lastResponse = null;
	}


	// >>> Requests


	public function get(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'GET');
		$this->setOption(CURLOPT_HTTPGET, true);
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function post(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'POST');
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function put(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function delete(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function options(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function head(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'HEAD');
		$this->setOption(CURLOPT_NOBODY, true);
		// $this->unsetOption(CURLOPT_WRITEFUNCTION);
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	public function patch(string $url, ?ResponseInterface $response = null) : ResponseInterface
	{
		$this->setHeader('X-HTTP-Method-Override', 'PATCH');
		$this->setOption(CURLOPT_CUSTOMREQUEST, 'PATCH');
		$this->setOption(CURLOPT_URL, $url);

		return $this->exec($response);
	}


	// <<< Requests


	public function share(CurlShare $curlShare) : self
	{
		curl_setopt($this->handle, CURLOPT_SHARE, $curlShare->handle);

		return $this;
	}


	public function setQuery(array $params) : self
	{
		$this->query = $params;

		return $this;
	}


	public function setOptions(array $options) : self
	{
		foreach ($options as $key => $value)
		{
			$this->setOption($key, $value);
		}

		return $this;
	}


	public function setOption(int $option, $value) : self
	{
		switch ($option)
		{
			case CURLOPT_POSTFIELDS:
				$this->setBody($value);
				break;
			case CURLOPT_HTTPHEADER:
				$this->setHeaders($value);
				break;
			case CURLOPT_URL:
				// $this->options[$key] = $value;
				$this->options[$option] = $this->buildUrl($value);
				break;
			default:
				$this->options[$option] = $value;

		}

		return $this;
	}


	public function setHeaders(array $headers) : self
	{
		foreach ($headers as $key => $value)
		{
			$this->setHeader($key, $value);
		}

		return $this;
	}


	public function setHeader($key, $value) : self
	{
		$this->headers[strtolower($key)] = $value;

		return $this;
	}


	public function resetHeaders() : self
	{
		$this->headers = [];

		return $this;
	}


	public function setCookie(string $key, string $value) : self
	{
		$this->cookies[$key] = $value;

		return $this;
	}


	public function setCookies(array $cookies) : self
	{
		foreach ($cookies as $key => $value)
		{
			$this->setCookie($key, $value);
		}

		return $this;
	}


	/**
	 * @param   mixed  $data
	 * @param   bool   $asJson
	 *
	 * @return $this
	 */
	public function setBody($data, bool $asJson = false) : self
	{
		if($asJson)
		{
			$this->setHeader('Content-Type', 'application/json');

			if(is_object($data) || is_array($data))
			{
				$data = json_encode($data);
			}
		}
		elseif (is_object($data))
		{
			$data = method_exists($data, '__toString') ? (string) $data : (array) $data;
		}

		$this->body = $data;

		return $this;
	}


	/**
	 * @param string|Proxy $proxy
	 *
	 * @return $this
	 */
	public function setProxy($proxy) : self
	{
		if(is_string($proxy))
		{
			$proxy = Proxy::fromUrl($proxy);
		}

		$this->setOptions($proxy->curlOptions());

		return $this;
	}


	public function getHeaders()
	{
		return $this->headers;
	}


	public function getHeader(string $key)
	{
		return $this->headers[strtolower($key)] ?? null;
	}


	public function getOptions()
	{
		return $this->options;
	}


	public function getOption(int $option)
	{
		return $this->options[$option] ?? null;
	}


	protected function buildOptions() : array
	{
		$options = $this->options;

		if($this->body)
		{
			$options[CURLOPT_POSTFIELDS] = $this->body;
			$this->body = null;
		}

		$options[CURLOPT_HTTPHEADER] = $this->buildHeaders();

		$cookies = $this->buildCookies();
		if($cookies)
		{
			$options[CURLOPT_COOKIE] = $cookies;
		}

		return $options;
	}


	protected function buildHeaders() : array
	{
		$headers = [];

		foreach ($this->headers as $key => $value)
		{
			$headers[] = "$key: $value";
		}

		return $headers;
	}


	private function buildCookies() : string
	{
		$cookies = [];

		foreach ($this->cookies as $key => $value)
		{
			$cookies[] = "$key=$value";
		}

		return implode('; ', $cookies);
	}


	protected function buildUrl($url) : string
	{
		$parts = parse_url($url);

		if (isset($parts['query']))
		{
			parse_str($parts['query'], $q);
			$parts['query'] = $this->query + $q;
		}
		elseif ($this->query)
		{
			$parts['query'] = $this->query;
		}

		$this->query = [];

		$scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';

		$host     = $parts['host'] ?? '';
		$port     = isset($parts['port']) ? ':' . $parts['port'] : '';

		$user     = $parts['user'] ?? '';
		$pass     = isset($parts['pass']) ? ':' . $parts['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';

		$path     = $parts['path'] ?? '';
		$query    = isset($parts['query']) ? '?' . http_build_query($parts['query']) : '';
		$fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

		return implode('', [$scheme, $user, $pass, $host, $port, $path, $query, $fragment]);
	}



	public function reset() : self
	{
		curl_reset($this->handle);

		return $this;
	}


	protected function exec(?ResponseInterface $response = null) : ResponseInterface
	{
		if(!$response)
		{
			$responseClass = $this->responseClass;

			$response = new $responseClass();
		}

		$response->init($this->handle, $this->buildOptions());

		$this->lastResponse = $response;

		return $response;
	}


	public function lastRequestTime() : ?float
	{
		return $this->lastResponse ? $this->lastResponse->requestTime : null;
	}
}

