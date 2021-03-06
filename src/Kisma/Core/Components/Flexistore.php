<?php
/**
 * This file is part of Kisma(tm).
 *
 * Kisma(tm) <https://github.com/kisma/kisma>
 * Copyright 2009-2014 Jerry Ablan <jerryablan@gmail.com>
 *
 * Kisma(tm) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Kisma(tm) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kisma(tm).  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Kisma\Core\Components;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\XcacheCache;
use Kisma\Core\Enums\CacheTypes;

/**
 * Wrapper around doctrine/cache
 */
class Flexistore
{
    //*************************************************************************
    //	Constants
    //*************************************************************************

    /**
     * @type string The namespace containing our store provider
     */
    const STORE_NAMESPACE = 'Doctrine\\Common\\Cache\\';
    /**
     * @type int The number of seconds to keep cached items. Defaults to 0, or forever.
     */
    const DEFAULT_CACHE_TTL = 0;
    /**
     * @type string The suffix for the cache files
     */
    const DEFAULT_CACHE_EXTENSION = '.flexistore.bin';

    //*************************************************************************
    //	Members
    //*************************************************************************

    /**
     * @var string The ID of this store. Used as a prefix for cache keys
     */
    protected $_storeId = null;
    /**
     * @var CacheProvider|RedisCache|XcacheCache
     */
    protected $_store = null;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param string $type
     * @param string $namespace
     *
     * @throws \InvalidArgumentException
     * @internal param string $storeId
     */
    public function __construct( $type = CacheTypes::ARRAY_CACHE, $namespace = null )
    {
        if ( !CacheTypes::contains( $type ) )
        {
            throw new \InvalidArgumentException( 'The $type "' . $type . '" is not valid.' );
        }

        $_class = static::STORE_NAMESPACE . $type . 'Cache';

        $_mirror = new \ReflectionClass( $_class );
        $this->_store = $_mirror->getConstructor() ? $_mirror->newInstanceArgs( $this->_getCacheTypeArguments( $type ) ) : $_mirror->newInstance();

        if ( null !== $namespace )
        {
            $this->_store->setNamespace( $namespace );
        }

        $this->_initializeCache( $type );
    }

    /**
     * @param string $type
     *
     * @return array|null
     */
    protected function _getCacheTypeArguments( $type )
    {
        switch ( $type )
        {
            case CacheTypes::FILE_SYSTEM:
            case CacheTypes::PHP_FILE:
                do
                {
                    $_directory = sys_get_temp_dir() . '/kfs.' . uniqid();
                }
                while ( is_dir( $_directory ) );

                return array( $_directory, static::DEFAULT_CACHE_EXTENSION );
        }

        return array();
    }

    /**
     * @param string $type
     *
     * @throws \LogicException
     */
    protected function _initializeCache( $type )
    {
        switch ( $type )
        {
            case CacheTypes::REDIS:
                $_redis = new \Redis();

                if ( false === $_redis->pconnect( '127.0.0.1' ) )
                {
                    throw new \LogicException( 'Cannot connect to redis server @ 127.0.0.1' );
                }

                $this->_store->setRedis( $_redis );
                break;
        }
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id           The id of the cache entry to fetch
     * @param mixed  $defaultValue The default value if $id not found
     * @param bool   $remove
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function get( $id, $defaultValue = null, $remove = false )
    {
        if ( false === ( $_data = $this->_store->fetch( $id ) ) )
        {
            if ( !$remove )
            {
                $this->_store->save( $id, $_data = $defaultValue );
            }
        }
        elseif ( $remove )
        {
            $this->_store->delete( $id );
        }

        return $_data;
    }

    /**
     * Puts data into the cache.
     *
     * $id can be specified as an array of key-value pairs: array( 'alpha' => 'xyz', 'beta' => 'qrs', 'gamma' => 'lmo', ... )
     *
     *
     * @param string|array $id       The cache id or array of key-value pairs
     * @param mixed        $data     The cache entry/data.
     * @param int          $lifeTime The cache lifetime. Sets a specific lifetime for this cache entry. Defaults to 0, or "never expire"
     *
     * @return boolean|boolean[] TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function set( $id, $data = null, $lifeTime = self::DEFAULT_CACHE_TTL )
    {
        if ( is_array( $id ) && null === $data )
        {
            $_result = array();

            foreach ( $id as $_key => $_value )
            {
                $_result[$_key] = $this->_store->save( $_key, $_value, $lifeTime );
            }

            return $_result;
        }

        return $this->_store->save( $id, $data, $lifeTime );
    }

    /**
     * Pass-thru for other cache methods to avoid extending CacheProvider
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws BadMethodException
     * @return mixed
     */
    public function __call( $name, $arguments )
    {
        if ( method_exists( $this->_store, $name ) )
        {
            return call_user_func_array( array( $this->_store, $name ), $arguments );
        }
    }

    /**
     * @return CacheProvider
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

}
