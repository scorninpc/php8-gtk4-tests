<?php

/**
 * some helpers
 */
class Strings
{
	/**
	 * sprintf with named parameters
	 */
	static function sprintf_named($format, $args)
	{
		$names = preg_match_all('/%\((.*?)\)/', $format, $matches, PREG_SET_ORDER);

		$values = array();
		foreach($matches as $match) {
			$values[] = $args[$match[1]];
		}

		$format = preg_replace('/%\((.*?)\)/', '%', $format);
		return vsprintf($format, $values);
	}

	/**
	 * explode CamelCase
	 */
	static public function explode_camelcase($string)
	{
		$re = '/(?<=[a-z])(?=[A-Z]) | (?<=[A-Z])(?=[A-Z][a-z])/x';
		$string = preg_split($re, $string);

		return $string;
	}
}