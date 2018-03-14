<?php

namespace haydenk\SansThreatLevel;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SansThreatLevelTest extends TestCase
{
    /**
     * @var Sans
     */
    private $sans;

    /**
     * @var MockObject|ClientInterface
     */
    private $mockClient;

    protected function setUp()
    {
        $this->sans = new Sans();

        $this->mockClient = $this->getMockBuilder(SansClient::class)->getMock();
        $this->mockClient
            ->method('request')
            ->with('get', '/infocon.txt')
            ->willReturn(new Response(200, [
                'Last-Modified' => 'Wed, 14 Mar 2018 01:30:13 GMT',
            ], 'green'));
    }

    public function testSetClient()
    {
        $this->sans->setClient(new SansClient());

        $this->assertInstanceOf(SansClient::class, $this->sans->getClient());
        $this->assertInstanceOf(ClientInterface::class, $this->sans->getClient());
    }

    /**
     * @throws GuzzleException
     */
    public function testGetLastModified()
    {
        $this->sans->setClient($this->mockClient);
        $this->sans->fetch();

        $this->assertInstanceOf(\DateTimeInterface::class, $this->sans->getLastModified());
        $this->assertEquals('2018-03-14', $this->sans->getLastModified()->format('Y-m-d'));
    }

    /**
     * @throws GuzzleException
     */
    public function testGetThreatLevel()
    {
        $this->sans->setClient($this->mockClient);
        $this->sans->fetch();

        $this->assertInternalType('string', $this->sans->getThreatLevel());
        $this->assertEquals('green', $this->sans->getThreatLevel());
    }

    /**
     * @throws GuzzleException
     */
    public function testGetHash()
    {
        $this->sans->setClient($this->mockClient);
        $this->sans->fetch();

        $expectedHash = hash('sha256', serialize([
            'last_modified' => new \DateTimeImmutable('Wed, 14 Mar 2018 01:30:13 GMT'),
            'threat_level' => 'green',
        ]));

        $this->assertInternalType('string', $this->sans->getHash());
        $this->assertEquals($expectedHash, $this->sans->getHash());
    }

    /**
     * @dataProvider fetchDataSource
     * @param bool $expected
     */
    public function testFetch($expected)
    {
        if (false === $expected) {
            /** @var Request $mockRequest */
            $mockRequest = $this->getMockBuilder(Request::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->mockClient
                ->method('request')
                ->with('get', '/infocon.txt')
                ->willThrowException(new ClientException('404 Not Found', $mockRequest));
        }

        try {
            $this->sans->setClient($this->mockClient);
            $return = $this->sans->fetch();

            $this->assertInstanceOf(Sans::class, $return);
        } catch (GuzzleException $e) {
            $this->assertInstanceOf(ClientException::class, $e);
            $this->assertEquals('404 Not Found', $e->getMessage());
        }
    }

    public function fetchDataSource()
    {
        return [
            [true],
            [false],
        ];
    }
}
