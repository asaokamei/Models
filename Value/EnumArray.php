<?php

abstract class EnumArray
{
    /**
     * @var array
     */
    public $values;

    /**
     * @var array
     */
    public $codes = array();

    /**
     * @var array
     */
    protected $originalCodes = array();

    /**
     * @param array $values
     * @throws UnexpectedValueException
     */
    public function __construct( $values )
    {
        if( !is_array( $values ) ) $values = array( $values );
        foreach( $values as $v ) {
            if( !array_key_exists( $v, $this->codes ) ) {
                throw new UnexpectedValueException( 'Undefined enum-array value: ' . $v );
            }
        }
        $this->values = $values;
        $this->originalCodes = $this->codes;
    }

    /**
     * resets the code to the original state.
     */
    public function resetCodes()
    {
        $this->codes = $this->originalCodes;
    }

    /**
     * @return string[]
     */
    public function toValues()
    {
        return $this->values;
    }

    /**
     * @return string[]
     */
    public function toLabels()
    {
        $labels = array();
        foreach( $this->values as $v ) {
            $labels[] = $this->codes[$v];
        }
        return $labels;
    }

}