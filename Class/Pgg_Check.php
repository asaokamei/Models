<?php

/**
 * Class Pgg_Check
 */
class Pgg_Check extends Pgg_Value
{
    var $types = array(
        'text'      => array( 'filter' => 'text',     'check' => 'text',     'default' => '' ),
        'mail'      => array( 'filter' => 'lower',    'check' => 'mail',     'default' => '' ),
        'katakana'  => array( 'filter' => 'katakana', 'check' => 'katakana', 'default' => '' ),
        'hiragana'  => array( 'filter' => 'hiragana', 'check' => 'hiragana', 'default' => '' ),
        'hankana'   => array( 'filter' => 'hankana',  'check' =>  'hankana', 'default' => '' ),
        'date'      => array( 'filter' => 'date',     'check' => 'date',     'default' => null ),
        'time'      => array( 'filter' => 'time',     'check' => 'time',     'default' => null ),
        'tel'       => array( 'filter' => 'ascii',    'check' => 'numeric',  'default' => '' ),
    );
    /**
     */
    function construct() {}

    /**
     * @param string $key
     * @param string $type
     * @param bool   $required
     * @param null   $pattern
     * @param null   $message
     * @return bool|string
     */
    function push( $key, $type, $required=false, $pattern=null, $message=null ) 
    {
        if( isset( $this->types[$type] ) ) {
            $set = $this->types[$type];
        } else {
            $set = $this->types['text'];
        }
        $set[ 'required' ] = $required;
        $set[ 'pattern'  ] = $pattern;
        $set[ 'message'  ] = $message;
        
        return parent::is( $key, $set );
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
     * @param array $source
     * @param bool  $reset
     */
    function setSource( $source, $reset=false ) 
    {
        if( $reset ) {
            $this->source = $source;
        } else {
            $this->source = array_merge( $this->source, $source );            
        }
    }
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
     * @param array  $option
     * @return bool|string
     */
    function is( $key, $option=array() )
    {
        $defaultOption = array(
            'filter'   => 'text',
            'check'    => 'text',
            'required' => false,
            'pattern'  => '',
            'default'  => '',
            'message'  => '',
        );
        $option = $option + $defaultOption;
        // get a value
        $value = (string) $this->getRaw( $key, $option['filter'] );

        // filter the value.
        $value = $this->filter( $value, $option['filter'] );
        
        // missing value. 
        if( !$value ) {
            if( $option['required'] ) {
                $this->setError( $key, false, 'required' );
                return false;
            }
            return $this->checked[ $key ] = $option['default'];
        }
        
        // validates the value.  
        if( !$this->check( $value, $option['check'], $option['pattern'] ) ) {
            $this->setError( $key, $value, $option['check'], $option['message'] );
            return false;
        }
        
        // check pattern
        if( $option['pattern'] && !$this->pattern( $value, $option['pattern'] ) ) {
            $this->setError( $key, $value, 'pattern', $option['message'] );
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

    static function getBinary( $value ) {
        return $value;
    }

    static function getText( $value )
    {
        if( !mb_check_encoding( $value, 'UTF-8' ) ) {
            return '';
        }
        $value = mb_convert_kana( $value, 'KV' );
        return $value;
    }

    static function getLower( $value ) {
        $value = self::getAscii( $value );
        return strtolower( $value );
    }

    static function getUpper( $value ) {
        $value = self::getAscii( $value );
        return strtoupper( $value );
    }

    static function getKatakana( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'C' );
        return $value;
    }

    static function getHiragana( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'c' );
        return $value;
    }

    static function getAscii( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'as' );
        $value = str_replace( 
            array( '＠', '（', '）', 'ー', '＜', '＞', '！', '＃', '＄', '％', '＆',   ), 
            array(  '@',  '(',  ')',  '-',  '<',  '>', '!', '#', '$', '%', '&',  ), $value );
        return $value;
    }

    static function getZenkaku( $value ) {
        $value = self::getText( $value );
        $value = mb_convert_kana( $value, 'AS' );
        return $value;
    }

    static function getHanKana( $value ) {
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
        if( method_exists( 'Pgg_Validate', $method ) ) {
            return self::$method( $value );
        }
        return $value;
    }

    static function isMail( $value ) {
        return (bool) preg_match( '/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $value );
    }

    static function isCode( $value ) {
        return (bool) preg_match( '/[-_0-9a-zA-Z]+/', $value );
    }

    static function isKatakana( $value ) {
        return (bool) preg_match( "/^[ァ-ヶー]+$/u", $value );
    }

    static function isHiragana( $value ) {
        return (bool) preg_match( "/^[ぁ-ん]+$/u", $value );
    }

    static function isDate( $value ) {
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