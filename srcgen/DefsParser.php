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

		$parsed['struct']['GdkRGBA'] = [
			'red' => "gdouble",
			'green' => "gdouble",
			'blue' => "gdouble",
			'alpha' => "gdouble",
		];

		$parsed['struct']['GdkColor'] = [
			'pixel' => "guint32",
			'red' => "guint32",
			'green' => "guint32",
			'blue' => "guint32",
		];

		$parsed['object']['GdkDevice'] = [
			'parent' => "GObject",
			'c-name' => "GdkDevice",
			'in-module' => "Gdk",
		];

		$parsed['object']['GdkVisual'] = [
			'parent' => "GObject",
			'c-name' => "GdkVisual",
			'in-module' => "Gdk",
		];

		$parsed['object']['GdkDisplay'] = [
			'parent' => "GObject",
			'c-name' => "GdkDisplay",
			'in-module' => "Gdk",
		];
		
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

					// verify if its a function
					if($tmp_type == "function") {

						// verify if its a function of 
						$function = $parsed[$tmp_type][$tmp_options['c-name']];
						if(isset($function['of-object'])) {
							if(in_array($function['of-object'], ["GdkDevice", "GdkVisual", "GdkDisplay"])) {

								$class_name = $function['of-object'];
								$function_name = $function['name'];
					
								$parsed[$class_name]['methods'][$function_name] = $function;
								unset($parsed[$tmp_type][$tmp_options['c-name']]);
							}
						}

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

		$this->parsed = $parsed;

		return $this->parsed;
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

	/**
	 * parse parameter
	 */
	function parseParam($count, $param_name, $param_type)
	{
		global $parsed;

		// Remove constant key
		// $type = getCleanType($param_type, FALSE);
		$type = $param_type;
		$name = $param_name;
		
		$template_code = "";


		// Verifica o tipo
		switch($type) {

			// String
			case "gchar*":
			case "const-gchar*":
				$template_code .= "\n\tstd::string c_%(param_name)s = parameters[%(param_count)s];";
				$template_code .= "\n\tgchar *%(param_name)s = (gchar *)c_%(param_name)s.c_str();";
				break;

			// Float
			case "gfloat":
			case "double":
				$template_code .= "\n\tdouble d_%(param_name)s = parameters[%(param_count)s];";
				$template_code .= "\n\tgfloat %(param_name)s = (float)d_%(param_name)s;";
				break;

			// ponter
			case "guint*":
				$template_code .= "\n\t%(type)s %(param_name)s;";
				break;

			//
			case "guint":
				$template_code .= "\n\tint %(param_name)s = (int)parameters[%(param_count)s];";
				break;

			// Some simple casts
			case "gint":
			case "gboolean":
				$template_code .= "\n\t%(type)s %(param_name)s = (%(type)s)parameters[%(param_count)s];";
				break;

			// Others
			default:

				// if($name == "device") {
				// 	die($type);
				// }
				if($this->isClass($type)) {
					if($type[-1] == "*") {
						$type = substr($type, 0, -1);
					}
					
					$cast_method = strtoupper(implode("_", Strings::explode_camelcase($type)));

					$template_code .= "\n\t" . $type . " *%(param_name)s;";
					// $template_code .= "if(parameters.size() > 0) {";
					$template_code .= "\n\tPhp::Value object_%(param_name)s = parameters[%(param_count)s];";
					$template_code .= "\n\t" . $type . "_ *phpgtk_%(param_name)s = (" . $type . "_ *)object_%(param_name)s.implementation();";
					$template_code .= "\n\t%(param_name)s = " . $cast_method . "(phpgtk_%(param_name)s->get_instance());";
					// $template_code .= "}";
				}

				else if(($this->isEnum($type)) || ($type == "GType")) {
					$template_code .= "\n\tint int_%(param_name)s = parameters[%(param_count)s];";
					$template_code .= "\n\t%(type)s %(param_name)s = (%(type)s) int_%(param_name)s;";
				}

				else if($this->isFlag($type)) {
					$template_code .= "\n\tint int_%(param_name)s = parameters[%(param_count)s];";
					$template_code .= "\n\t%(type)s %(param_name)s = (%(type)s) int_%(param_name)s;";
				}

				else if($this->isStruct($type)) {
					$type = str_replace("const-", "", $type);
					if($type[-1] == "*") {
						$type = substr($type, 0, -1);
					}
					
					$template_code .= "\n\t" . $type . " %(param_name)s = {";
					$count_inline = 0;
					foreach($this->parsed['struct'][$type] as $part) {
						$template_code .= "parameters[%(param_count)s][" . $count_inline++ . "], ";
					}
					// o problema de usar esse metod é que ele é passado por referencia na função, por exemplo &color

					$template_code = substr($template_code, 0, -2);
					$template_code .= " };";
				}

				// elseif(isClass($type)) {

				
				// }
				// else {
				// 	die($type . " [" . $name . "] cannot be parsed on parseParam function [" . __FILE__ . ":" . __LINE__ . "]\n");
				// }
		}

		$result_code = Strings::sprintf_named($template_code, [
			'param_count' => $count,
			'param_name' => $name,
			'type' => $type,
		]);

		return $result_code;
	}

	/**
	 * verify if type is a class
	 */
	public function isClass($type)
	{
		// verify if it's a pointer
		if($type[-1] == "*") {
			$type = substr($type, 0, -1);
		}
		if(isset($this->parsed['object'][$type])) {
			return TRUE;
		}
	}

	/**
	 * verify if type is a struct
	 */
	public function isStruct($type)
	{
		$type = str_replace("const-", "", $type);

		// verify if it's a pointer
		if($type[-1] == "*") {
			$type = substr($type, 0, -1);
		}
		if(isset($this->parsed['struct'][$type])) {
			return TRUE;
		}
	}

	/**
	 * verify if type is enum
	 */
	public function isEnum($type)
	{
		if(isset($this->parsed['enum'][$type])) {
			return TRUE;
		}
	}

	/**
	 * verify if type is a flag
	 */
	public function isFlag($type)
	{
		if(isset($this->parsed['flags'][$type])) {
			return TRUE;
		}
	}

	/**
	 * parse return type
	 */
	public function parseReturn($type)
	{
		$str = "";

		// verify the type
		switch($type) {
			// normal returns
			case "gchar*":
			case "gfloat":
			case "gint":
			case "gboolean":
				return "\n\treturn ret;";
			
			case "guint":
				return "\n\treturn (int)ret;";

			case "GList*":
				$str = "\n\n\treturn glist_to_phparray(ret);";
				return $str;

			// this can be any class, not enum, not interface
			// @todo need to verify on array of classes if its exists
			default:
				
				if($this->isClass($type)) {
					$str = "\n\n\treturn cobject_to_phpobject(ret);";
					return $str;
				}

				else if($this->isEnum($type)) {
					$str = "\n\n\treturn (int)ret;";
					return $str;
				}

				else if($this->isFlag($type)) {
					$str = "\n\n\treturn (int)ret;";
					return $str;
				}

				else {
					$str = "\n\n\treturn ret;";
					return $str;
				}
		}
	}
}