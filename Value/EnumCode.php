<?php

abstract class EnumCode implements EnumInterface
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var array
     */
    public $codes = array();

    /**
     * @var array
     */
    protected $originalCodes = array();

    /**
     * @param $value
     * @throws UnexpectedValueException
     */
    public function __construct( $value )
    {
        if( !array_key_exists( $value, $this->codes ) ) {
            throw new UnexpectedValueException( 'Undefined enum value: ' . $value );
        }
        $this->value = $value;
        $this->originalCodes = $this->codes;
    }

    /**
     * resets the code to the original state.
     */
    public function resetCode()
    {
        $this->codes = $this->originalCodes;
    }

    /**
     * @return string
     */
    public function toValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toLabel()
    {
        return $this->codes[ $this->value ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->toValue();
    }
}