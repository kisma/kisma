<?php
namespace Kisma\Core\Utility;

use Kisma\Core\Enums;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-21 at 19:41:48.
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var DateTime
	 */
	protected $object;

	/**
	 * @covers Kisma\Core\Utility\DateTime::prettySeconds
	 * @todo   Implement testPrettySeconds().
	 */
	public function testPrettySeconds()
	{
		$this->assertEquals(
			'1h 0m 0.00s',
			DateTime::prettySeconds( Enums\DateTime::SecondsPerHour )
		);

		$this->assertEquals(
			'5h 37m 25.12s',
			DateTime::prettySeconds( 20245.12 )
		);

	}
}
