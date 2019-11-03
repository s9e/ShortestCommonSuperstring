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
		$identicalAffixKeys = [];
		foreach ($this->prefixes as $k => $prefix)
		{
			if ($this->suffixes[$k] === $prefix)
			{
				$identicalAffixKeys[] = $k;
			}
		}

		return $identicalAffixKeys;
	}

	/**
	* Match and merge a string by key
	*
	* @param  integer $leftKey Left string's key
	* @return bool             Whether a match was found and strings merged
	*/
	protected function mergeString(int $leftKey): bool
	{
		$suffix = $this->suffixes[$leftKey];
		foreach ($this->prefixes as $rightKey => $prefix)
		{
			if ($prefix === $suffix && $leftKey !== $rightKey)
			{
				$this->mergeStringPair($leftKey, $rightKey);

				return true;
			}
		}

		return false;
	}

	/**
	* Merge two stored strings together at current affix length
	*/
	protected function mergeStringPair(int $leftKey, int $rightKey): void
	{
		$this->strings[$leftKey] .= substr($this->strings[$rightKey], $this->len);
		$this->suffixes[$leftKey] = $this->suffixes[$rightKey];
		unset($this->prefixes[$rightKey], $this->strings[$rightKey]);
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

		// Merge the remaining strings that have a prefix stored
		$keys = array_diff(array_keys($this->prefixes), $keys);
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
		$strlen = array_map('strlen', $this->strings);
		$i      = count($this->strings);
		while (--$i > 0)
		{
			$str = $this->strings[$i];
			$len = $strlen[$i];

			// Iterate over strings starting with the longest. Stop when we reach strings the size
			// of the current string
			$j = -1;
			while ($strlen[++$j] > $len)
			{
				if (strpos($this->strings[$j], $str) !== false)
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