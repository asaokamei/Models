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
    public $fp;

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
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        fwrite( $this->fp, $data );
        rewind( $this->fp );
        return $this;
    }
}