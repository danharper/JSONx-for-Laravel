<?php

namespace danharper\LaravelJSONx;

use danharper\JSONx\JSONx;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JSONxResponseAdaptor {

	/**
	 * @var JSONx
	 */
	private $converter;

	public function __construct(JSONx $converter)
	{
		$this->converter = $converter;
	}

	public function handle(Request $request, $response)
	{
		if ($this->wantsXml($request))
		{
			if ($response instanceof Response)
			{
				if ( ! $this->providingJson($response)) return $response;

				return new Response(
					$this->converter->toJSONx($response->getOriginalContent()),
					$response->getStatusCode(),
					array_merge($response->headers->all(), ['Content-Type' => 'application/xml'])
				);
			}
			else if ($response instanceof JsonResponse)
			{
				return new Response(
					$this->converter->toJSONx(json_decode($response->getContent())),
					$response->getStatusCode(),
					array_merge($response->headers->all(), ['Content-Type' => 'application/xml'])
				);
			}
			else
			{
				return new Response($this->converter->toJSONx($response), 200, ['Content-Type' => 'application/xml']);
			}
		}
		else
		{
			return $response;
		}
	}

	private function wantsXml(Request $request)
	{
		$acceptable = $request->getAcceptableContentTypes();

		return isset($acceptable[0]) && $acceptable[0] === 'application/xml';
	}

	private function providingJson(Response $response)
	{
		return $response->headers->get('Content-Type') === 'application/json';
	}

}