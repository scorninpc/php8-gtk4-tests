<?php

class DefsParser
{
	protected $content;
	protected $regex;

	const TYPE_CALLER = 1;
	const TYPE_GETBYREF = 2;
	const TYPE_SETTER = 3;
	const TYPE_GETTER = 4;
	const TYPE_LIST = 5;
	const TYPE_CALLBACK = 6;

	/**
	 * constructor
	 */
	public function __construct($content)
	{
		$this->content = $content;

		$this->regex = [
			'block' => '/((\r?\n)|(\r\n?))/',
			'define' => '/\(define-(\w*+) (\w*+)/m',
			'parameters' => '/\(parameters/m',
			'values' => '/\(values/m',
			'key_value' => '/[[:alnum:]|\-|\_|\*]+/m',
		];
	}

	/**
	 * do the parse
	 */
	public function parse()
	{
		// initialize controls
		$lineno = 0;
		$state = "";
		$tmp_type = NULL;
		$tmp_name = NULL;
		$tmp_parameters = [];
		$tmp_values = [];
		$tmp_values = [];
		$tmp_options = [];
		$parsed = [];

		// explode blocks
		foreach(preg_split($this->regex['block'], $this->content) as $line) {
			$lineno++;

			// blank line
			if(strlen($line) == 0) {
				continue;
			}

			// comment
			if($line[0] == ";") {
				continue;
			}

			// define line
			preg_match_all($this->regex['define'], $line, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0) {

				// test if its ok
				if($state != "") {
					die("[101] block not closed at line " . $lineno);
				}

				$tmp_type = $matches[0][1];
				$tmp_name = $matches[0][2];
				
				$state = "define";
				continue;
			}

			// parameters
			preg_match_all($this->regex['parameters'], $line, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0) {
				$state = "parameters";
				continue;
			}

			// values
			preg_match_all($this->regex['values'], $line, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0) {
				$state = "values";
				continue;
			}

			// end parentesis
			if(trim($line) == ")") {
				if($state == "parameters") {
					$state = "define";
				}
				elseif($state == "values") {
					$state = "define";
				}
				elseif($state == "define") {
					// end of block
					$state = "";

					$tmp_options['name'] = $tmp_name;
					
					// add the object
					$parsed[$tmp_type][$tmp_options['c-name']] = $tmp_options;
					if(count($tmp_parameters) > 0) {
						$parsed[$tmp_type][$tmp_options['c-name']]['parameters'] = $tmp_parameters;
					}
					if(count($tmp_values) > 0) {
						$parsed[$tmp_type][$tmp_options['c-name']]['values'] = $tmp_values;
					}

					// restart
					$tmp_type = NULL;
					$tmp_name = NULL;
					$tmp_parameters = [];
					$tmp_values = [];
					$tmp_options = [];
				}
			}

			// Parse the key => value
			preg_match_all($this->regex['key_value'], $line, $matches, PREG_SET_ORDER, 0);
			if(count($matches) == 0) {
				continue;
			}
			if($state == "parameters") {
				$tmp_parameters[] = [
					'type' => $matches[0][0],
					'name' => $matches[1][0]
				];
			}
			elseif($state == "values") {
				$tmp_values[] = [
					'name' => $matches[0][0],
					'value' => $matches[1][0]
				];
			}
			elseif($state == "define") {
				$tmp_options[$matches[0][0]] = $matches[1][0];
			}
		}

		return $parsed;
	}

	/**
	 * try to get method type
	 */
	static public function get_method_type($method)
	{
		
		// if return none, possible set type
		if($method['return-type'] == "none") {

			// look if has no parameter
			if(!isset($method['parameters'])) {
				return DefsParser::TYPE_CALLER;
			}

			// look the parameters type
			foreach($method['parameters'] as $parameter) {

				// verify if parameters has any reference parameter 
				// if(strpos($parameter['type'], "**") !== FALSE) {
				// 	return DefsParser::TYPE_GETBYREF;
				// }

				// verify if parameters has any reference parameter 
				if(in_array($parameter['type'], ["int*", "long*", "gint*", "glong*", "guint*", "gulong*"])) {
					return DefsParser::TYPE_GETBYREF;
				}

				// verify if has func 
				if(substr($parameter['name'], -5) == "_func") {
					return DefsParser::TYPE_CALLBACK;
				}
			}

			// if no just caller, no parameter by ref, so its a setter 
			return DefsParser::TYPE_SETTER;
			
		}
		if($method['return-type'] != "none") {

			// if return is a GList
			if(0) {
				return DefsParser::TYPE_LIST;
			}

			// just a get
			return DefsParser::TYPE_GETTER;
		}

		return FALSE;
	}
}