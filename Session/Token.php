<?php

class ValidationBadTokenException extends RuntimeException {}

class Token
{
    /**
     * @var string
     */
    var $token;

    /**
     * @var string
     */
    var $tokenName = '_token';

    /**
     * @var int
     */
    var $maxTokens = 20;
    
    /**
     */
    public function __construct()
    {
        if( !isset( $_SESSION ) ) {
            throw new RuntimeException( 'start session before Token' );
        }
        if( !isset( $_SESSION[ $this->tokenName ] ) ) {
            $_SESSION[ $this->tokenName ] = array();
        }
    }

    /**
     * @return string
     */
    public function pushToken() 
    {
        $this->token = md5( time() . uniqid() );
        $_SESSION[ $this->tokenName ][] = $this->token;
        if( count( $_SESSION[ $this->tokenName ] ) > $this->maxTokens ){
            array_slice( $_SESSION[ $this->tokenName ], - $this->maxTokens );
        }
        return $this->token;
    }

    /**
     * @param string|null $type
     * @return string
     */
    public function getTokenTag( $type=null )
    {
        if( $type == 'hidden' ) {
            return "<input type=\"hidden\" name=\"{$this->tokenName}\" value=\"{$this->token}\" />";
        }
        return $this->token;
    }

    /**
     * @throws ValidationBadTokenException
     * @return bool
     */
    public function verifyToken( $token=null ) 
    {
        if( !$token ) $token = $_POST[ $this->tokenName ];
        if( !$token ) {
            throw new ValidationBadTokenException( 'no token to verify' );
        }
        if( !in_array( $token, $_SESSION[ $this->tokenName ] ) ) {
            throw new ValidationBadTokenException( 'bad token' );
        }
        foreach( $_SESSION[ $this->tokenName ] as $key => $value ) {
            if( $value == $token ) {
                unset( $_SESSION[ $this->tokenName ][ $key ] );
            }
        }
        $_SESSION[ $this->tokenName ] = array_values( $_SESSION[ $this->tokenName ] );
        return true;
    }

}