<?php


namespace fl\curl;

/**
 *
 * # Netscape HTTP Cookie File
 * # http://curl.haxx.se/rfc/cookie_spec.html
 *
 * example.com	FALSE	/	FALSE	1338534278	cookiename	value
 *
 * string example.com - the domain name
 * boolean FALSE - include subdomains
 * string / - path
 * boolean FALSE - send/receive over HTTPS only
 * number 1338534278 - expires at - seconds since Jan 1st 1970, or 0
 * string cookiename - name of the cookie
 * string value - value of the cookie
 *
 */

class Cookie
{
	public string $name = '';
	public string $value = '';

	public string $domain = '';
	public string $path   = '/';
	public int    $expires = 0;

	public bool $secure = false;
	public bool $httponly = false;


	/**
	 * Cookie constructor.
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   string  $domain
	 * @param   string  $path
	 * @param   int     $expires
	 * @param   bool    $secure
	 * @param   bool    $httponly
	 */
	public function __construct($name, $value, $domain = '', $path = '/', $expires = 0, $secure = false, $httponly = false)
	{
		$this->name     = $name;
		$this->value    = $value;

		$this->domain   = $domain;
		$this->path     = $path;
		$this->expires  = $expires;

		$this->secure   = $secure;
		$this->httponly = $httponly;
	}

	/**
	 * @param   string|string[]  $cookies
	 *
	 * @return self[]
	 */
	public static function parse($cookies) : array
	{
		$result = [];

		foreach ((array) $cookies as $key => $cookie)
		{
			if (stripos($cookie, 'Set-Cookie:') === 0)
			{
				$cookie = str_ireplace('Set-Cookie:', '', $cookie);
			}

			$parts = explode(';', $cookie);

			$instances = [];

			$domain   = null;
			$path     = null;
			$expires  = null;
			$secure   = null;
			$httponly = null;

			foreach ($parts as $part)
			{
				$part = trim($part);

				if (!$part)
				{
					continue;
				}

				$part = explode('=', $part);

				$key   = strtolower(trim($part[0]));
				$value = isset($part[1]) ? trim($part[1]) : '';

				if (!$key)
				{
					continue;
				}

				switch ($key)
				{
					case 'domain':
						$domain = $value;
						break;
					case 'path':
						$path = $value;
						break;
					case 'expires':
						$expires = strtotime($value);
						break;
					case 'secure':
						$secure = true;
						break;
					case 'httponly':
						$httponly = true;
						break;
					default:
						$instances[$key] = $value;
				}
			}

			foreach ($instances as $name => $value)
			{
				$instance = new static($name, $value);

				if ($domain)
				{
					$instance->domain = $domain;
				}
				if ($path)
				{
					$instance->path = $path;
				}
				if ($expires)
				{
					$instance->expires = $expires;
				}
				if ($secure)
				{
					$instance->secure = $secure;
				}
				if ($httponly)
				{
					$instance->httponly = $httponly;
				}

				$result[] = $instance;
			}
		}

		return $result;
	}


	public function cookieResponseHeader() : string
	{
		$result[] = "$this->name=$this->value";

		if ($this->expires)
		{
			$result[] = 'Expires=' . gmdate('r', $this->expires);
		}

		if ($this->domain)
		{
			$result[] = "Domain=$this->domain";
		}

		if ($this->path)
		{
			$result[] = "Path=$this->path";
		}

		if ($this->secure)
		{
			$result[] = 'Secure';
		}

		if ($this->httponly)
		{
			$result[] = 'HttpOnly';
		}

		return 'Set-Cookie: ' . implode('; ', $result);
	}


	public function __toString()
	{
		return "$this->name=$this->value";
	}
}
