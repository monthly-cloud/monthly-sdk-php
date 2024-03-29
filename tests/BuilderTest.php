<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * Get builder.
     *
     * @return type
     */
    public function getBuilder()
    {
        $builder = new MonthlyCloud\Sdk\Builder();

        return $builder;
    }

    /**
     * Test if builder generates correct endpoint.
     *
     * @return type
     */
    public function testUrlBuilder()
    {
        $builder = $this->getBuilder();
        $builder->endpoint('properties')
            ->id(1);
        $this->assertStringEndsWith('properties/1', $builder->buildUrl());
    }

    /**
     * Test $builder->filter('query', 'test').
     *
     * @return void
     */
    public function testFilter()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->filter('query', 'test');

        $this->assertStringContainsString('filter%5Bquery%5D=test', $builder->buildUrl());
    }

    /**
     * Test $builder->with('comments').
     *
     * @return void
     */
    public function testWith()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->id(1)
            ->with('comments');

        $this->assertStringContainsString('?include=comments', $builder->buildUrl());
    }

    /**
     * Test $builder->with(['comments', 'images']).
     *
     * @return void
     */
    public function testMultipleWith()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->id(1)
            ->with(['comments', 'images']);

        $this->assertStringContainsString('?include=comments%2Cimages', $builder->buildUrl());
    }

    /**
     * Test $builder->find();.
     *
     * @return void
     */
    public function testFind()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $response = $builder->endpoint('properties')
            ->find(1);

        $this->assertObjectHasAttribute('data', $response);
    }

    /**
     * Test if $builder->first() respond with first object.
     *
     * @return void
     */
    public function testFirst()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": [{"exists": true}]}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $response = $builder->endpoint('properties')
            ->first();

        $this->assertObjectHasAttribute('exists', $response);
    }

    /**
     * Test if $builder->exists() working.
     *
     * @return void
     */
    public function testExists()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": [{"exists": true}]}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $response = $builder->endpoint('properties')
            ->exists();

        $this->assertTrue($response);
    }

    /**
     * Test if $builder->exists() working.
     *
     * @return void
     */
    public function testNotExists()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $response = $builder->endpoint('properties')
            ->exists();

        $this->assertFalse($response);
    }

    /**
     * Test if $builder->pageSize() limiting page size.
     *
     * @return void
     */
    public function testPageSize()
    {
        $builder = $this->getBuilder();

        $builder
            ->endpoint('properties')
            ->pageSize(200);

        $this->assertStringContainsString('page%5Bsize%5D=200', $builder->buildUrl());
    }

    /**
     * Test if $builder->setCurrentPage() set page number.
     *
     * @return void
     */
    public function testPageNumber()
    {
        $builder = $this->getBuilder();

        $builder
            ->endpoint('properties')
            ->setCurrentPage(2);

        $this->assertStringContainsString('page%5Bnumber%5D=2', $builder->buildUrl());
    }

    /**
     * Test page number together with page size.
     *
     * @return void
     */
    public function testPageNumberAndSize()
    {
        $builder = $this->getBuilder();

        $builder
            ->endpoint('properties')
            ->pageSize(200)
            ->setCurrentPage(2);

        $this->assertStringContainsString('page%5Bsize%5D=200', $builder->buildUrl());
        $this->assertStringContainsString('page%5Bnumber%5D=2', $builder->buildUrl());
    }

    /**
     * Test $builder->sort('id').
     *
     * @return void
     */
    public function testSort()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->sort('-id');

        $this->assertStringContainsString('?sort=-id', $builder->buildUrl());
    }

    /**
     * Test $builder->limit() alias to pageSize().
     *
     * @return void
     */
    public function testLimit()
    {
        $builder = $this->getBuilder();

        $builder
            ->endpoint('properties')
            ->limit(200);

        $this->assertStringContainsString('page%5Bsize%5D=200', $builder->buildUrl());
    }

    /**
     * Test flushing. Builder should reset parameters on endpoint() call.
     *
     * @return void
     */
    public function testFlushing()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->with('comments')
            ->filter('query', 'test');

        $builder->endpoint('properties');

        $this->assertStringNotContainsString('filter%5Bquery%5D=test', $builder->buildUrl());
        $this->assertStringNotContainsString('include=comments', $builder->buildUrl());
    }

    /**
     * Test cache ttl setter and getter.
     *
     * @return void
     */
    public function testCacheTtl()
    {
        $builder = $this->getBuilder();

        $builder->cacheTtl(90);

        $this->assertEquals($builder->getCacheTtl(), 90);

        $builder->setCacheTtl(80);

        $this->assertEquals($builder->getCacheTtl(), 80);
    }

    /**
     * Test read only.
     *
     * @return void
     */
    public function testReadOnly()
    {
        $builder = $this->getBuilder()
            ->readOnly(true);

        $this->assertEquals($builder->post(['test' => 1]), (object) []);
    }

    /**
     * Test async Post calls.
     *
     * @return void
     */
    public function testAsyncPost()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $response = $builder->endpoint('properties')
            ->postAsync(['test' => 1]);

        $this->assertInstanceOf(\GuzzleHttp\Promise\Promise::class, $response);
    }

    /**
     * Test if setting api url works correctly.
     *
     * @return void
     */
    public function testSetApiUrl()
    {
        $builder = $this->getBuilder();

        $builder
            ->setApiUrl('http://new-url.test');
        $this->assertEquals($builder->getApiUrl(), 'http://new-url.test');
    }
}
