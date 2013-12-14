<?php

/**
 * Class Pgg_Check
 */
class Pgg_Check extends Pgg_Value
{
    var $types = array(
        'text'      => array( 'text', 'text' ),
        'mail'      => array( 'lower', 'mail' ),
        'katakana'  => array( 'katakana', 'katakana' ),
        'hiragana'  => array( 'hiragana', 'hiragana' ),
        'hankana'   => array( 'hankana',   'hankana' ),
        'date'      => array( 'date', 'date' ),
        'time'      => array( 'time', 'time' ),
        'tel'       => array( 'ascii', 'numeric' ),
    );
    /**
     */
    function construct() {}

    function is( $key, $type, $required=false, $pattern=null, $message=null ) {
        if( isset( $this->types[$type] ) ) {
            $set = $this->types[$type];
        } else {
            $set = $this->types['text'];
        }
        return parent::is( $key, $set[0], $set[1], $required, $pattern, $message );
    }
}

/**
 * Class Pgg_Value
 */
class Pgg_Value
{
    var $source = array();
    
    var $checked = array();
    
    var $errors = array();
    
    var $isValid = true;

    /**
     * @return array
     */
    function popData() {
        return $this->checked;
    }

    /**
     * @return array
     */
    function popError() {
        return $this->errors;
    }

    /**
     * @return bool
     */
    function isValid() {
        return $this->isValid;
    }

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
        'mail'      => 'メールアドレスです',
        'ascii'     => '半角英数字のみです',
        'katakana'  => 'カタカナのみです。',
        'hiragana'  => 'ひらがなのみです',
        'pattern '  => '文字を確認ください',
        'date'      => '日付を入力ください',
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
 * 
 * binary:   UTF-8チェックをパスしてそのまま。
 *           以下、全てUTF-8エンコードチェックあり。
 * text:     半角カタカナを全角に変換。
 * ascii:    全角英数字を半角に変換。
 * lower:    半角に変換して、小文字に。
 * upper:    半角に変換して、大文字に。
 * katakana: ひらがなをカタカナに変換。
 * hiragana: カタカナをひらがなに変換。
 * zenkaku:  半角文字全てを全角に。
 * hankana:  カタカナ／ひらがなを半角カタカナに。
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
        if( method_exists( 'Pgg_Filter', $method ) ) {
            return self::$method( $value );
        }
        return self::getAscii( $value );
    }
    
    function getBinary( $value ) {
        return $value;
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

    function getKatakana( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'C' );
        return $value;
    }
    
    function getHiragana( $value ) {
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

/**
 * Class Pgg_Validate
 * 
 * mail:
 * code:
 * ascii:
 * katakana:
 * hiragana:
 * date:
 */
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
    
    function isCode( $value ) {
        return (bool) preg_match( '/[-_0-9a-zA-Z]+/', $value );
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