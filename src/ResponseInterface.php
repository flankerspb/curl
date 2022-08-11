<?php


namespace fl\curl;


interface ResponseInterface
{
	/**
	 * @param   resource  $handle
	 * @param   array     $options
	 *
	 * @return void
	 */
	public function init($handle, array $options) : void;
}
