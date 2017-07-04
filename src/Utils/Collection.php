<?php



namespace QCloudSDK\Utils;

use Illuminate\Support\Arr;

/**
 * Class Collection.
 */
class Collection extends \Illuminate\Support\Collection
{


    /**
     * Merge data.
     *
     * @param Collection|array $items
     *
     * @return array
     */
    public function merge($items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }

        return $this->all();
    }

    /**
     * To determine Whether the specified element exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return !is_null(Arr::get($this->items, $key));
    }

    /**
     * Set the item value.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        Arr::set($this->items, $key, $value);
    }

    /**
     * Retrieve item from Collection.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Remove item form Collection.
     *
     * @param string $key
     * @return Collection
     */
    public function forget($key)
    {
        Arr::forget($this->items, $key);
        return $this;
    }

}
