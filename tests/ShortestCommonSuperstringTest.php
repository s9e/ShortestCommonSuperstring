<?php

/**
* @package   s9e\ShortestCommonSuperstring
* @copyright Copyright (c) 2016-2019 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\ShortestCommonSuperstring\Tests;

use PHPUnit\Framework\TestCase;
use s9e\ShortestCommonSuperstring\ShortestCommonSuperstring;

class ShortestCommonSuperstringTest extends TestCase
{
	/**
	* @dataProvider getTestCases
	*/
	public function test(array $strings, $expected)
	{
		$scs    = new ShortestCommonSuperstring;
		$actual = $scs->getShortest($strings);
		foreach ($strings as $string)
		{
			if ($string !== '')
			{
				$this->assertStringContainsString($string, $actual);
			}
		}
		$this->assertEquals($expected, $actual);
	}

	public function getTestCases()
	{
		return [
			[
				[],
				''
			],
			[
				[''],
				''
			],
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
			[
				['abb', 'bba', 'bbb'],
				'abbba'
			],
			[
				['abb', 'bbb', 'bbc'],
				'abbbc'
			],
			[
				['aaa', 'aac', 'baa'],
				'baaac'
			],
			[
				['abc', 'agf', 'bcda'],
				'abcdagf'
			],
			[
				['0', '1', '10', '11'],
				'110'
			],
			[
				['xbbb', 'bbabb', 'bbbx'],
				'xbbbabbbx'
			],
			[
				['xbbb', 'bbabb', 'bbbx', 'bbxxb'],
				'xbbbxxbbabb'
			],
			[
				// This test doesn't target any specific code path. It just merges 2048 strings
				// (1536 unique strings) that are between 1 and 10 characters long. It ensures that
				// things don't go haywire when we try to merge many strings
				array_merge(
					array_map('decbin', range(0, 1023)),
					array_map(
						function ($i)
						{
							return sprintf('%010b', $i);
						},
						range(0, 1023)
					)
				),
				'0000000000100000000011111111110000000011000000010100000001110000001001000000101100000011010000001111000001000100000100110000010101000001011100000110010000011011000001110100000111110000100001000110000100101000010011100001010010000101011000010110100001011110000110001000011001100001101010000110111000011100100001110110000111101000011111100010001010001000111000100100100010010110001001101000100111100010100110001010101000101011100010110010001011011000101110100010111110001100011001010001100111000110100100011010110001101101000110111100011100110001110101000111011100011110010001111011000111110100011111110010010011001001010100100101110010011011001001110100100111110010100101001110010101011001010110100101011110010110011001011010100101101110010111011001011110100101111110011001101001100111100110101010011010111001101101100110111010011011111001110011101011001110110100111011110011110101001111011100111110110011111101001111111101010101011101010110110101011111010110101101111010111011101011110110101111111011011011101101111110111011111011110111111111'
			],
		];
	}
}