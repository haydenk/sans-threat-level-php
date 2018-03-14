<?php

namespace haydenk\SansThreatLevel;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class SansThreatLevel
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var \DateTimeInterface
     */
    private $lastModified;

    /**
     * @var string
     */
    private $threatLevel;

    /**
     * @var string
     */
    private $hash;

    /**
     * @param ClientInterface $client
     * @return SansThreatLevel
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return string
     */
    public function getThreatLevel()
    {
        return $this->threatLevel;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $uri
     * @return $this
     * @throws GuzzleException
     */
    public function fetch($uri = '/infocon.txt')
    {
        $response = $this->client->request('get', $uri);

        if ($response->hasHeader('Last-Modified')) {
            $this->lastModified = new \DateTimeImmutable($response->getHeaderLine('Last-Modified'));
        }

        $this->threatLevel = strtolower($response->getBody()->getContents());
        $this->hash = $this->generateHash();

        return $this;
    }

    private function generateHash()
    {
        return hash('sha256', serialize([
            'last_modified' => $this->lastModified,
            'threat_level' => $this->threatLevel,
        ]));
    }
}
