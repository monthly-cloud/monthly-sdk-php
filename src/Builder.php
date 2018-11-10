<?php

namespace MonthlyCloud\Sdk;

use GuzzleHttp\Client;

class Builder
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $include;

    /**
     * @var string
     */
    private $sort;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var integer
     */
    private $pageSize;

    /**
     * Api key to connect cloud.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Guzzle client.
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Guzzle response
     *
     * @var GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * Api url.
     *
     * @var string
     */
    private $apiUrl;


    public function __construct($accessToken = '', $apiUrl = '')
    {
        $this->apiUrl = $apiUrl;
        $this->accessToken = $accessToken;
    }

    /**
     * Set endpoint.
     *
     * Ex.: endpoint("property/1"), endpoint("property/").
     *
     * @param string $endpoint
     * @return self
     */
    public function endpoint($endpoint = null)
    {
        $this->flush();

        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Apply filters used during listing (index) some resource.
     *
     * Ex.: filter('query', 'test') would result in adding filter['query']=test to call.
     *
     * @param string $name
     * @param string $value
     * @return self
     */
    public function filter($name, $value)
    {
        $this->filters[$name] = $value;

        return $this;
    }

    /**
     * Include other resources.
     *
     * Ex.: ['comments'] would pass ?include=comments call.
     *
     * @param array|string $include
     * @return self
     */
    public function with($include)
    {
        if (is_array($include)) {
            $this->include = $include;
        }
        if (is_string($include)) {
            $this->include = [$include];
        }
        $this->include = $include;

        return $this;
    }

    /**
     * Apply sorting.
     *
     * Ex.: sort('-id') would order by id desc.
     *
     * @param string $sort
     * @return self
     */
    public function sort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Limit fields loaded in response.
     *
     * @param array $fields
     * @return self
     */
    public function fields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * pageSize alias.
     *
     * @param integer $size
     * @return self
     */
    public function limit($size)
    {
        return $this->pageSize($size);
    }

    /**
     * Limit page size.
     *
     * @param integer $size
     * @return self
     */
    public function pageSize($size)
    {
        $this->pageSize = $size;

        return $this;
    }

    public function buildUrl()
    {
        $url = $this->getApiUrl();
        if ($endpoint = $this->getEndpoint()) {
            $url .= $endpoint;
        }
        if ($id = $this->getId()) {
            $url .= '/'.$id;
        }
        $parameters = [];
        if ($include = $this->getInclude()) {
            if (is_array($include)) {
                $include = implode(',', $include);
            }
            $parameters['include'] = $include;
        }
        if ($filters = $this->getFilters()) {
            if (!empty($filters)) {
                $parameters['filter'] = $filters;
            }
        }
        if ($pageSize = $this->getPageSize()) {
            if (!empty($pageSize)) {
                $parameters['page'] = ['size' => $pageSize];
            }
        }
        if ($parameters) {
            $url .= '?'.http_build_query($parameters);
        }
        return $url;
    }

    /**
     * Get headers used in api calls.
     *
     * @return array
     */
    private function getHeaders()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->getAccessToken(),
        ];

        return $headers;
    }

    /**
     * Make a GET request and respond with json decoded decoded to array.
     *
     * @param string $url
     * @return array
     */
    public function httpGetRequest($url)
    {
        if (empty($this->client)) {
            $this->client = new Client(['verify' => false]);
        }

        $this->response = $this->client->request(
            'GET',
            $url,
            ['headers' => $this->getHeaders()]
        );

        return json_decode($this->response->getBody());
    }

    /**
     * Find entitiy.
     *
     * @param integer $id
     * @return object
     */
    public function find($id)
    {
        $this->id($id);

        return $this->httpGetRequest($this->buildUrl());
    }

    public function get($fields = null)
    {
        if (!empty($fields)) {
            $this->fields($fields);
        }

        return $this->httpGetRequest($this->buildUrl());
    }

    /**
     * Get first item from get response.
     *
     * @return stdClass|false
     */
    public function first()
    {
        $response =  $this->httpGetRequest($this->buildUrl());

        if (empty($response->data[0])) {
            return false;
        }

        return $response->data[0];
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return integer
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set access token.
     *
     * @param string $accessToken
     * @return self
     */
    public function accessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     *
     * @return self
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
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
        $this->filters = [];
        $this->include = null;
        $this->id = null;
        $this->endpoint = null;
        $this->sort = null;
        $this->fields = null;
        $this->pageSize = null;
        $this->response = null;
    }
}
