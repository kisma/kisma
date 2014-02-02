<?php
namespace Kisma\Core;

require_once __DIR__ . '/SeedTest_Object.php';

/**
 */
class SeedTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var SeedTest_Object
	 */
	protected $_object;
	/**
	 * @var int
	 */
	protected $_destructorEventFired = 0;

	/**
	 * @param int $how Used to capture the destructor event in the fixture
	 */
	public function destructorEventFired( $how = 0 )
	{
		$this->_destructorEventFired += ( empty( $how ) ? 0 : 1 );
	}

	/**
	 * Creates our test object
	 */
	protected function setUp()
	{
		if ( null !== $this->_object )
		{
			unset( $this->_object );
		}

		$this->_object = new SeedTest_Object( array( 'tester' => $this ) );
	}

	/**
	 * @covers Kisma\Core\Seed::__destruct
	 */
	public function testOnBeforeDestruct()
	{
		$this->_destructorEventFired = 0;
		$this->_object->__destruct();
		$this->assertEquals( 1, $this->_destructorEventFired, 'Destructor event was not fired.' );
	}

	/**
	 * @covers Kisma\Core\Seed::__wakeup
	 * @covers Kisma\Core\Seed::__construct
	 * @covers Kisma\Core\Seed::publish
	 */
	public function testOnAfterConstruct()
	{
		$this->assertTrue( $this->_object->constructEvent );
	}

	/**
	 * @covers Kisma\Core\Seed::getId
	 */
	public function testGetId()
	{
		$this->assertNotEmpty(
			 $_id = $this->_object->getId(),
			 'The object ID has not been set properly.'
		);
	}

	/**
	 * @covers Kisma\Core\Seed::getTag
	 * @covers Kisma\Core\Seed::setTag
	 */
	public function testGetTag()
	{
		$this->assertTrue( is_string( $this->_object->getTag() ) );
		$this->_object->setTag( 'new_tag' );
		$this->assertTrue( 'new_tag' == $this->_object->getTag() );
	}

	/**
	 * @covers Kisma\Core\Seed::getName
	 * @covers Kisma\Core\Seed::setName
	 */
	public function testGetName()
	{
		$this->assertTrue( is_string( $this->_object->getName() ) );
		$this->_object->setName( 'new_name' );
		$this->assertTrue( 'new_name' == $this->_object->getName() );
	}

	/**
	 * @covers Kisma\Core\Seed::on
	 * @covers Kisma\Core\Seed::publish
	 */
	public function testUnsubscribe()
	{
		$_eventFired = false;

		$_listener = function ( $event ) use ( &$_eventFired )
		{
			$_eventFired = true;
		};

		//	Subscribe and publish to set flag
		$this->_object->on( 'crazy.event', $_listener );
		$this->_object->publish( 'crazy.event' );

		$this->assertTrue( $_eventFired );

		//	Clear, unsub, and publish. Flag should not be set...
		$_eventFired = false;
		$this->_object->off( 'crazy.event', $_listener );
		$this->_object->publish( 'crazy.event' );

		$this->assertTrue( false === $_eventFired );
	}
}
