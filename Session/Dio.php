<?php

class Dio
{
    var $encoder = 'base64';

    var $id = '_saved-DIO_';

    /**
     * saves data as a hidden tag to transport it to next page.
     *
     * @param $data
     * @return string
     */
    public function savePost( $data )
    {
        $value = $this->encode( $data );
        $html  = "<input type='hidden' name='{$this->id}' value='{$value}'>";
        return $html;
    }

    /**
     * @return array|null|string
     */
    public function loadPost()
    {
        $input = null;
        if( $_POST[ $this->id ] ) {
            $input = $this->decode( $_POST[ $this->id ] );
        }
        return $input;
    }

    public function decode( $input )
    {
        $method = 'dencode' . ucwords( $this->encoder );
        return $this->$method( $input );
    }

    /**
     * @param $data
     * @return mixed
     */
    public function encode( $data )
    {
        $method = 'encode' . ucwords( $this->encoder );
        return $this->$method( $data );
    }

    /**
     * @param $data
     * @return string
     */
    protected function encodeBase64( $data )
    {
        $data = serialize( $data );
        $data = base64_encode( $data );
        return $data;
    }

    protected function decodeBase64( $input )
    {
        $input = base64_decode( $input );
        $input = unserialize( $input );
        return $input;
    }
}