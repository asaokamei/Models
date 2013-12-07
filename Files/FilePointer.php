<?php

class FilePointer
{
    /**
     * @var string
     */
    public $file;
    
    /**
     * @var resource
     */
    public $fp = null;

    /**
     * @var bool
     */
    public $lock = false;

    /**
     * @param null   $file
     * @param string $mode
     */
    public function __construct( $file=null, $mode='rb' )
    {
        if( $file ) {
            $this->open( $file, $mode );
        }
    }

    /**
     * @return resource
     */
    public function fp()
    {
        return $this->fp;
    }
    
    /**
     * open a file. 
     * 
     * @param string $file
     * @param string $mode
     * @return $this
     * @throws RuntimeException
     */
    public function open( $file, $mode='rb' )
    {
        if( !file_exists( $file ) ) {
            throw new RuntimeException( "cannot find file: " . $file );
        }
        $this->file = $file;
        $this->fp   = fopen( $file, $mode );
        return $this;
    }

    /**
     * open a file with lock. 
     * 
     * @param string $file
     * @param string $mode
     * @return $this
     * @throws RuntimeException
     */
    public function openWithLock( $file, $mode='r+' )
    {
        $this->open( $file, $mode );
        if( !flock( $this->fp, LOCK_EX ) ) {
            throw new RuntimeException( 'cannot lock file: ' . $file );
        }
        rewind( $this->fp );
        $this->lock = true;
        return $this;
    }

    /**
     * close file pointer. 
     * unlocks the file if locked.
     * 
     * @return $this
     */
    public function close()
    {
        if( !$this->fp ) return $this;
        if( $this->lock ) {
            fflush( $this->fp );
            flock( $this->fp, LOCK_UN );
        }
        fclose( $this->fp );
        $this->fp = null;
        return $this;
    }
    
    /**
     * re-opens file contents as UTF-8. 
     * may use a lot of memory.
     * 
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function reOpenAsUtf8( $from, $to='UTF-8' )
    {
        // get all contents.
        rewind( $this->fp );
        $data = stream_get_contents( $this->fp );
        fclose( $this->fp );
        
        // convert to the new charset and store it in the memory. 
        $data = mb_convert_encoding( $data, $to, $from );
        $this->fp   = fopen( 'php://temp', 'r+' );
        fwrite( $this->fp, $data );
        rewind( $this->fp );
        return $this;
    }

    /**
     * re-opens as a temp file as UTF-8 contents. 
     * may slower but use less memory compared to reOpenAsUtf8.
     * 
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function tempAsUtf8( $from, $to='UTF-8' )
    {
        rewind( $this->fp );
        $tempFp   = tmpfile();
        while( $text = fgets( $this->fp ) ) {
            fwrite( $tempFp, mb_convert_encoding( $tempFp, $to, $from ) );
        }
        fclose( $this->fp );
        $tempFp->fp = $tempFp;
        return $this;
    }

    /**
     * 
     */
    public function __destruct()
    {
        $this->close();
    }
}