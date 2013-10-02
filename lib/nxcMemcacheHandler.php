<?php
/**
 * @author vd@nxc.no
 * @date 25 Sep 2013
 * @copyright Copyright (C) 2011 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

/**
 * Adapter to store cache into Memcached
 */
class nxcMemcacheHandler
{
    /**
     * @return (Memcache)
     */
    private static function getMemcache()
    {
        static $memcache = false;
        if ( $memcache !== false )
        {
            return $memcache;
        }

        $memcache = new Memcache;
        $ini = eZINI::instance( 'memcache.ini' );
        $host = $ini->hasVariable( 'MemcacheSettings', 'Host' ) ? $ini->variable( 'MemcacheSettings', 'Host' ) : '127.0.0.1';
        $port = $ini->hasVariable( 'MemcacheSettings', 'Port' ) ? $ini->variable( 'MemcacheSettings', 'Port' ) : '11211';

        if ( !$memcache->pconnect( $host, $port ) )
        {
            throw new tbeRunTimeException( 'Could not connect to memcached ' . $host . ':' . $port );
        }

        return $memcache;
    }

    /**
     * @return (mixed)
     */
    public static function get( $key )
    {
        return self::getMemcache()->get( $key );
    }

    /**
     * @return (bool)
     */
    public static function set( $key, $content, $flag = false, $expire = false )
    {
        return self::getMemcache()->set( $key, $content, $flag, $expire );
    }

    /**
     * @return (bool)
     */
    public static function delete( $key )
    {
        return self::getMemcache()->delete( $key );
    }
}

?>
