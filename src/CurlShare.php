<?php

namespace fl\curl;

/**
 * @property-read int      $id
 * @property-read bool     $shareCookie
 * @property-read bool     $shareDns
 * @property-read bool     $shareSession
 * @property-read resource $handle
 */
class CurlShare
{
	protected int $id;

	protected bool $shareCookie;
	protected bool $shareDns;
	protected bool $shareSession;

	/** @var resource */
	protected $handle;


	public function __construct(bool $shareCookie = true, bool $shareDns = true, bool $shareSession = true)
	{
		$this->init($shareCookie, $shareDns, $shareSession);
	}


	public function __clone()
	{
		$this->init($this->shareCookie, $this->shareDns, $this->shareSession);
	}


	protected function init(bool $shareCookie, bool $shareDns, bool $shareSession) : void
	{
		$this->handle = curl_share_init();
		$this->id     = (int) $this->handle;

		$this->shareCookie  = $shareCookie;
		$this->shareDns     = $shareDns;
		$this->shareSession = $shareSession;

		curl_share_setopt($this->handle, $shareCookie  ? CURLSHOPT_SHARE : CURLSHOPT_UNSHARE, CURL_LOCK_DATA_COOKIE);
		curl_share_setopt($this->handle, $shareDns     ? CURLSHOPT_SHARE : CURLSHOPT_UNSHARE, CURL_LOCK_DATA_DNS);
		curl_share_setopt($this->handle, $shareSession ? CURLSHOPT_SHARE : CURLSHOPT_UNSHARE, CURL_LOCK_DATA_SSL_SESSION);
	}


	public function __destruct()
	{
		curl_share_close($this->handle);
	}


	public function __get($name)
	{
		return $this->$name;
	}


	public function errorCode() : int
	{
		return curl_share_errno($this->handle);
	}


	public function errorDesc() : string
	{
		return curl_share_strerror($this->errorCode());
	}
}
