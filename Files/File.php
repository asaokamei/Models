<?php

class File
{
    /**
     * get file statistics. 
     * 
     * @param $file
     * @return array|bool
     */
    static function stat( $file )
    {
        $stat = FALSE;
        if( file_exists( $file ) ) {
            $stat = lstat( $file );
            $stat[ 'acc_time' ] = date( 'Y-m-d H:i:s', $stat[ 'atime' ] );
            $stat[ 'mod_time' ] = date( 'Y-m-d H:i:s', $stat[ 'mtime' ] );
            $stat[ 'chg_time' ] = date( 'Y-m-d H:i:s', $stat[ 'ctime' ] );
            $stat[ 'size_kb' ]  = sprintf( '%0.0f', $stat[ 'size' ] / 1024 );
            $stat[ 'size_mb' ]  = sprintf( '%0.2f', $stat[ 'size' ] / 1024 / 1024 );
            $stat[ 'size_gb' ]  = sprintf( '%0.2f', $stat[ 'size' ] / 1024 / 1024 / 1024 );
        }
        return $stat;
    }

    /**
     * @param string $file
     * @param string $mode
     * @return resource
     * @throws RuntimeException
     */
    static function open( $file, $mode='rb' )
    {
        if( !file_exists( $file ) ) {
            throw new RuntimeException( "cannot find file: " . $file );
        }
        return fopen( $file, $mode );
    }

    /**
     * @param string $file
     * @param string $mode
     * @throws RuntimeException
     */
    static function openWithLock( $file, $mode='rb+' )
    {
        $fp = self::open( $file, $mode );
        if( !flock( $fp, LOCK_EX ) ) {
            throw new RuntimeException( 'cannot lock file: ' . $file );
        }
        rewind( $fp );
    }
    
    static function closeWithLock( $fp )
    {
        fflush( $fp );
        flock( $fp, LOCK_UN );
        fclose( $fp );
    }
}