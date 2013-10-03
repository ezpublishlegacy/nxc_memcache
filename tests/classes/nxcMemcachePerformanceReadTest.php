<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

class nxcMemcachePerformanceReadTest extends PHPUnit_Framework_TestCase
{
    const DIR = 'root/sub/dir/';
    const FILES_COUNT = 200000;

    public function testSetFiles()
    {
        $o = nxcMemcache::fetch( self::DIR . 'file_1' );
        if ( $o )
        {
            return;
        }

        for ( $i = 0; $i < self::FILES_COUNT; $i++ )
        {
            $o = new nxcMemcache( self::DIR . 'file_' . $i );
            $this->assertTrue( $o->store( str_repeat( 'content', 1000 ) ) );
        }
    }

    public function testGetFiles()
    {
        for ( $i = 0; $i < self::FILES_COUNT; $i++ )
        {
            $o = nxcMemcache::fetch( self::DIR . 'file_' . $i );
            $this->assertTrue( $o instanceof nxcMemcache );
            $this->assertEquals( str_repeat( 'content', 1000 ), $o->getContent() );
        }
    }
}

?>
