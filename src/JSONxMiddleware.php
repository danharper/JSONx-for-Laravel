<?php

namespace danharper\LaravelJSONx;

use Closure;
use Illuminate\Http\Request;

class JSONxMiddleware {

	/**
	 * @var JSONxRequestAdaptor
	 */
	private $requestAdaptor;

	/**
	 * @var JSONxResponseAdaptor
	 */
	private $responseAdaptor;

	public function __construct(JSONxRequestAdaptor $requestAdaptor, JSONxResponseAdaptor $responseAdaptor)
	{
		$this->requestAdaptor = $requestAdaptor;
		$this->responseAdaptor = $responseAdaptor;
	}

	/**
	 * @param Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$request = $this->requestAdaptor->handle($request);

		$response = $next($request);

		return $this->responseAdaptor->handle($request, $response);
	}

}