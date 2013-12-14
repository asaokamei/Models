<?php

class Pgg_Check
{
    var $source = array();
    
    var $checked = array();
    
    var $errors = array();
    
    var $isValid = true;

    /**
     * @param string $key
     * @param string $filter
     * @param string $check
     * @param bool   $required
     * @param string $pattern
     * @param string $default
     * @param null   $message
     * @internal param string $key
     * @return bool|string
     */
    function is( $key, $filter='text', $check='text', $required=false, $pattern='', $default='', $message=null )
    {
        // get a value
        $value = (string) $this->getRaw( $key, $filter );

        // filter the value.
        $value = $this->filter( $value, $filter );
        
        // missing value. 
        if( !$value ) {
            if( $required ) {
                $this->setError( $key, $default, 'required' );
                return false;
            }
            return $default; // return the default value. 
        }
        
        // validates the value.  
        if( !$this->check( $value, $check, $pattern ) ) {
            $this->setError( $key, $value, $check, $message );
            return false;
        }
        
        // check pattern
        if( !$this->pattern( $value, $pattern ) ) {
            $this->setError( $key, $value, 'pattern', $message );
            return false;
        }
        $this->checked[ $key ] = $value;
        return $value;
    }

    /**
     * filter a $value based on its type. 
     * 
     * @param $value
     * @param $type
     * @return bool|string
     */
    function filter( $value, $type )
    {
        return Pgg_Filter::get( $value, $type );
    }

    /**
     * validates a $value based on its type and pattern.
     *
     * @param $value
     * @param $type
     * @return string
     */
    function check( $value, $type='text' )
    {
        return Pgg_Validate::is( $value, $type );
    }

    /**
     * @param $value
     * @param $pattern
     * @return bool
     */
    function pattern( $value, $pattern )
    {
        return Pgg_Validate::pattern( $value, $pattern );
    }

    /**
     * @param string      $key
     * @param string      $value
     * @param string|null $error
     * @param null        $message
     */
    function setError( $key, $value, $error, $message=null )
    {
        $this->isValid = false;
        $error = Pgg_Error::get( $error, $message );
        $this->checked[ $key ] = $value;
        $this->errors[ $key ] = $error;
    }

    /**
     * @param string $key
     * @param string $filter
     * @return mixed
     */
    function getRaw( $key, $filter='text' )
    {
        if( $filter == 'date' ) {
            if( $found = $this->getMultiple( $key, array('y','m','d'), '%04d-%02d-%02d' ) ) {
                return $found;
            }
        }
        if( $filter == 'time' ) {
            if( $found = $this->getMultiple( $key, array('h','m','s'), '%02d:%02d:%02d' ) ) {
                return $found;
            }
        }
        if( $filter == 'datetime' ) {
            if( $found = $this->getMultiple( $key, array('y','m','d','h','m','s'), '%04d-%02d-%02d %02d:%02d:%02d' ) ) {
                return $found;
            }
        }
        if( isset( $this->source[ $key ] ) ) {
            return $this->source[ $key ];
        }
        return false;
    }

    /**
     * @param string $key
     * @param array  $list
     * @param string $format
     * @return bool|string
     */
    function getMultiple( $key, $list, $format )
    {
        $found = array();
        foreach( $list as $post ) {
            if( isset( $this->source[ "{$key}_{$post}" ] ) ) {
                $found[] = $this->source[ "{$key}_{$post}" ];
            } else {
                return false;
            }
        }
        $args  = array( $format ) + $found;
        $value = call_user_func_array( 'sprintf', $args );
        return $value;
    }
}

/**
 * Class Pgg_Error
 */
class Pgg_Error
{
    static public  $messages = array(
        0 => '入力内容を確認ください',
        'required'  => '入力必須です',
        'mail'      => '',
        'AsciiOnly' => '',
        'KanaOnly'  => '',
        'HiraOnly'  => '',
        'pattern '  => '',
    );

    /**
     * @param string      $error
     * @param string|null $message
     * @return string
     */
    static function get( $error, $message=null )
    {
        if( $message ) {
            return $message;
        }
        if( isset( self::$messages[ $error ] ) ) {
            return self::$messages[ $error ];
        }
        return  self::$messages[ 0 ];
    }
}

/**
 * Class Pgg_Filter
 */
class Pgg_Filter
{
    /**
     * @param $value
     * @param $type
     * @return string|bool
     */
    static function get( $value, $type )
    {
        $method = 'get' . ucwords( $type );
        return self::$method( $value );
    }
    
    function getText( $value )
    {
        if( !mb_check_encoding( $value, 'UTF-8' ) ) {
            return '';
        }
        $value = mb_convert_kana( $value, 'KV' );
        return $value;
    }
    
    function getLower( $value ) {
        $value = self::getText( $value );
        return strtolower( $value );
    }

    function getUpper( $value ) {
        $value = self::getText( $value );
        return strtoupper( $value );
    }

    function getKana( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'C' );
        return $value;
    }
    
    function getHira( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'c' );
        return $value;
    }
    
    function getAscii( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'as' );
        return $value;
    }

    function getZenkaku( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'AS' );
        return $value;
    }
    
    function getHanKana( $value ) {
        if( !mb_check_encoding( $value, 'UTF-8' ) ) {
            return '';
        }
        $value = mb_convert_kana( $value, 'k' );
        return $value;
    }
}

class Pgg_Validate
{
    /**
     * validates a $value based on its type and pattern. 
     * returns null if valid, or string of invalid reason.
     * 
     * @param string        $value
     * @param string        $type
     * @return bool
     */
    static function is( $value, $type='text' )
    {
        $method = 'is' . ucwords( $type );
        return self::$method( $value );
    }

    function isMail( $value ) {
        return (bool) preg_match( '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $value );
    }
    
    function isKatakana( $value ) {
        return (bool) preg_match( "/^[ァ-ヶー]+$/u", $value );
    }

    function isHiragana( $value ) {
        return (bool) preg_match( "/^[ぁ-ん]+$/u", $value );
    }

    function isDate( $value ) {
        try {
            new DateTime( $value );
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }
    /**
     * @param string $value
     * @param string $pattern
     * @return bool
     */
    static function pattern( $value, $pattern )
    {
        $pattern = "/^{$pattern}$/";
        return (bool) preg_match( $pattern, $value );
    }
}