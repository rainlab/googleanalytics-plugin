<?php namespace RainLab\GoogleAnalytics\Classes;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Illuminate\Contracts\Cache\Repository;
use InvalidArgumentException;
use Exception;

class CacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $repository;

    /**
     * @var \Psr\Cache\CacheItemInterface[]
     */
    protected $deferred = [];

    /**
     * @param \Illuminate\Contracts\Cache\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $this->validateKey($key);

        if ($this->repository->has($key)) {
            return new CacheItem($key, unserialize($this->repository->get($key)), true);
        }
        else {
            return new CacheItem($key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = array())
    {
        return array_combine($keys, array_map(function ($key) {
            return $this->getItem($key);
        }, $keys));
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $this->validateKey($key);

        return $this->repository->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        try {
            /* @var \Illuminate\Contracts\Cache\Store $store */
            $store = $this->repository;
            $store->flush();
        }
        catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $this->validateKey($key);

        return $this->repository->forget($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        // Validating all keys first.
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        $success = true;

        foreach ($keys as $key) {
            $success = $success && $this->deleteItem($key);
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $expiresInMinutes = null;

        if ($item instanceof CacheItem) {
            $expiresInMinutes = $item->getTTL();
        }

        try {
            if (is_null($expiresInMinutes)) {
                $this->repository->forever($item->getKey(), serialize($item->get()));
            }
            else {
                $this->repository->put($item->getKey(), serialize($item->get()), $expiresInMinutes);
            }
        }
        catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $success = true;

        foreach ($this->deferred as $item) {
            $success = $success && $this->save($item);
        }

        $this->deferred = [];

        return $success;
    }

    /**
     * @param string $key
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function validateKey($key)
    {
        if (!is_string($key) || preg_match('#[{}\(\)/\\\\@:]#', $key)) {
            throw new InvalidArgumentException();
        }
    }
}
