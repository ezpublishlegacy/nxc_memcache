<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

/**
 * Class to store cache files in Memcached. Where main feature is to handle linux like paths.
 */
class nxcMemcache
{
    /**
     * @var (string)
     */
    protected $Path = false;

    /**
     * @var (int)
     */
    protected $ModificationTime = false;

    /**
     * Cached content
     *
     * @var (bytes)
     */
    protected $Content = false;

    /**
     * @var (array( name => path ))
     */
    protected $ChildrenPathList = array();

    /**
     * @var (array)
     */
    private static $CachedList = array();

    /**
     * @param (string)
     */
    public function __construct( $path )
    {
        $this->Path = self::trimPath( $path );
    }

    /**
     * @return (string)
     */
    protected static function trimPath( $path )
    {
        $path = trim( $path );
        if ( !$path )
        {
            $path = '/';
        }
        elseif ( $path[0] != '/' )
        {
            $path = '/' . $path;
        }

        $path = preg_replace( '/\/\/*/', '/', $path );
        $l = strlen( $path );
        if ( $l > 1 and $path[$l - 1] == '/' )
        {
            $path = trim( substr( $path, 0, $l - 1 ) );
        }

        return $path;
    }

    /**
     * @return (__CLASS__|false)
     */
    public static function fetch( $path = '' )
    {
        $path = self::trimPath( $path );
        if ( isset( self::$CachedList[$path] ) )
        {
            return self::$CachedList[$path];
        }

        $o = nxcMemcacheHandler::get( $path );
        $result = ( $o instanceof nxcMemcache ) ? $o : false;
        if ( $result )
        {
            self::$CachedList[$path] = $result;
        }

        return $result;
    }

    /**
     * @return (string)
     */
    public function getPath()
    {
        return $this->Path;
    }

    /**
     * @return (int)
     */
    public function getModificationtime()
    {
        return $this->ModificationTime;
    }

    /**
     * @return (this)
     */
    protected function addChild( $name )
    {
        $this->ChildrenPathList[basename( $name )] = $name;

        return $this;
    }

    /**
     * @return (bool)
     */
    public function hasChild( $name )
    {
        return isset( $this->ChildrenPathList[basename( $name )] );
    }

    /**
     * @return (this)
     */
    protected function deleteChild( $name )
    {
        unset( $this->ChildrenPathList[basename( $name )] );

        return $this;
    }

    /**
     * @return (array)
     */
    public function fetchChildrenList()
    {
        $result = array();
        foreach ( $this->ChildrenPathList as $key => $item )
        {
            $o = self::fetch( $item );
            if ( !$o )
            {
                continue;
            }

            $result[] = $o;
        }

        return $result;
    }

    /**
     * @return (__CLASS__)
     */
    public function getChild( $name )
    {
        $name = self::trimPath( $name );
        if ( !$name or $name == '/' )
        {
            return false;
        }

        $e = explode( '/', $name );
        if ( count( $e ) > 2 )
        {
            $o = self::fetch( $this->Path . '/' .$e[1] );
            if ( $o )
            {
                unset( $e[0] );
                unset( $e[1] );

                return $o->getChild( implode( '/', $e ) );
            }
        }

        $name = basename( $name );
        $path = isset( $this->ChildrenPathList[$name] ) ? $this->ChildrenPathList[$name] : false;

        return $path ? self::fetch( $path ) : false;
    }

    /**
     * @return (array)
     */
    public static function fetchTree( $root = '/' )
    {
        $o = self::fetch( $root );
        if ( !$o )
        {
            return false;
        }

        $list = $o->fetchChildrenList();
        foreach ( $list as $item )
        {
            $o->ChidlrenList[] = self::fetchTree( $item->getPath() );
        }

        return $o;
    }

    /**
     * @return (this)
     */
    protected function updateParent()
    {
        $name = $this->Path;
        $e = explode( '/', dirname( $this->Path ) );
        $i = 0;
        while ( $e )
        {
            $p = implode( '/', $e );
            if ( !$p )
            {
                break;
            }

            $o = self::fetch( $p );
            if ( !$o )
            {
                $o = new self( $p );
            }

            if ( !$o->hasChild( $name ) )
            {
                $o->addChild( $name )->update();
            }

            $name = $p;
            $e = ( $p and $p != '/' ) ? explode( '/', dirname( $p ) ) : array();
        }

        return $this;
    }

    /**
     * @return (bool)
     */
    public function store( $content = false )
    {
        $this->ModificationTime = time();
        $this->Content = $content;
        $this->updateParent();

        return $this->update();
    }

    /**
     * @return (bool)
     */
    protected function update()
    {
        self::$CachedList[$this->Path] = $this;

        return nxcMemcacheHandler::set( $this->Path, $this );
    }

    /**
     * @return (bytes)
     */
    public function getContent()
    {
        return $this->Content;
    }

    /**
     * @return (bool)
     */
    public function delete()
    {
        $list = $this->fetchChildrenList();
        foreach ( $list as $item )
        {
            $item->delete();
        }

        unset( self::$CachedList[$this->Path] );

        return nxcMemcacheHandler::delete( $this->Path );
    }

}

?>
