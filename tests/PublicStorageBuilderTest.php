<?php 

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

class PublicStorageBuilderTest extends TestCase
{
    /**
     * Get builder.
     *
     * @return void
     */
    public function getBuilder()
    {
        $builder = new MonthlyCloud\Sdk\PublicStorageBuilder();

        return $builder;
    }

    /**
     * Test if builder generates correct endpoint.
     *
     * @return void
     */
    public function testUrlBuilder()
    {
        $builder = $this->getBuilder();
        $builder->endpoint('contents')
        	->setStorageUrl('http://test')
            ->id(1);

        $this->assertStringEndsWith('http://test/contents/1.json', $builder->buildUrl());
    }

    /**
     * Test if builder generates website id.
     *
     * @return void
     */
    public function testWebsiteUrlBuilder()
    {
        $builder = $this->getBuilder();
        $builder
        	->website(1)
        	->endpoint('contents')
            ->id(1);

        $this->assertStringEndsWith('websites/1/contents/1.json', $builder->buildUrl());
    }

    /**
     * Test if builder using locales correctly.
     *
     * @return void
     */
    public function testLocaleUrlBuilder()
    {
        $builder = $this->getBuilder();
        $builder
        	->website(1)
        	->locale('en')
        	->endpoint('routes');

        $this->assertStringEndsWith('websites/1/routes/en.json', $builder->buildUrl());
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

        $response = $builder->endpoint('contents')
            ->find(1);

        $this->assertObjectHasAttribute('data', $response);
    }

    /**
     * Test flushing. Builder should reset parameters on endpoint() call.
     *
     * @return void
     */
    public function testFlushing()
    {
        $builder = $this->getBuilder();

        $builder->endpoint('routes')
            ->id(1);

        $builder->endpoint('menus');

        $this->assertNotContains('routes', $builder->buildUrl());
        $this->assertNotContains('1', $builder->buildUrl());
    }

    /**
     * Test storage url '/' trim.
     *
     * @return void
     */
    public function testStorageUrlTrim()
    {
        $builder = $this->getBuilder();
        $builder->setStorageUrl('http://test///');

        $this->assertStringEndsWith('http://test', $builder->getStorageUrl());
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
}
