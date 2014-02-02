<?php
use Kisma\Core\Enums\CoreSettings;
use Kisma\Core\TestCase;
use Kisma\Kisma;

/**
 * KismaTest
 */
class KismaTest extends TestCase
{
	/**
	 * @covers \Kisma\Kisma::get
	 */
	public function testConceive()
	{
		$this->assertTrue( Kisma::get( CoreSettings::INITIALIZED ) );
	}

	/**
	 * @covers \Kisma\Kisma::set
	 * @covers \Kisma\Kisma::get
	 */
	public function testSet()
	{
		Kisma::set( 'testSetOption', true );
		$this->assertTrue( Kisma::get( 'testSetOption' ) );
	}

	/**
	 * @covers \Kisma\Kisma::get
	 */
	public function testGet()
	{
		$this->assertTrue( Kisma::get( 'testSetOption' ) );
	}

}
