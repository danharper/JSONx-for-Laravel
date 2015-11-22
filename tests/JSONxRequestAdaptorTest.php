<?php

namespace danharper\LaravelJSONx\Tests;

use danharper\JSONx\JSONx;
use danharper\JSONx\ToJSONx;
use danharper\LaravelJSONx\JSONxRequestAdaptor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class JSONxRequestAdaptorTest extends TestCase {

	public function testItDoesNotModifyRequestsWithoutXml()
	{
		$request = $this->makeRequest();
		$request->headers->set('Content-Type', 'application/json');

		$out = $this->go(clone $request);

		$this->assertEquals($request, $out);
	}

	public function testItModifiesRequestsWithXml()
	{
		$request = $this->makeRequest($this->toJSONx(['foo' => ['bar', 'baz']]));
		$request->headers->set('Content-Type', 'application/xml');

		$out = $this->go(clone $request);

		$this->assertEquals(['foo' => ['bar', 'baz']], $out->input());
	}

	public function testItChangesContentTypeHeaderOfRequestToPretendToBeJson()
	{
		$request = $this->makeRequest($this->toJSONx([]));
		$request->headers->set('Content-Type', 'application/xml');

		$out = $this->go(clone $request);

		$this->assertEquals('application/json', $out->header('Content-Type'));
	}

	public function testItConvertsRequestInAWayThatLaravelCanUseDotNotationOnIt()
	{
		$request = $this->makeRequest($this->toJSONx(['hello' => ['world' => '!!!']]));
		$request->headers->set('Content-Type', 'application/xml');

		$out = $this->go(clone $request);

		$this->assertEquals('!!!', $out->input('hello.world'));
	}

	private function go(Request $request)
	{
		return (new JSONxRequestAdaptor(new JSONx))->handle($request);
	}

	private function makeRequest($content = null)
	{
		return Request::createFromBase(SymfonyRequest::create('http://foo.com', 'POST', [], [], [], [], $content));
	}

	private function toJSONx($php)
	{
		return (new ToJSONx)->execute($php);
	}

}