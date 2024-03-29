<?php

namespace fl\curl;

class Response implements ResponseInterface
{
	public float   $time;
	public string  $url;
	public ?string $proxy = null;

	public int     $errorCode = 0;
	public string  $errorText = '';
	public string  $errorDesc = '';

	public ?string $header = null;
	public ?string $body   = null;

	public ?int   $code          = null;
	public int    $size          = 0;
	public int    $contentLength = 0;
	public string $mimeType      = '';
	public string $charset       = '';

	public array  $headers = [];
	public array  $cookie  = [];

	public ?string $requestHeader = null;
	public ?string $requestBody   = null;

	public int    $redirectCount = 0;
	public string $effectiveUrl  = '';
	public string $redirectUrl   = '';

	public array $data;


	public function init($handle, $options) : void
	{
		curl_setopt_array($handle, $options);

		$result = curl_exec($handle);

		$this->time  = microtime(true);
		$this->url   = $options[CURLOPT_URL];
		$this->proxy = $options[CURLOPT_PROXY] ?? null;

		if ($result === false)
		{
			$this->errorCode = curl_errno($handle);
			$this->errorText = curl_strerror($this->errorCode) ?? '';
			$this->errorDesc = curl_error($handle);

			return;
		}

		$this->code = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
		$this->size = curl_getinfo($handle, CURLINFO_SIZE_DOWNLOAD_T);

		if (isset($options[CURLINFO_HEADER_OUT]) && $options[CURLINFO_HEADER_OUT])
		{
			$this->requestHeader = curl_getinfo($handle, CURLINFO_HEADER_OUT);
			$this->requestBody   = $options[CURLOPT_POSTFIELDS] ?? null;
		}


		if (isset($options[CURLOPT_FOLLOWLOCATION]) && $options[CURLOPT_FOLLOWLOCATION])
		{
			$this->redirectCount = curl_getinfo($handle, CURLINFO_REDIRECT_COUNT);
			$this->effectiveUrl  = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
		}
		else
		{
			$this->redirectUrl = curl_getinfo($handle, CURLINFO_REDIRECT_URL);
		}

		$contentType = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);

		if ($contentType)
		{
			$contentType = explode(';', $contentType);

			$this->mimeType = trim(array_shift($contentType));

			$re = '/^\s?(?P<key>\S+)\s?=\s?(?P<value>\S+)/';

			foreach ($contentType as $part)
			{
				if (preg_match_all($re, $part, $matches, PREG_SET_ORDER))
				{
					switch ($matches[0]['key'])
					{
						case 'charset':
							$this->charset = $matches[0]['value'];
							break;
					}
				}
			}
		}

		if ($options[CURLOPT_HEADER])
		{
			$headerSize   = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
			$this->header = substr($result, 0, $headerSize);
			$this->body   = substr($result, $headerSize);

			$this->parseHeader();
		}
		else
		{
			$this->body = $result;
		}

		if ($this->body)
		{
			$this->parseBody();
		}

		$this->contentLength = curl_getinfo($handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	}


	protected function parseHeader() : void
	{
		$lines = explode("\r\n", $this->header);

		foreach ($lines as $line)
		{
			if (!trim($line))
			{
				continue;
			}

			/**
			 * If server return several status lines.
			 * For example:
			 *
			 * HTTP/1.1 100 Continue
			 *
			 * HTTP/1.1 200 OK
			 * <headers>
			 *
			 */
			if (strpos($line, 'HTTP/') === 0)
			{
				$key   = 'status';
				$value = $line;
			}
			else
			{
				[$key, $value] = explode(':', $line, 2);

				$key = strtolower($key);
			}

			$key   = trim($key);
			$value = trim($value);

			if (!array_key_exists($key, $this->headers))
			{
				$this->headers[$key] = [];
			}

			$this->headers[$key][] = $value;
		}

		if (array_key_exists('set-cookie', $this->headers))
		{
			$now = (int) $this->time;

			foreach (Cookie::parse($this->headers['set-cookie'], $now) as $cookie)
			{
				$this->cookie[$cookie->name] = $cookie;
			}
		}
	}


	protected function parseBody() : void
	{
		switch ($this->mimeType)
		{
			case 'application/json':
				$this->data = json_decode($this->body, true);
				break;
		}
	}
}
