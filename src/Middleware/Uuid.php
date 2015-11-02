<?php

namespace Psr7Middlewares\Middleware;

use Psr7Middlewares\Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware to generate UUID on each request.
 */
class Uuid
{
    const KEY = 'UUID';

    /**
     * @var string|false The header name to use 
     */
    protected $header = 'X-Uuid';

    /**
     * @var array The version and arguments needed to generate the Uuid
     */
    protected $version = [1];

    /**
     * Returns the Uuid instance.
     *
     * @param ServerRequestInterface $request
     *
     * @return \Rhumsaa\Uuid\Uuid|null
     */
    public static function getUuid(ServerRequestInterface $request)
    {
        return Middleware::getAttribute($request, self::KEY);
    }

    /**
     * Constructor. Set the version of UUID.
     *
     * @param int|null $version
     */
    public function __construct($version = null)
    {
        if ($version !== null) {
            call_user_func_array([$this, 'version'], func_get_args());
        }
    }

    /**
     * Choose the Uuid version.
     *
     * @param int $version 1, 3, 4 or 5
     *
     * @return self
     */
    public function version($version)
    {
        if (!in_array($version, [1, 3, 4, 5])) {
            throw new \InvalidArgumentException('Only 1, 3, 4 and 5 versions are available');
        }

        $this->version = func_get_args();

        return $this;
    }

    /**
     * Set whether the Uuid is stored in the header.
     * Set false to do not store.
     *
     * @param false|string $header
     *
     * @return self
     */
    public function header($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $uuid = $this->generateUuid();

        $request = Middleware::setAttribute($request, self::KEY, $uuid);

        if (!empty($this->header)) {
            $request = $request->withHeader($this->header, (string) $uuid);
        }

        return $next($request, $response);
    }

    /**
     * Generate the uuid with the current configuration.
     *
     * @return string
     */
    protected function generateUuid()
    {
        $args = $this->version;
        $fn = 'uuid'.array_shift($args);

        return call_user_func_array('Rhumsaa\Uuid\Uuid::'.$fn, $args);
    }
}