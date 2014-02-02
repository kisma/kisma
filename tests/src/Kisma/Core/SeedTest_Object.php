<?php
namespace Kisma\Core;

use Kisma\Core\Enums\Events\SeedEvents;
use Kisma\Core\Interfaces\SubscriberLike;
use Kisma\Kisma;

/**
 * SeedTest_Object
 */
class SeedTest_Object extends Seed implements SubscriberLike
{
	//*************************************************************************
	//* Public Members
	//*************************************************************************

	/**
	 * @var bool
	 */
	public $constructEvent = false;
	/**
	 * @var bool
	 */
	public $destructEvent = false;
	/**
	 * @var \Kisma\Core\SeedTest
	 */
	public $tester = null;
	/**
	 * @var int
	 */
	public static $counter = 0;

	//*************************************************************************
	//* Public Methods
	//*************************************************************************

	/**
	 * @param array $settings
	 */
	public function __construct( $settings = array() )
	{
		Kisma::getDispatcher()->addListener( SeedEvents::AFTER_CONSTRUCT, array( $this, 'onAfterConstruct' ) );
		Kisma::getDispatcher()->addListener( SeedEvents::BEFORE_DESTRUCT, array( $this, 'onBeforeDestruct' ) );

		parent::__construct( $settings );
	}

	/**
	 * {@InheritDoc}
	 */
	public function onAfterConstruct( $event = null )
	{
		$this->constructEvent = true;
	}

	/**
	 * {@InheritDoc}
	 */
	public function onBeforeDestruct( $event = null )
	{
		$this->tester->destructorEventFired( 1 );

		return true;
	}
}