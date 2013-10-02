NXC Memcache
============

This extension is simplified distributed file system based on Memcache.
The main feature is to store some files by linux standard paths.

But there is no any difference between dirs and files.
Dir is just a file without content.
Thus files also may contain other subfiles or subdirs.

It has been implemented to provide an ablity to remove dirs and files recursively and to use it on cluster installations where several nodes must have rapid access to shared data.

Example
-------
    $m = new nxcMemcache( 'profiles/bill_gates.txt' );
    $m->store( 'some content' );

    var_dump( nxcMemcache::fetch( '/' )->getChild( 'profiles/bill_gates.txt' )->getContent() );
    var_dump( nxcMemcache::fetch( 'profiles' )->getChild( 'bill_gates.txt' )->getContent() );
    var_dump( nxcMemcache::fetch( 'profiles/bill_gates.txt' )->getContent() );

    // Purge just a file
    nxcMemcache::fetch( 'profiles/bill_gates.txt' )->delete();
    // Remove root dir thus remove all files
    nxcMemcache::fetch( '/' )->delete();
