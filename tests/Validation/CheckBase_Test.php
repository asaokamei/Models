<?php
require_once( __DIR__ . '/../../Class/Pgg_Check.php' );

class CheckBase_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Pgg_Check
     */
    public $pgg;
    
    function setUp()
    {
        $this->pgg = new Pgg_Check();
    }
    
    function test0()
    {
        $this->assertEquals( 'Pgg_Check', get_class( $this->pgg ) );
    }

    /**
     * @test
     */
    function push_text_gets_test()
    {
        $source = array( 'text' => 'test text' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'text', 'text' );
        $this->assertEquals( 'test text', $got );
    }
}