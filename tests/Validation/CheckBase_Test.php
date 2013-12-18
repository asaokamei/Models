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
        $source = array( 'test' => 'test text' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'text' );
        $pop = $this->pgg->popData();
        $this->assertEquals( true, $this->pgg->isValid() );
        $this->assertEquals( 'test text', $got );
        $this->assertEquals( 'test text', $pop['test'] );
    }

    /**
     * @test
     */
    function push_empty_data_gets_empty()
    {
        $source = array();
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'text' );
        $pop = $this->pgg->popData();
        $this->assertEquals( true, $this->pgg->isValid() );
        $this->assertEquals( '', $got );
        $this->assertEquals( true, isset( $pop['test'] ) );
        $this->assertEquals( '', $pop['test'] );
    }

    /**
     * @test
     */
    function push_mail()
    {
        $source = array( 'test' => 'test@Example.com' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'mail' );
        $pop = $this->pgg->popData();
        $this->assertEquals( true, $this->pgg->isValid() );
        $this->assertEquals( 'test@example.com', $got );
        $this->assertEquals( 'test@example.com', $pop['test'] );
    }

    /**
     * @test
     */
    function push_mail_with_Japanese_char()
    {
        $source = array( 'test' => 'test＠Example.com' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'mail' );
        $pop = $this->pgg->popData();
        $this->assertEquals( true, $this->pgg->isValid() );
        $this->assertEquals( 'test@example.com', $got );
        $this->assertEquals( 'test@example.com', $pop['test'] );
    }

    /**
     * @test
     */
    function push_required_with_empty_returns_false()
    {
        $source = array( 'test' => '' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'text', true );
        $pop = $this->pgg->popData();
        $err = $this->pgg->popError();
        $this->assertEquals( false, $this->pgg->isValid() );
        $this->assertEquals( false, $got );
        $this->assertEquals( false, $pop['test'] );
        $this->assertEquals( '入力必須です', $err['test'] );
    }

    /**
     * @test
     */
    function push_katakana_only()
    {
        $source = array( 'test' => 'あいうえお' );
        $this->pgg->setSource( $source );
        $got = $this->pgg->push( 'test', 'katakana' );
        $pop = $this->pgg->popData();
        $this->assertEquals( true, $this->pgg->isValid() );
        $this->assertEquals( 'test@example.com', $got );
        $this->assertEquals( 'test@example.com', $pop['test'] );
    }
}