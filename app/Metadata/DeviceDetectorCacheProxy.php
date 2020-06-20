<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Metadata;

use App\Configs;
use App\Logs;
use Cache;

/**
 * Class used as Proxy between Device Detector and Laravel system cache.
 *
 * Class DeviceDetectorCacheProxy
 * @package App\Proxy
 */
class DeviceDetectorCacheProxy implements \DeviceDetector\Cache\Cache
{
    /** @var \Illuminate\Contracts\Cache\Repository|\Illuminate\Contracts\Cache\Store $driver */
    protected $driver;

    /**
     * DeviceDetectorCacheProxy constructor.
     *
     * @param null $driver Driver used in Laravel
     */
    public function __construct($driver = null)
    {
      $a = new \Illuminate\Cache\CacheManager();
        $this->driver = $a.driver($driver);
    }

    /**
     * Fetch item from Cache
     *
     * @param $id
     * @return mixed
     */
    public function fetch($id)
    {
        return $this->driver->get($id);
    }

    /**
     * Check is cache has $id.
     * @param $id
     */
    public function contains($id)
    {
        $this->driver->has($id);
    }

    /**
     * Save data to cache.
     *
     * @param $id
     * @param $data
     * @param int $lifeTime
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $this->driver->add($id, $data, $lifeTime);
    }

    /**
     * Delete key from Cache
     *
     * @param $id
     */
    public function delete($id)
    {
        $this->driver->forget($id);
    }

    /**
     * Remove all data from cache
     */
    public function flushAll()
    {
        $this->driver->flush();
    }

    /**
     * Returns current used driver
     *
     * @return \Illuminate\Contracts\Cache\Repository|\Illuminate\Contracts\Cache\Store
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
