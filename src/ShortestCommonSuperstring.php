<?php declare(strict_types=1);

/**
* @package   s9e\ShortestCommonSuperstring
* @copyright Copyright (c) 2016-2021 The s9e authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\ShortestCommonSuperstring;

class ShortestCommonSuperstring
{
	/**
	* @var integer Affix length for current iteration
	*/
	protected int $len;

	/**
	* @var string[] Prefixes of current length
	*/
	protected array $prefixes;

	/**
	* @var string[] Strings being merged
	*/
	protected array $strings;

	/**
	* @var string[] Suffixes of current length
	*/
	protected array $suffixes;

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
	* Match and merge a string by key
	*
	* @param  integer $leftKey Left string's key
	* @return bool             Whether a match was found and strings merged
	*/
	protected function mergeString(int $leftKey): bool
	{
		// Temporarily remove this string's prefix from the array to avoid matches
		if (isset($this->prefixes[$leftKey]))
		{
			$prefix = $this->prefixes[$leftKey];
			unset($this->prefixes[$leftKey]);
		}

		// Search for a prefix that matches this string's suffix before restoring its prefix
		$rightKey = array_search($this->suffixes[$leftKey], $this->prefixes, true);
		if (isset($prefix))
		{
			$this->prefixes[$leftKey] = $prefix;
		}

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
		if (isset($this->suffixes[$rightKey]))
		{
			$this->suffixes[$leftKey] = $this->suffixes[$rightKey];
		}
		else
		{
			unset($this->suffixes[$leftKey]);
		}
		unset($this->prefixes[$rightKey], $this->strings[$rightKey], $this->suffixes[$rightKey]);
	}

	/**
	* Merge all stored strings using current affix length
	*/
	protected function mergeStrings(): void
	{
		$this->storeMatchingAffixes();

		// Merge strings whose prefix is identical to their suffix
		$keys = array_keys(array_intersect_assoc($this->prefixes, $this->suffixes));
		$this->mergeStringsGroup($keys);

		// Merge all the strings that can be used on the left side of the concatenation
		$keys = array_keys($this->suffixes);
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
			while (isset($this->suffixes[$leftKey]) && $this->mergeString($leftKey))
			{
				// Keep going as long as the left key has a suffix that matches a prefix
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
				if (str_contains($strings[$j], $str))
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
		// Shortest first, then lexical order
		usort(
			$this->strings,
			fn($a, $b) => (strlen($b) - strlen($a)) ?: ($a <=> $b)
		);
	}

	/**
	* Capture and store matching affixes of current length
	*
	* Will only store affixes from strings that are longer than current affix length and that have
	* a match on the other end of a string
	*/
	protected function storeMatchingAffixes(): void
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

		// Only keep affixes with a match on the other end of a string
		$this->prefixes = array_intersect($this->prefixes, $this->suffixes);
		$this->suffixes = array_intersect($this->suffixes, $this->prefixes);
	}
}