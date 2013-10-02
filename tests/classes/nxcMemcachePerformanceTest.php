<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

class nxcMemcachePerformanceTest extends PHPUnit_Framework_TestCase
{
    public function getIndexList()
    {
        $result = array();
        for ( $i = 0; $i < 1000; $i++ )
        {
            $result[] = array( 'file_' . $i );
        }

        for ( $i = 0; $i < 1000; $i++ )
        {
            $result[] = array( 'root/sub/dir/file_' . $i );
        }

        return $result;
    }

    /**
     * @dataProvider getIndexList
     */
    public function testSetFiles( $i )
    {
        $o = new nxcMemcache( $i );
        $this->assertTrue( $o->store( str_repeat( 'content', 1000 ) ) );
    }

    /**
     * @dataProvider getIndexList
     */
    public function testGetFiles(  $i )
    {
        $o = nxcMemcache::fetch( $i );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( str_repeat( 'content', 1000 ), $o->getContent() );
    }
}

?>
