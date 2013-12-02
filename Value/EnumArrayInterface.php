<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2013/12/02
 * Time: 13:02
 */
interface EnumArrayInterface
{
    /**
     * @return string[]
     */
    public function toValues();

    /**
     * resets the code to the original state.
     */
    public function resetCodes();

    /**
     * @return string[]
     */
    public function toLabels();
}