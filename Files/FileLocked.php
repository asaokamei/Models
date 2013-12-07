<?php

class FileLocked extends FilePointer
{
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
     * open a file with lock. 
     * 
     * @param string $file
     * @param string $mode
     * @return $this
     * @throws RuntimeException
     */
    public function open( $file, $mode='r+' )
    {
        parent::open( $file, $mode );
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
     * 
     */
    public function __destruct()
    {
        $this->close();
    }
}