<?php
/**
 * Created by PhpStorm.
 * User: asao
 * Date: 2013/12/02
 * Time: 13:02
 */
interface EnumInterface
{
    /**
     * @return string[]
     */
    public function toValue();

    /**
     * resets the code to the original state.
     */
    public function resetCode();

    /**
     * @return string[]
     */
    public function toLabel();
}