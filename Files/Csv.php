<?php

class Csv
{
    /**
     * @var resource
     */
    protected $filePointer;

    /**
     * @var array
     */
    protected $header = array();

    /**
     * @var array
     */
    protected $map = array();
    
    /**
     * @var int
     */
    public $size = 10800;

    /**
     * @var int
     */
    public $count = 0;
    
    /**
     * @param resource $fp
     */
    public function __construct( $fp )
    {
        $this->filePointer = $fp;
        setlocale( LC_ALL, 'ja_JP.UTF-8' );
    }
    
    /**
     * gets CSV data from file.
     *
     * @throws RuntimeException
     * @return array|bool
     */
    public function readCsv()
    {
        if( !$this->filePointer ) {
            throw new RuntimeException( "Invalid CSV pointer" );
        }
        /*
         * get csv data
         */
        $data = fgetcsv( $this->filePointer, $this->size );
        $this->count ++;
        if( $data === false ) return array();
        if( $data === null  ) return array();
        if( !is_array( $data ) ) return array();
        if( count( $data ) == 1 && $data[0] === NULL ) return array(); // make it really empty.
        if( empty( $data ) ) return $data;
        
        /*
         * convert normal csv array to hashed-key using header column.
         */
        if( $this->header ) {
            $result = array();
            foreach( $data as $col => $val ) {
                $result[ $this->header[$col] ] = $val;
            }
            $data = $result;
        }
        /*
         * map csv to key/column array.
         */
        if( $this->map ) {
            $result = array();
            foreach( $this->map as $col => $key ) {
                if( !isset( $data[$col] ) ) {
                    throw new RuntimeException( "column not defined: " . $col );
                }
                $result[ $key ] = $data[ $col ];
            }
            $data = $result;
        }
        return $data;
    }

    /**
     * gets CSV data as header.
     *
     * @return array
     */
    public function readHeader()
    {
        $this->header = $this->readCsv( $this->size );
        return $this->header;
    }

    /**
     * @param array $map
     */
    public function setMap( $map )
    {
        $this->map = $map;
    }
}