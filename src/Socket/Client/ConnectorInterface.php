<?php
namespace Icicle\Socket\Client;

interface ConnectorInterface
{
    /**
     * @param   string $host Hostname or IP address.
     * @param   int $port Port number.
     * @param   array $options {
     *     @var string $protocol The protocol to use, such as tcp, udp, s3, ssh. Defaults to tcp.
     *     @var int|float $timeout Number of seconds until connection attempt times out. Defaults to 10 seconds.
     *     @var string $cn Host name used to verify certificate.
     *     @var bool $allow_self_signed Set to true to allow self-signed certificates. Defaults to false.
     *     @var int $verify_depth Max levels of certificate authorities the verifier will transverse. Defaults to 10.
     *     @var string cafile Path to bundle of root certificates to verify against.
     * }
     *
     * @return  PromiseInterface Fulfilled with a LocalClient object once the connection is established.
     *
     * @resolve ClientInterface
     *
     * @reject  FailureException If connecting fails.
     * @reject  InvalidArgumentException If a CA file does not exist at the path given.
     *
     * @see     http://curl.haxx.se/docs/caextract.html Contains links to download bundle of CA Root Certificates that
     *          may be used for the cafile option if needed.
     */
    public function connect($host, $port, array $options = null);
}
