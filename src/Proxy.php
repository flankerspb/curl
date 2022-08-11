<?php


namespace fl\curl;


use InvalidArgumentException;

class Proxy
{
	public int     $type;
	public string  $host;
	public ?int    $port;
	public ?string $user;
	public ?string $pass;

	public bool $useTunnel = true;


	public function __construct(string $host, ?int $port = null, ?int $type = null, ?string $user = null, ?string $pass = null)
	{
		$this->type = $type ?? CURLPROXY_HTTP;
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $user ? $pass : null;
	}


	public static function fromUrl(string $url) : self
	{
		$parts = parse_url($url);

		if (!$parts['host'])
		{
			throw new InvalidArgumentException('Url must contains host');
		}

		$type = $parts['scheme'] ?? null;

		switch ($type)
		{
			case 'http':
				$type = CURLPROXY_HTTP;
				break;
			case 'https':
				$type = CURLPROXY_HTTPS;
				break;
			case 'socks4':
				$type = CURLPROXY_SOCKS4;
				break;
			case 'socks4a':
				$type = CURLPROXY_SOCKS4A;
				break;
			case 'socks5':
				$type = CURLPROXY_SOCKS5;
				break;
			case 'socks5a':
				$type = CURLPROXY_SOCKS5_HOSTNAME;
				break;
		}

		return new self(
			$parts['host'],
			$parts['port'] ?? null,
			$type,
			$parts['user'] ?? null,
			$parts['pass'] ?? null
		);
	}


	public function curlOptions() : array
	{
		return [
			CURLOPT_PROXY           => $this->port ? "$this->host:$this->port" : $this->host,
			CURLOPT_PROXYUSERPWD    => $this->pass ? "$this->user:$this->pass" : $this->user,
			CURLOPT_PROXYTYPE       => $this->type,
			CURLOPT_HTTPPROXYTUNNEL => $this->useTunnel,
		];
	}
}
