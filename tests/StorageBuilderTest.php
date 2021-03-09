<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StorageBuilderTest extends TestCase
{
    /**
     * Get builder.
     *
     * @return void
     */
    public function getBuilder()
    {
        $builder = new MonthlyCloud\Sdk\StorageBuilder();

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
     * Test if builder handles "//".
     *
     * @return void
     */
    public function testUrlBuilderDounleSlashFilter()
    {
        $builder = $this->getBuilder();
        $builder->endpoint('contents/')
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

        $this->assertStringEndsWith('/websites/1/contents/1.json', $builder->buildUrl());
    }

    /**
     * Test if builder support root endpoints starting with /.
     *
     * @return void
     */
    public function testRootEndpointUrlBuilder()
    {
        $builder = $this->getBuilder();
        $builder
            ->website(1)
            ->endpoint('/contents')
            ->id(1);

        $this->assertStringEndsWith('/contents/1.json', $builder->buildUrl());
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

        $this->assertStringEndsWith('/websites/1/routes/en.json', $builder->buildUrl());
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
     * Test if builder generates listing id and item id.
     *
     * @return void
     */
    public function testListingItemUrlBuilder()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $listing = $builder
            ->website(1)
            ->listing(2)
            ->getListingItem(3);

        $this->assertStringEndsWith('/websites/1/listings/2/items/3.json', $builder->buildUrl());
        $this->assertNotEmpty($listing);
    }

    /**
     * Test if builder generates listing id and location id.
     *
     * @return void
     */
    public function testLocationUrlBuilder()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $listing = $builder
            ->website(1)
            ->listing(2)
            ->getLocation(1307024979609764);

        $this->assertStringEndsWith('/websites/1/listings/2/locations/1307024979609764.json', $builder->buildUrl());
        $this->assertNotEmpty($listing);
    }

    /**
     * Test getRoutes method.
     *
     * @return void
     */
    public function testGetRoutes()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $routes = $builder
            ->website(1)
            ->getRoutes('en');

        $this->assertStringEndsWith('/websites/1/routes/en.json', $builder->buildUrl());
        $this->assertNotEmpty($routes);
    }

    /**
     * Test content finder.
     *
     * @return void
     */
    public function testContentFinder()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $content = $builder
            ->website(1)
            ->findContent(2);

        $this->assertStringEndsWith('contents/2.json', $builder->buildUrl());
        $this->assertNotEmpty($content);
    }

    /**
     * Test profile finder.
     *
     * @return void
     */
    public function testProfileFinder()
    {
        $builder = $this->getBuilder();

        // Mock client
        $mock = new MockHandler([
            new Response(200, ['Content-Length' => 0], '{"data": []}'),
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $builder->setClient($client);

        $profile = $builder
            ->marketplace(1)
            ->findProfile(2);

        $this->assertStringStartsWith('/marketplaces', $builder->buildUrl());
        $this->assertStringEndsWith('marketplaces/1/profiles/2.json', $builder->buildUrl());
        $this->assertNotEmpty($profile);
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
