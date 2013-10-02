<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

class nxcMemcacheHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $this->assertFalse( nxcMemcacheHandler::get( 'wrongkey' ) );
    }

    public function testSet()
    {
        $this->assertTrue( nxcMemcacheHandler::set( 'key', 'content' ) );
    }

    public function testGet()
    {
        $this->assertTrue( nxcMemcacheHandler::set( 'key', 'content' ) );
        $this->assertEquals( 'content', nxcMemcacheHandler::get( 'key' ) );
    }

    public function testDelete()
    {
        $this->assertTrue( nxcMemcacheHandler::set( 'key', 'content' ) );
        $this->assertTrue( nxcMemcacheHandler::delete( 'key' ) );
        $this->assertFalse( nxcMemcacheHandler::get( 'key' ) );
    }

    public function testSetObject()
    {
        $o = new nxcMemcacheHandlerTest();
        $this->assertTrue( nxcMemcacheHandler::set( 'key', $o ) );
    }

    public function testGetObject()
    {
        $o = new nxcMemcacheHandlerTest();
        $this->assertTrue( nxcMemcacheHandler::get( 'key', $o ) instanceof nxcMemcacheHandlerTest );
    }

}

?>
