<?php
/**
 * @author VaL <vd@nxc.no>
 * @copyright Copyright (C) 2013 NXC AS.
 * @license GNU GPL v2
 * @package nxc_memcache
 */

class nxcMemcacheTest extends PHPUnit_Framework_TestCase
{
    public function testFetchWrong()
    {
        $this->assertFalse( nxcMemcache::fetch( 'wrong/path/to/file.txt' ) );
    }

    public function testEmpty()
    {
        $o = new nxcMemcache( 'path/to/empty/file.txt' );

        $this->assertFalse( $o->getContent() );
        $this->assertFalse( $o->getModificationTime() );
    }

    public function getPaths()
    {
        return array(
            array(
                'path/to/file',
                '/path/to/file',
            ),
            array(
                '/path/to/file',
                '/path/to/file',
            ),
            array(
                'path to file',
                '/path to file',
            ),
            array(
                '//path to file',
                '/path to file',
            ),
            array(
                'path to file/txt//png////////',
                '/path to file/txt/png',
            ),
            array(
                'path to file/txt//png/////////',
                '/path to file/txt/png',
            ),
            array(
                'path to/ file///txt//png//',
                '/path to/ file/txt/png',
            ),
            array(
                'path to//// file///txt//png /',
                '/path to/ file/txt/png',
            ),
            array(
                '//////////path to file///txt\png /',
                '/path to file/txt\png',
            ),
            array(
                '/////////path to file//txt\png',
                '/path to file/txt\png',
            ),
            array(
                '',
                '/',
            ),
            array(
                '     ',
                '/',
            ),
            array(
                '    ',
                '/',
            ),
            array(
                '  /  ',
                '/',
            ),

        );
    }

    /**
     * @dataProvider getPaths
     */
    public function testPath( $v, $c )
    {
        $o = new nxcMemcache( $v );
        $this->assertEquals( $c, $o->getPath() );
    }

    public function testRootStore()
    {
        $o = new nxcMemcache( 'file.txt' );
        $o->store( 'content' );

        $o = nxcMemcache::fetch( 'file.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content', $o->getContent() );

        $o = nxcMemcache::fetch( '/file.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content', $o->getContent() );

        $o = nxcMemcache::fetch( '//file.txt /' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content', $o->getContent() );

        $o = nxcMemcache::fetch();
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertTrue( $o->getChild( 'file.txt' ) instanceof nxcMemcache );
        $this->assertTrue( $o->getChild( '/file.txt' ) instanceof nxcMemcache );
        $this->assertTrue( $o->getChild( '//file.txt' ) instanceof nxcMemcache );
        $this->assertEquals( 'content', $o->getChild( 'file.txt' )->getContent() );
    }

    public function testRootChildStore()
    {
        $o = new nxcMemcache( 'file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( '/file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = new nxcMemcache( 'file2.txt' );
        $o->store( 'content2' );

        $o = nxcMemcache::fetch( '//////file2.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content2', $o->getContent() );

        $o = new nxcMemcache( 'file3.txt' );
        $o->store( 'content3' );

        $o = nxcMemcache::fetch( 'file3.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content3', $o->getContent() );

        $o = nxcMemcache::fetch( '/' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );
        $this->assertEquals( 'content2', $o->getChild( 'file2.txt' )->getContent() );
        $this->assertEquals( 'content3', $o->getChild( 'file3.txt' )->getContent() );

    }

    public function testPathChildStore()
    {
        $o = new nxcMemcache( 'dir/file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( 'dir/file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = nxcMemcache::fetch( 'dir' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( '/dir' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( '/' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( false, $o->getChild( 'dir' )->getContent() );
    }

    public function testPathSubChildStore()
    {
        $o = new nxcMemcache( 'dir/subdir/file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( 'dir/subdir/file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = nxcMemcache::fetch( 'dir/subdir/' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( '/dir' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( false, $o->getChild( 'subdir' )->getContent() );

        $o = nxcMemcache::fetch( '/' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( false, $o->getContent() );
        $this->assertEquals( false, $o->getChild( 'dir' )->getContent() );
        $this->assertEquals( 'content1', $o->getChild( 'dir' )->getChild( 'subdir' )->getChild( 'file1.txt' )->getContent() );
    }

    public function testDeleteFile()
    {
        $o = new nxcMemcache( 'file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( 'file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = nxcMemcache::fetch();
        $this->assertTrue( $o->getChild( 'file1.txt' ) instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );
        $o->getChild( 'file1.txt' )->delete();

        $o = nxcMemcache::fetch( 'file1.txt' );
        $this->assertFalse( $o );

        $o = nxcMemcache::fetch();
        $this->assertEquals( false, $o->getChild( 'file1.txt' ) );
    }

    public function testDeleteDir()
    {
        $o = new nxcMemcache( '/dir/file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( 'dir/file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = nxcMemcache::fetch();
        $this->assertTrue( $o->getChild( 'dir' )->getChild( 'file1.txt' ) instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( 'dir' )->getChild( 'file1.txt' )->getContent() );
        $o->getChild( 'dir' )->delete();

        $o = nxcMemcache::fetch( 'dir/file1.txt' );
        $this->assertFalse( $o );

        $o = nxcMemcache::fetch();
        $this->assertEquals( false, $o->getChild( 'dir' ) );
    }

    public function testDeleteRoot()
    {
        $o = new nxcMemcache( '/dir/file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch( 'dir/file1.txt' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getContent() );

        $o = nxcMemcache::fetch();
        $this->assertTrue( $o->getChild( 'dir' )->getChild( 'file1.txt' ) instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( 'dir' )->getChild( 'file1.txt' )->getContent() );
        $o->delete();

        $o = nxcMemcache::fetch( 'dir/file1.txt' );
        $this->assertFalse( $o );

        $o = nxcMemcache::fetch();
        $this->assertEquals( false, $o );
    }

    public function testGetChild()
    {
        $o = new nxcMemcache( 'dir/sub/under/file1.txt' );
        $o->store( 'content1' );

        $o = nxcMemcache::fetch();
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( '/dir/sub/under/file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( 'dir' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( '/sub/under/file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( 'dir/sub' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( 'under/file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( 'dir/sub/under' );
        $this->assertTrue( $o instanceof nxcMemcache );
        $this->assertEquals( 'content1', $o->getChild( 'file1.txt' )->getContent() );

        $o = nxcMemcache::fetch( 'dir/sub/under/wrong' );
        $this->assertFalse( $o instanceof nxcMemcache );
    }
}

?>
