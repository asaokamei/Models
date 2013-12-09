<?php

class Messages
{
    var $error = false;
    var $message = '';

    /**
     * @param string $message
     */
    public function message( $message )
    {
        $this->message = $message;
    }
    /**
     * @param string $message
     */
    public function error( $message )
    {
        $this->error = true;
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->error;
    }

    /**
     * @param null $message
     * @return string
     */
    public function display( $message=null )
    {
        $text = '';
        if( $this->error ) {
            $text .= "Error:\n";
        }
        $text .= $message;
        $text .= $this->message;
        return $text;
    }
}