<?php

namespace haydenk\haydenk\SansThreatLevel;

use GuzzleHttp\ClientInterface;
use haydenk\SansThreatLevel\SansClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class SansClientTest extends TestCase
{
    public function testClientIsGuzzleClient()
    {
        $client = new SansClient();
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testSettingClientBaseUri()
    {
        $client = new SansClient(['base_uri' => 'http://localhost']);

        /** @var UriInterface $actualBaseUri */
        $actualBaseUri = $client->getConfig('base_uri');

        $this->assertNotEquals(SansClient::BASE_URI, "{$actualBaseUri}");
        $this->assertEquals('http://localhost', "{$actualBaseUri}");
    }
}
