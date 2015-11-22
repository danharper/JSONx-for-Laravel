<?php

namespace danharper\LaravelJSONx\Tests;

use danharper\LaravelJSONx\JSONxMiddleware;
use danharper\LaravelJSONx\JSONxRequestAdaptor;
use danharper\LaravelJSONx\JSONxResponseAdaptor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery as m;

class JSONxMiddlewareTest extends TestCase {

	public function tearDown()
	{
		m::close();
	}

	public function testItAdaptsThem()
	{
		$origReq = new Request(['foo' => 'bar']);
		$adapReq = new Request(['foo' => 'lol']);

		$origRes = new Response('x');
		$adapRes = new Response('y');

		$mockRequestAdaptor = m::mock(JSONxRequestAdaptor::class)
			->shouldReceive('handle')
			->with($origReq)
			->andReturn($adapReq)
			->getMock();

		$mockResponseAdaptor = m::mock(JSONxResponseAdaptor::class)
			->shouldReceive('handle')
			->with($adapReq, $origRes)
			->andReturn($adapRes)
			->getMock();

		$next = function() use ($origRes) { return $origRes; };

		$out = (new JSONxMiddleware($mockRequestAdaptor, $mockResponseAdaptor))->handle($origReq, $next);

		$this->assertEquals($adapRes, $out);
	}

}