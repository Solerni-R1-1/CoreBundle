<?php

namespace Claroline\CoreBundle\Handler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as BaseExceptionHandler;
/**
 * ExceptionHandler converts an exception to a Response object.
 *
 * It is mostly useful in debug mode to replace the default PHP/XDebug
 * output with something prettier and more useful.
 *
 * As this class is mainly used during Kernel boot, where nothing is yet
 * available, the Response content is always HTML.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionHandler extends BaseExceptionHandler
{
	private $debug;
	private $charset;

	public function __construct($debug = true, $charset = 'UTF-8')
	{
		$this->debug = $debug;
		$this->charset = $charset;
	}

	/**
	 * Registers the exception handler.
	 *
	 * @param Boolean $debug
	 *
	 * @return ExceptionHandler The registered exception handler
	 */
	public static function register($debug = true)
	{
		$handler = new static($debug);

		set_exception_handler(array($handler, 'handle'));

		return $handler;
	}

	/**
	 * Sends a response for the given Exception.
	 *
	 * If you have the Symfony HttpFoundation component installed,
	 * this method will use it to create and send the response. If not,
	 * it will fallback to plain PHP functions.
	 *
	 * @param \Exception $exception An \Exception instance
	 *
	 * @see sendPhpResponse
	 * @see createResponse
	 */
	public function handle(\Exception $exception)
	{
		throw $exception;
	}
	
}
