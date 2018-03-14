<?php

namespace haydenk\haydenk\SansThreatLevel;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use haydenk\SansThreatLevel\SansClient;
use haydenk\SansThreatLevel\SansThreatLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SansThreatLevelTest extends TestCase
{
    /**
     * @var SansThreatLevel
     */
    private $sansThreatLevel;

    /**
     * @var MockObject|ClientInterface
     */
    private $mockClient;

    protected function setUp()
    {
        $this->sansThreatLevel = new SansThreatLevel();

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
        $this->sansThreatLevel->setClient(new SansClient());

        $this->assertInstanceOf(SansClient::class, $this->sansThreatLevel->getClient());
        $this->assertInstanceOf(ClientInterface::class, $this->sansThreatLevel->getClient());
    }

    /**
     * @throws GuzzleException
     */
    public function testGetLastModified()
    {
        $this->sansThreatLevel->setClient($this->mockClient);
        $this->sansThreatLevel->fetch();

        $this->assertInstanceOf(\DateTimeInterface::class, $this->sansThreatLevel->getLastModified());
        $this->assertEquals('2018-03-14', $this->sansThreatLevel->getLastModified()->format('Y-m-d'));
    }

    /**
     * @throws GuzzleException
     */
    public function testGetThreatLevel()
    {
        $this->sansThreatLevel->setClient($this->mockClient);
        $this->sansThreatLevel->fetch();

        $this->assertInternalType('string', $this->sansThreatLevel->getThreatLevel());
        $this->assertEquals('green', $this->sansThreatLevel->getThreatLevel());
    }

    /**
     * @throws GuzzleException
     */
    public function testGetHash()
    {
        $this->sansThreatLevel->setClient($this->mockClient);
        $this->sansThreatLevel->fetch();

        $expectedHash = hash('sha256', serialize([
            'last_modified' => new \DateTimeImmutable('Wed, 14 Mar 2018 01:30:13 GMT'),
            'threat_level' => 'green',
        ]));

        $this->assertInternalType('string', $this->sansThreatLevel->getHash());
        $this->assertEquals($expectedHash, $this->sansThreatLevel->getHash());
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
            $this->sansThreatLevel->setClient($this->mockClient);
            $return = $this->sansThreatLevel->fetch();

            $this->assertInstanceOf(SansThreatLevel::class, $return);
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
