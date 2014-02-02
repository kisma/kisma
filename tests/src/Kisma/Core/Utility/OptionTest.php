<?php
namespace Kisma\Core\Utility;

class OptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var Option
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = array(
			'ThisIsCamelCase'        => 'camel case',
			'this_is_not_camel_case' => 'not camel case',
			1                        => 'one',
			8                        => 'eight',
			0                        => 'zero',
		);
	}

	/**
	 * @covers Kisma\Core\Utility\Option::get
	 * @todo   Implement testGet().
	 */
	public function testGet()
	{
		$this->assertEquals(
			 'camel case',
			 Option::get( $this->object, 'this_is_camel_case' )
		);

		$this->assertEquals(
			 'not camel case',
			 Option::get( $this->object, 'ThisIsNotCamelCase' )
		);
	}

	/**
	 * @covers \Kisma\Core\Utility\Option::merge
	 */
	public function testMerge()
	{
		$_a1 = array( 'a' => 1, 'b' => 2, 'c' => 3 );
		$_a2 = array( 'd' => 4, 'e' => 5, 'f' => 6 );
		$_a3 = array( 'a' => 7, 'b' => 8, 'c' => 9 );
		$_a4 = array( 'p' => 666, 'q' => 18, 'r' => 19 );

		$_result = Option::merge( $_a1, $_a2, $_a3, $_a4 );

		$this->assertTrue( is_array( $_result ) && $_result['a'] == 7 );
		$this->assertTrue( is_array( $_result ) && $_result['p'] == 666 );
	}
}
