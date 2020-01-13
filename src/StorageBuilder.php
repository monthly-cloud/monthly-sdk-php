<?php

namespace MonthlyCloud\Sdk;

use GuzzleHttp\Client;
use MonthlyCloud\Sdk\Cache\CacheInterface;

class StorageBuilder
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $extension = 'json';

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int|null
     */
    private $websiteId;

    /**
     * @var int|null
     */
    private $listingId;

    /**
     * Guzzle client.
     *
     * @var GuzzleHttp\Client|null
     */
    private $client;

    /**
     * Guzzle response.
     *
     * @var GuzzleHttp\Psr7\Response|null
     */
    private $response;

    /**
     * Storage url.
     *
     * @var string
     */
    private $storageUrl;

    /**
     * @var MonthlyCloud\Sdk\Cache\CacheInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $useCache = false;

    /**
     * Cache ttl in seconds (Laravel 5.8+) or minutes.
     *
     * @var int
     */
    private $cacheTtl = 60;

    public function __construct($storageUrl = '')
    {
        $this->storageUrl = $storageUrl;
    }

    /**
     * Set endpoint.
     *
     * Ex.: endpoint("menus"), endpoint("contents/").
     *
     * @param string $endpoint
     *
     * @return self
     */
    public function endpoint($endpoint = null)
    {
        $this->flush();

        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get routes for locale. Locale is auto-detected by default.
     *
     * @param string|null $locale
     * @return object
     */
    public function getRoutes($locale = null)
    {
        $this->endpoint('routes');

        if (!empty($locale)) {
            $this->locale($locale);
        }

        return $this->get();
    }

    /**
     * Find content by id.
     *
     * @param int $contentId
     * @return object
     */
    public function findContent($contentId)
    {
        return $this->endpoint('contents')->find($contentId);
    }

    /**
     * Get listing item.
     *
     * @param int $id
     *
     * @return object
     */
    public function getListingItem($id)
    {
        $listingId = $this->getListing();

        if (empty($listingId)) {
            throw new \Exception('Please set listing id.');
        }

        return $this->endpoint('listings/'.$listingId.'/items')
            ->find($id);
    }

    /**
     * Set listing id.
     *
     * @param int $listingId
     *
     * @return self
     */
    public function listing($listingId)
    {
        $this->listingId = $listingId;

        return $this;
    }

    /**
     * Get current listing id.
     *
     * @return int
     */
    public function getListing()
    {
        return $this->listingId;
    }

    /**
     * Build url.
     *
     * @return string
     */
    public function buildUrl()
    {
        $url = $this->getStorageUrl();

        if ($websiteId = $this->getWebsite()) {
            $url .= '/websites/'.$websiteId;
        }

        if ($endpoint = $this->getEndpoint()) {
            $url .= '/'.$endpoint;
        }

        if ($id = $this->getId()) {
            $url .= '/'.$id;
        } else {
            $url .= '/'.$this->getLocale();
        }

        $url .= '.'.$this->getExtension();

        return $url;
    }

    /**
     * Get headers used in curl calls.
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        return $headers;
    }

    /**
     * Make a GET request and respond with json or array.
     *
     * @param string $url
     *
     * @return array|object
     */
    public function httpGetRequest($url)
    {
        // Check for cache hit.
        if ($this->useCache()) {
            $cache = $this->getCache();

            if ($response = $cache->get($url)) {
                return $response;
            }
        }

        if (empty($this->client)) {
            $this->client = new Client();
        }

        $this->response = $this->client->request(
            'GET',
            $url,
            ['headers' => $this->getHeaders()]
        );
        $response = json_decode($this->response->getBody());

        if (!empty($cache)) {
            $cache->put($url, $response, $this->getCacheTtl());
        }

        return $response;
    }

    /**
     * Find entitiy.
     *
     * @param int $id
     *
     * @return object
     */
    public function find($id)
    {
        $this->id($id);

        return $this->httpGetRequest($this->buildUrl());
    }

    /**
     * Call get request.
     *
     * @param array|null $fields
     *
     * @return object
     */
    public function get()
    {
        return $this->httpGetRequest($this->buildUrl());
    }

    /**
     * Throw 404 exception.
     *
     * @return void
     */
    public function resourceNotFound()
    {
        throw new \Exception('Resource not found');
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale.
     *
     * @param string $locale
     *
     * @return self
     */
    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get current locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set website id.
     *
     * @param int $websiteId
     *
     * @return self
     */
    public function website($websiteId)
    {
        $this->websiteId = $websiteId;

        return $this;
    }

    /**
     * Get current website id.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->websiteId;
    }

    /**
     * Get extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Return storage url without '/' at the end.
     *
     * @return string
     */
    public function getStorageUrl()
    {
        return rtrim($this->storageUrl, '/');
    }

    /**
     * Set storage url.
     *
     * @param string $storageUrl
     *
     * @return self
     */
    public function setStorageUrl($storageUrl)
    {
        $this->storageUrl = $storageUrl;

        return $this;
    }

    /**
     * @return GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param GuzzleHttp\Client $client
     *
     * @return self
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Unset all call parameters.
     *
     * @return void
     */
    public function flush()
    {
        $this->id = null;
        $this->endpoint = null;
        $this->response = null;
    }

    /**
     * Check if cache can be used in this request, or set caching for request.
     *
     * Apply only to get requests.
     *
     * @param bool|null $caching
     *
     * @return self|bool
     */
    public function useCache($caching = null)
    {
        if (is_null($caching)) {
            return $this->useCache && $this->getCache();
        }

        $this->useCache = (bool) $caching;

        return $this;
    }

    /**
     * Use cache in current request.
     *
     * Apply only to get requests.
     *
     * @return self
     */
    public function withCache()
    {
        $this->useCache = true;

        return $this;
    }

    /**
     * Dont use cache in current request.
     *
     * Apply only to get requests.
     *
     * @return self
     */
    public function withouCache()
    {
        $this->useCache = true;

        return $this;
    }

    /**
     * Get cache driver.
     *
     * @return MonthlyCloud\Sdk\Cache\CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set cache driver.
     *
     * @param MonthlyCloud\Sdk\Cache\CacheInterface $cache
     *
     * @return self
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get cache ttl.
     *
     * @return int
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }

    /**
     * Set cache ttl (alias).
     *
     * @param int $cacheTtl
     *
     * @return self
     */
    public function setCacheTtl(int $cacheTtl)
    {
        return $this->cacheTtl($cacheTtl);
    }

    /**
     * Set cache ttl.
     *
     * @param int $cacheTtl
     *
     * @return self
     */
    public function cacheTtl(int $cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }
}
