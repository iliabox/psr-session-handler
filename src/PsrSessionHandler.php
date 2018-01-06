<?php

namespace PsrSessionHandler;

use SessionHandlerInterface;
use Psr\Cache\CacheItemPoolInterface;

class PsrSessionHandler implements SessionHandlerInterface
{
    const PREFIX = 'session.';

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param CacheItemPoolInterface $cache
     * @param integer $lifetime
     * @param string $prefix
     */
    public function __construct(CacheItemPoolInterface $cache, int $lifetime, string $prefix = self::PREFIX)
    {
        $this->cache    = $cache;
        $this->lifetime = $lifetime;
        $this->prefix   = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        $key = $this->getKey($sessionId);

        return $this->cache->hasItem($key) ? $this->cache->getItem($key)->get() : '';
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $value)
    {
        $key = $this->getKey($sessionId);

        $item = $this->cache->getItem($key);
        $item->set((string)$value);
        $item->expiresAfter($this->lifetime);

        return $this->cache->save($item);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $key = $this->getKey($sessionId);

        return $this->cache->deleteItem($key);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * Get key
     *
     * @param string $sessionId session id
     * @return string
     */
    protected function getKey(string $sessionId) : string
    {
        return $this->prefix . $sessionId;
    }

}
