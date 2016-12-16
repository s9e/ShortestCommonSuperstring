<?php

/**
* @package   s9e\ShortestCommonSuperstring
* @copyright Copyright (c) 2016 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\ShortestCommonSuperstring;

class ShortestCommonSuperstring
{
	/**
	* @var string[]
	*/
	protected $strings;

	/**
	* Get the shortest string that contains all given strings
	*
	* @param  string[] $strings
	* @return string
	*/
	public function getShortest(array $strings)
	{
		$this->strings = $strings;
		$this->sortStrings();
	}

	/**
	* Compare strings
	*
	* @param  string  $a
	* @param  string  $b
	* @return integer
	*/
	protected function compareStrings($a, $b)
	{
		$aLen = strlen($a);
		$bLen = strlen($b);
		if ($aLen !== $bLen)
		{
			// Longest first
			return $bLen - $aLen;
		}

		// Lexical order
		return ($a > $b) ? 1 : -1;
	}

	/**
	* Sort the stored strings
	*
	* @return void
	*/
	protected function sortStrings()
	{
		usort($this->strings, [__CLASS__, 'compareStrings']);
	}
}