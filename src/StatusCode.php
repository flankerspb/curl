<?php

namespace fl\curl;


class StatusCode
{
	// [Informational 1xx]
	public const CONTINUE            = 100;
	public const SWITCHING_PROTOCOLS = 101;

	// [Successful 2xx]
	public const OK                           = 200;
	public const CREATED                      = 201;
	public const ACCEPTED                     = 202;
	public const NONAUTHORITATIVE_INFORMATION = 203;
	public const NO_CONTENT                   = 204;
	public const RESET_CONTENT                = 205;
	public const PARTIAL_CONTENT              = 206;

	// [Redirection 3xx]
	public const MULTIPLE_CHOICES   = 300;
	public const MOVED_PERMANENTLY  = 301;
	public const FOUND              = 302;
	public const SEE_OTHER          = 303;
	public const NOT_MODIFIED       = 304;
	public const USE_PROXY          = 305;
	public const UNUSED             = 306;
	public const TEMPORARY_REDIRECT = 307;

	// [Client Error 4xx]
	public const BAD_REQUEST                     = 400;
	public const UNAUTHORIZED                    = 401;
	public const PAYMENT_REQUIRED                = 402;
	public const FORBIDDEN                       = 403;
	public const NOT_FOUND                       = 404;
	public const METHOD_NOT_ALLOWED              = 405;
	public const NOT_ACCEPTABLE                  = 406;
	public const PROXY_AUTHENTICATION_REQUIRED   = 407;
	public const REQUEST_TIMEOUT                 = 408;
	public const CONFLICT                        = 409;
	public const GONE                            = 410;
	public const LENGTH_REQUIRED                 = 411;
	public const PRECONDITION_FAILED             = 412;
	public const REQUEST_ENTITY_TOO_LARGE        = 413;
	public const REQUEST_URI_TOO_LONG            = 414;
	public const UNSUPPORTED_MEDIA_TYPE          = 415;
	public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	public const EXPECTATION_FAILED              = 417;

	// [Server Error 5xx]
	public const INTERNAL_SERVER_ERROR      = 500;
	public const NOT_IMPLEMENTED            = 501;
	public const BAD_GATEWAY                = 502;
	public const SERVICE_UNAVAILABLE        = 503;
	public const GATEWAY_TIMEOUT            = 504;
	public const HTTP_VERSION_NOT_SUPPORTED = 505;

	public const NAMES = [
		// [Informational 1xx]
		self::CONTINUE                        => 'Continue',
		self::SWITCHING_PROTOCOLS             => 'Switching Protocols',

		// [Successful 2xx]
		self::OK                              => 'OK',
		self::CREATED                         => 'Created',
		self::ACCEPTED                        => 'Accepted',
		self::NONAUTHORITATIVE_INFORMATION    => 'Non-Authoritative Information',
		self::NO_CONTENT                      => 'No Content',
		self::RESET_CONTENT                   => 'Reset Content',
		self::PARTIAL_CONTENT                 => 'Partial Content',

		// [Redirection 3xx]
		self::MULTIPLE_CHOICES                => 'Multiple Choices',
		self::MOVED_PERMANENTLY               => 'Moved Permanently',
		self::FOUND                           => 'Found',
		self::SEE_OTHER                       => 'See Other',
		self::NOT_MODIFIED                    => 'Not Modified',
		self::USE_PROXY                       => 'Use Proxy',
		self::UNUSED                          => '(Unused)',
		self::TEMPORARY_REDIRECT              => 'Temporary Redirect',

		// [Client Error 4xx]
		self::BAD_REQUEST                     => 'Bad Request',
		self::UNAUTHORIZED                    => 'Unauthorized',
		self::PAYMENT_REQUIRED                => 'Payment Required',
		self::FORBIDDEN                       => 'Forbidden',
		self::NOT_FOUND                       => 'Not Found',
		self::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
		self::NOT_ACCEPTABLE                  => 'Not Acceptable',
		self::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
		self::REQUEST_TIMEOUT                 => 'Request Timeout',
		self::CONFLICT                        => 'Conflict',
		self::GONE                            => 'Gone',
		self::LENGTH_REQUIRED                 => 'Length Required',
		self::PRECONDITION_FAILED             => 'Precondition Failed',
		self::REQUEST_ENTITY_TOO_LARGE        => 'Request Entity Too Large',
		self::REQUEST_URI_TOO_LONG            => 'Request-URI Too Long',
		self::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
		self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
		self::EXPECTATION_FAILED              => 'Expectation Failed',

		// [Server Error 5xx]
		self::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
		self::NOT_IMPLEMENTED                 => 'Not Implemented',
		self::BAD_GATEWAY                     => 'Bad Gateway',
		self::SERVICE_UNAVAILABLE             => 'Service Unavailable',
		self::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
		self::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
	];


	public static function name(int $code) : string
	{
		return array_key_exists($code, self::NAMES) ? self::NAMES[$code] : '';
	}


	public static function message(int $code) : string
	{
		return array_key_exists($code, self::NAMES) ? $code . ' ' . self::NAMES[$code] : $code;
	}


	public static function isError(int $code) : bool
	{
		return is_numeric($code) && $code >= self::BAD_REQUEST;
	}


	public static function canHaveBody(int $code) : bool
	{
		return
			// True if not in 100s
			($code < self::CONTINUE || $code >= self::OK)
			&&
			// and not 204 NO CONTENT
			$code !== self::NO_CONTENT
			&&
			// and not 304 NOT MODIFIED
			$code !== self::NOT_MODIFIED;
	}
}
