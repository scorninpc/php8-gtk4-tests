<?php

$file = "defs/gtk_methods.defs";
$contents = file_get_contents($file);

/**
 * Parse def file
 */
$reged_define = '/\(define-(\w*+) (\w*+)/m';
$reged_parameters = '/\(parameters/m';
$reged_values = '/\(values/m';
$reged_key_value = '/[[:alnum:]|\-|\_|\*]+/m';

$parsed = [];

$tmp_type = NULL;
$tmp_name = NULL;
$tmp_parameters = [];
$tmp_values = [];
$tmp_values = [];
$tmp_options = [];

$lineno = 0;
$state = "";
foreach(preg_split("/((\r?\n)|(\r\n?))/", $contents) as $line){
	$lineno++;

	// comment
	if(strlen($line) == 0) {
		continue;
	}
	if($line[0] == ";") {
		continue;
	}
    
	// define line
	preg_match_all($reged_define, $line, $matches, PREG_SET_ORDER, 0);
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
	preg_match_all($reged_parameters, $line, $matches, PREG_SET_ORDER, 0);
	if(count($matches) > 0) {
		$state = "parameters";
		continue;
	}

	// values
	preg_match_all($reged_values, $line, $matches, PREG_SET_ORDER, 0);
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

			// add the object
			$parsed[$tmp_type][$tmp_name] = $tmp_options;
			if(count($tmp_parameters) > 0) {
				$parsed[$tmp_type][$tmp_name]['parameters'] = $tmp_parameters;
			}
			if(count($tmp_values) > 0) {
				$parsed[$tmp_type][$tmp_name]['values'] = $tmp_values;
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
	preg_match_all($reged_key_value, $line, $matches, PREG_SET_ORDER, 0);
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

/**
 * add the c-name as key of array
 */
$classes = [];
foreach($parsed['object'] as $object) {
	$name = $object['c-name'];
	
	$classes[$name] = $object;
	$classes[$name]['methods'] = [];
}



/**
 * add methods on the same array of object
 * @ToDo some methods are static, interface or just a struct, so this is not on $classes (maybe add a list of objects to load from a file manually)
 */
foreach($parsed['method'] as $method_name => $method) {

	$class_name = $method['of-object'];
	
	// verify if the object of this method are on $classes
	if(!isset($classes[$class_name])) {
		//echo "[102] object " . $class_name . " not exists\n";
		// var_dump($method);
		// echo "\n\n";
		//$classes[$class_name] = [];
		continue;
	}


	// add the method to the object
	$classes[$class_name]['methods'][$method_name] = $method;
}

/**
 * order classes by hierarchy
 */
$pos = 0;
while($pos < count($classes)) {


	$test = $pos+1;
	while($test < count($classes)) {

		var_dump($classes[0]);
		die();
		$test++;

	}

	$pos++;


}


/**
 * create main C files of php extension
 */


 /**
  * create object specific bind code
  */
foreach($classes as $class_name => $class) {

	// create header file

	// create code file

	// add to include of extension

	// add object and methods to the main

}




