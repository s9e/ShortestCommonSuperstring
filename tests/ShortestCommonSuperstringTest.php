<?php

/**
* @package   s9e\ShortestCommonSuperstring
* @copyright Copyright (c) 2016 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\ShortestCommonSuperstring\Tests;

use PHPUnit_Framework_TestCase;
use s9e\ShortestCommonSuperstring\ShortestCommonSuperstring;

class ShortestCommonSuperstringTest extends PHPUnit_Framework_TestCase
{
	/**
	* @dataProvider getTestCases
	*/
	public function test(array $strings, $expected)
	{
		$scs = new ShortestCommonSuperstring;
		$this->assertEquals($expected, $scs->getShortest($strings));
	}

	public function getTestCases()
	{
		return [
			[
				['abc', 'bcd'],
				'abcd'
			],
			[
				['abc', 'def'],
				'abcdef'
			],
			[
				['def', 'abc'],
				'abcdef'
			],
		];
	}
}