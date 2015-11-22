<?php

namespace danharper\LaravelJSONx\Tests;

use danharper\JSONx\JSONx;
use danharper\JSONx\ToJSONx;
use danharper\LaravelJSONx\JSONxResponseAdaptor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class JSONxResponseAdaptorTest extends TestCase {

	public function testItDoesNotModifyResponsesWhichArentRequestedToBeXml()
	{
		$request = $this->makeRequest();
		$request->headers->set('Accept', 'application/json');

		$out = $this->go($request, []);

		$this->assertEquals([], $out);
	}

	public function testItConvertsRawResponsesToSuccessfulXmlResponse()
	{
		$request = $this->makeRequest();
		$request->headers->set('Accept', 'application/xml');

		$out = $this->go($request, ['foo', 'bar']);

		$this->assertInstanceOf(Response::class, $out);
		$this->assertEquals(200, $out->getStatusCode());
		$this->assertEquals('application/xml', $out->headers->get('Content-Type'));
		$this->assertXmlStringEqualsXmlString($this->toJSONx(['foo', 'bar']), $out->getContent());
	}

	public function testItConvertsJsonResponseObjectsToXmlResponsesAndMaintainsHeaders()
	{
		$request = $this->makeRequest();
		$request->headers->set('Accept', 'application/xml');

		$out = $this->go($request, new JsonResponse(['foo' => 'bar'], 201, ['X-Omg' => 'Yeah!']));

		$this->assertInstanceOf(Response::class, $out);
		$this->assertEquals(201, $out->getStatusCode());
		$this->assertEquals('application/xml', $out->headers->get('Content-Type'));
		$this->assertEquals('Yeah!', $out->headers->get('X-Omg'));
		$this->assertXmlStringEqualsXmlString($this->toJSONx(['foo' => 'bar']), $out->getContent());
	}

	public function testItConvertsPlainResponsesObjectsToXmlResponsesAndMaintainsHeaders()
	{
		$request = $this->makeRequest();
		$request->headers->set('Accept', 'application/xml');

		$out = $this->go($request, new Response(['foo' => true], 400, ['X-Foo' => 'Yeah!']));

		$this->assertInstanceOf(Response::class, $out);
		$this->assertEquals(400, $out->getStatusCode());
		$this->assertEquals('application/xml', $out->headers->get('Content-Type'));
		$this->assertEquals('Yeah!', $out->headers->get('X-Foo'));
		$this->assertXmlStringEqualsXmlString($this->toJSONx(['foo' => true]), $out->getContent());
	}

	public function testItDoesNotConvertPlainResponsesWhichAreNotJson()
	{
		$request = $this->makeRequest();
		$request->headers->set('Accept', 'application/xml');

		$response = new Response('hello', 500, ['Content-Type' => 'application/xml']);

		$out = $this->go($request, clone $response);

		$this->assertEquals($response, $out);
	}

	private function go(Request $request, $response)
	{
		return (new JSONxResponseAdaptor(new JSONx))->handle($request, $response);
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