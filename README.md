s9e\ShortestCommonSuperstring takes a list of strings and returns a single string that contains all of the given input.

[![Build Status](https://api.travis-ci.org/s9e/ShortestCommonSuperstring.svg?branch=master)](https://travis-ci.org/s9e/ShortestCommonSuperstring)
[![Code Coverage](https://scrutinizer-ci.com/g/s9e/ShortestCommonSuperstring/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/s9e/ShortestCommonSuperstring/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/s9e/ShortestCommonSuperstring/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/s9e/ShortestCommonSuperstring/?branch=master)

## Usage

```php
$scs = new s9e\ShortestCommonSuperstring\ShortestCommonSuperstring;
echo $scs->getShortest(['abb', 'bba', 'bbb']);
```
```
abbba
```
