<?php declare(strict_types=1);

/**
* @package   s9e\ShortestCommonSuperstring
* @copyright Copyright (c) 2016-2019 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\ShortestCommonSuperstring;

class ShortestCommonSuperstring
{
	/**
	* @var integer Affix length for current iteration
	*/
	protected $len;

	/**
	* @var string[] Prefixes of current length
	*/
	protected $prefixes;

	/**
	* @var string[] Strings being merged
	*/
	protected $strings;

	/**
	* @var string[] Suffixes of current length
	*/
	protected $suffixes;

	/**
	* Get the shortest string that contains all given strings
	*
	* @param  string[] $strings
	* @return string
	*/
	public function getShortest(array $strings): string
	{
		$this->strings = array_unique($strings);
		$this->sortStrings();
		$this->removeEmptyStrings();
		$this->removeFullyOverlappingStrings();
		if (isset($this->strings[1]))
		{
			// Start with the longest partial match possible, which is equal to the length of the
			// second longest string minus 1
			$this->len = strlen($this->strings[1]);
			while (--$this->len > 0)
			{
				$this->mergeStrings();
			}
		}

		return implode('', $this->strings);
	}

	/**
	* Compare given strings
	*/
	protected static function compareStrings(string $a, string $b): int
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
	* Return the list of keys pointing to strings whose prefix is identical to their suffix
	*
	* @return integer[]
	*/
	protected function getIdenticalAffixKeys(): array
	{
		return array_keys(array_intersect_assoc($this->prefixes, $this->suffixes));
	}

	/**
	* Match and merge a string by key
	*
	* @param  integer $leftKey Left string's key
	* @return bool             Whether a match was found and strings merged
	*/
	protected function mergeString(int $leftKey): bool
	{
		// Temporarily blank this string's prefix from the array to avoid matches
		$prefix = $this->prefixes[$leftKey];
		$this->prefixes[$leftKey] = '';

		// Search for a prefix that matches this string's suffix before restoring its prefix
		$rightKey = array_search($this->suffixes[$leftKey], $this->prefixes, true);
		$this->prefixes[$leftKey] = $prefix;

		if ($rightKey === false)
		{
			return false;
		}

		$this->mergeStringPair($leftKey, $rightKey);

		return true;
	}

	/**
	* Merge two stored strings together at current affix length
	*/
	protected function mergeStringPair(int $leftKey, int $rightKey): void
	{
		$this->strings[$leftKey] .= substr($this->strings[$rightKey], $this->len);
		$this->suffixes[$leftKey] = $this->suffixes[$rightKey];
		unset($this->prefixes[$rightKey], $this->strings[$rightKey], $this->suffixes[$rightKey]);
	}

	/**
	* Merge all stored strings using current affix length
	*/
	protected function mergeStrings(): void
	{
		$this->storeAffixes();

		// Merge strings whose prefix is identical to their suffix
		$keys = $this->getIdenticalAffixKeys();
		$this->mergeStringsGroup($keys);

		// Merge strings that have a suffix that matches a prefix
		$keys = array_keys(array_intersect($this->suffixes, $this->prefixes));
		$this->mergeStringsGroup($keys);

		$this->resetKeys();
	}

	/**
	* Match and merge strings from given group
	*
	* @param  integer[] $keys List of keys
	* @return void
	*/
	protected function mergeStringsGroup(array $keys): void
	{
		foreach ($keys as $leftKey)
		{
			if (isset($this->strings[$leftKey]))
			{
				while ($this->mergeString($leftKey))
				{
					// Keep going
				}
			}
		}
	}

	/**
	* Remove empty strings from the list
	*/
	protected function removeEmptyStrings(): void
	{
		if (end($this->strings) === '')
		{
			array_pop($this->strings);
		}
	}

	/**
	* Remove fully-overlapping strings from the list
	*/
	protected function removeFullyOverlappingStrings(): void
	{
		// Copy the list of strings ordered by ascending length and create a master string by
		// concatenating them all
		$strings = array_reverse($this->strings, true);
		$all     = implode('', $strings);
		$pos     = 0;
		foreach ($strings as $i => $str)
		{
			// Test whether current string potentially appears in any subsequent, bigger strings
			$pos += strlen($str);
			if (strpos($all, $str, $pos) === false)
			{
				continue;
			}

			// Iterate over strings from the longest to the one before current string
			$j = -1;
			while (++$j < $i)
			{
				if (strpos($strings[$j], $str) !== false)
				{
					unset($this->strings[$i]);
					break;
				}
			}
		}

		$this->resetKeys();
	}

	/**
	* Reset the keys in the string list to remove the gaps
	*/
	protected function resetKeys(): void
	{
		end($this->strings);
		if (key($this->strings) !== count($this->strings) - 1)
		{
			$this->strings = array_values($this->strings);
		}
	}

	/**
	* Sort the stored strings
	*/
	protected function sortStrings(): void
	{
		usort($this->strings, [__CLASS__, 'compareStrings']);
	}

	/**
	* Capture and stored affixes of current length
	*
	* Will only store affixes from strings that are longer than current affix length
	*/
	protected function storeAffixes(): void
	{
		$this->prefixes = [];
		$this->suffixes = [];
		foreach ($this->strings as $str)
		{
			if (strlen($str) <= $this->len)
			{
				break;
			}
			$this->prefixes[] = substr($str, 0, $this->len);
			$this->suffixes[] = substr($str, -$this->len);
		}
	}
}