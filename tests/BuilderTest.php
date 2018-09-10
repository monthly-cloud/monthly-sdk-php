<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

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

        $this->assertContains('?include=comments', $builder->buildUrl());
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

        $this->assertContains('?include=comments%2Cimages', $builder->buildUrl());
    }

    /**
     * Test $builder->find();
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
     * Test flushing. Builder should reset parameters on endpoint() call.
     *
     * @return void
     */
    public function testFlushing()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('properties')
            ->with('comments');

        $builder->endpoint('properties');

        $this->assertNotContains('?include=comments', $builder->buildUrl());
    }
}
