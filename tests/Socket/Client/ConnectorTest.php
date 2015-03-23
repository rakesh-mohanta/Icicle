<?php
namespace Icicle\Tests\Socket\Client;

use Exception;
use Icicle\Loop\Loop;
use Icicle\Socket\Client\Client;
use Icicle\Socket\Client\Connector;
use Icicle\Tests\TestCase;

class ConnectorTest extends TestCase
{
    const HOST_IPv4 = '127.0.0.1';
    const HOST_IPv6 = '[::1]';
    const PORT = 51337;
    
    protected $connector;
    
    public function createServer()
    {
        $host = self::HOST_IPv4;
        $port = self::PORT;
        
        $context = [];
        
        $context['socket'] = [];
        $context['socket']['bindto'] = "{$host}:{$port}";
        
        $context = stream_context_create($context);
        
        $socket = stream_socket_server(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );
        
        if (!$socket || $errno) {
            $this->fail("Could not create server {$host}:{$port}: [Errno: {$errno}] {$errstr}");
        }
        
        return $socket;
    }
    
    public function createServerIPv6()
    {
        $host = self::HOST_IPv6;
        $port = self::PORT;
        
        $context = [];
        
        $context['socket'] = [];
        $context['socket']['bindto'] = "{$host}:{$port}";
        
        $context = stream_context_create($context);
        
        $socket = stream_socket_server(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );
        
        if (!$socket || $errno) {
            $this->fail("Could not create server {$host}:{$port}: [Errno: {$errno}] {$errstr}");
        }
        
        return $socket;
    }
    
    public function setUp()
    {
        $this->connector = new Connector();
    }
    
    public function tearDown()
    {
        Loop::clear();
    }
    
    public function testConnect()
    {
        $server = $this->createServer();
        
        $promise = $this->connector->connect(self::HOST_IPv4, self::PORT);
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->isInstanceOf('Icicle\Socket\Client\Client'));
        
        $promise->done($callback, $this->createCallback(0));
        
        $promise->done(function (Client $client) {
            $this->assertSame($client->getLocalAddress(), self::HOST_IPv4);
            $this->assertSame($client->getRemoteAddress(), self::HOST_IPv4);
            $this->assertInternalType('integer', $client->getLocalPort());
            $this->assertSame($client->getRemotePort(), self::PORT);
        });
        
        Loop::run();
        
        fclose($server);
    }
    
    /**
     * @depends testConnect
     */
    public function testConnectIPv6()
    {
        $server = $this->createServerIPv6();
        
        $promise = $this->connector->connect(self::HOST_IPv6, self::PORT);
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->isInstanceOf('Icicle\Socket\Client\Client'));
        
        $promise->done($callback, $this->createCallback(0));
        
        $promise->done(function (Client $client) {
            $this->assertSame($client->getLocalAddress(), self::HOST_IPv6);
            $this->assertSame($client->getRemoteAddress(), self::HOST_IPv6);
            $this->assertInternalType('integer', $client->getLocalPort());
            $this->assertSame($client->getRemotePort(), self::PORT);
        });
        
        Loop::run();
        
        fclose($server);
    }
    
    /**
     * @medium
     * @depends testConnect
     */
    public function testConnectFailure()
    {
        $promise = $this->connector->connect('invalid.host', self::PORT, ['timeout' => 1]);
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->isInstanceOf('Icicle\Socket\Exception\FailureException'));
        
        $promise->done($this->createCallback(0), $callback);
        
        Loop::run();
    }
    
    /**
     * @medium
     * @depends testConnect
     */
    public function testConnectTimeout()
    {
        $promise = $this->connector->connect('8.8.8.8', 8080, ['timeout' => 1]);
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->isInstanceOf('Icicle\Socket\Exception\TimeoutException'));
        
        $promise->done($this->createCallback(0), $callback);
        
        Loop::run();
    }
}
