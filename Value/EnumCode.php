<?php

abstract class EnumCode
{
    public $value;

    public $codes = array();

    protected $originalCodes = array();

    /**
     * @param $value
     * @throws UnexpectedValueException
     */
    public function __construct( $value )
    {
        if( !array_key_exists( $value, $this->codes ) ) {
            throw new UnexpectedValueException( 'value=' . $value );
        }
        $this->value = $value;
        $this->originalCodes = $this->codes;
    }

    /**
     * resets the code to the original state.
     */
    public function resetCodes() {
        $this->codes = $this->originalCodes;
    }

    /**
     * @return string
     */
    public function toValue() {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toLabel() {
        return $this->codes[ $this->value ];
    }
}