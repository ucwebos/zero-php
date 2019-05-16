<?php

namespace Zero\Route;

use Zero\Business\Http\Response;

class ErrorHandler {
	const NOT_FOUND          = 404;
	const NOT_ALLOWED        = 405;
	const CLASS_NOT_FOUND    = 404;
	const CLASS_ILLEGAL      = 500;
	const METHOD_NOT_FOUND   = 404;
	const METHOD_NOT_ALLOWED = 405;
	const SERVER_ERROR       = 500;

	/**
	 * @return Response
	 */
	public function notFound() {
		return (new Response())->setStatus(self::NOT_FOUND)
			->setResult('NOT FOUND');
	}

	/**
	 * @return Response
	 */
	public function notAllowed() {
		return (new Response())->setStatus(self::NOT_ALLOWED)
			->setResult('NOT ALLOWED');
	}

	/**
	 * @return Response
	 */
	public function classNotFound() {
		return (new Response())->setStatus(self::CLASS_NOT_FOUND)
			->setResult('CLASS NOT FOUND');
	}

	/**
	 * @return Response
	 */
	public function classIllegal() {
		return (new Response())->setStatus(self::CLASS_ILLEGAL)
			->setResult('CLASS ILLEGAL');
	}

	/**
	 * @return Response
	 */
	public function methodNotFound() {
		return (new Response())->setStatus(self::METHOD_NOT_FOUND)
			->setResult('METHOD NOT FOUND');
	}

	/**
	 * @return Response
	 */
	public function classMethodNotAllowed() {
		return (new Response())->setStatus(self::METHOD_NOT_ALLOWED)
			->setResult('METHOD NOT ALLOWED');
	}

	/**
	 * @return Response
	 */
	public function serverError() {
		return (new Response())->setStatus(self::SERVER_ERROR)
			->setResult('SERVER ERROR');
	}
}
