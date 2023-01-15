<?php

function explodeCamelCase($string)
{
	$re = '/(?<=[a-z])(?=[A-Z]) | (?<=[A-Z])(?=[A-Z][a-z])/x';
	$str = preg_split($re, $string);

	return $str;
}

function toCamelCase($string) 
{
	$parts = explode("_", $string);
	foreach($parts as $index => $part) {
		if(in_array($part, [
				"vbox", "hbox",
				"vbutton", "hbutton",
				"hpaned", "vpaned",
				"vseparator", "hseparator",
				"vscrollbar", "hscrollbar",
				"hscale", "vscale"
			])) {
			$parts[$index] = strtoupper(substr($part, 0, 2)).substr($part, 2);
		}
		elseif(in_array($part, ["hsv"])) {
			$parts[$index] = strtoupper(substr($part, 0, 3)).substr($part, 3);
		}
		else {
			$parts[$index] = ucfirst($part);
		}
	}
    // $str = str_replace(" ", "", ucwords(str_replace("_", " ", $string)));
	$str = implode("", $parts);

    return $str;
}


// $a = [1, 2, 3];

// array_splice($a, 1, 0, [1.5]);

// var_dump($a);

// die();

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

// Override
$classes['GtkAccelMap'] = ['c-name' => "GtkAccelMap",'methods' => [],];
$classes['GtkAccelerator'] = ['c-name' => "GtkAccelerator", 'methods' => [], ];
$classes['AppChooserWidget'] = ['c-name' => "AppChooserWidget", 'parent'=>"GtkBox", 'methods' => [], ];
$classes['GtkAppChooser'] = ['c-name' => "GtkAppChooser", 'parent'=>"GtkAppChooserWidget", 'methods' => [], ];
$classes['GtkBorder'] = ['c-name' => "GtkBorder", 'methods' => [], ];
$classes['GtkClipboard'] = ['c-name' => "GtkClipboard", 'methods' => [], ];
$classes['GtkCssSection'] = ['c-name' => "GtkCssSection", 'methods' => [], ];
$classes['GtkDrag'] = ['c-name' => "GtkDrag", 'methods' => [], ];
$classes['GtkGrab'] = ['c-name' => "GtkGrab", 'methods' => [], ];
$classes['GtkColorChooser'] = ['c-name' => "GtkColorChooser", 'is-interface' => TRUE, 'methods' => [], ];
$classes['GtkEditable'] = ['c-name' => "GtkEditable", 'is-interface' => TRUE, 'methods' => [], ];
$classes['GtkEventController'] = ['c-name' => "GtkEventController", 'methods' => [], ];
$classes['GtkFileChooser'] = ['c-name' => "GtkFileChooser", 'is-interface' => TRUE, 'methods' => [], ];
$classes['GtkScrollable'] = ['c-name' => "GtkScrollable", 'is-interface' => TRUE, 'methods' => [], ];
$classes['GtkGesture'] = ['c-name' => "GtkGesture", 'methods' => [], ];
$classes['GtkFileFilter'] = ['c-name' => "GtkFileFilter", 'methods' => [], ];
$classes['GtkGlArea'] = ['c-name' => "GtkGlArea", 'methods' => [], ];
$classes['GtkIconInfo'] = ['c-name' => "GtkIconInfo", 'methods' => [], ];
$classes['GtkImContext'] = ['c-name' => "GtkImContext", 'methods' => [], ];
$classes['GtkImMulticontext'] = ['c-name' => "GtkImMulticontext", 'parent'=>"GtkImContext", 'methods' => [], ];
$classes['GtkMain'] = ['c-name' => "GtkMain", 'methods' => [], ];
$classes['GtkDevice'] = ['c-name' => "GtkDevice", 'methods' => [], ];
$classes['GtkKeySnooper'] = ['c-name' => "GtkKeySnooper", 'methods' => [], ];
$classes['GtkModelButton'] = ['c-name' => "GtkModelButton", 'parent'=>"GtkButton", 'methods' => [], ];
$classes['GtkPadController'] = ['c-name' => "GtkPadController", 'parent'=>"GtkEventController", 'methods' => [], ];
$classes['GtkPageSetup'] = ['c-name' => "GtkPageSetup", 'methods' => [], ];
$classes['GtkPaperSize'] = ['c-name' => "GtkPaperSize", 'methods' => [], ];
$classes['GtkPlacesSidebar'] = ['c-name' => "GtkPlacesSidebar", 'parent'=>"GtkScrolledWindow", 'methods' => [], ];
$classes['GtkRecentInfo'] = ['c-name' => "GtkRecentInfo", 'methods' => [], ];
$classes['GtkFileFilter'] = ['c-name' => "GtkFileFilter", 'methods' => [], ];
$classes['GtkRender'] = ['c-name' => "GtkRender", 'methods' => [], ];
$classes['GtkPrintContext'] = ['c-name' => "GtkPrintContext", 'methods' => [], ];
$classes['GtkPrintSettings'] = ['c-name' => "GtkPrintSettings", 'methods' => [], ];
$classes['GtkRecentFilter'] = ['c-name' => "GtkRecentFilter", 'methods' => [], ];
$classes['GtkSelection'] = ['c-name' => "GtkSelection", 'methods' => [], ];
$classes['GtkTargetList'] = ['c-name' => "GtkTargetList", 'methods' => [], ];
$classes['GtkTargetTable'] = ['c-name' => "GtkTargetTable", 'methods' => [], ];
$classes['GtkTarget'] = ['c-name' => "GtkTarget", 'methods' => [], ];
$classes['GtkRcProperty'] = ['c-name' => "GtkRcProperty", 'methods' => [], ];
$classes['GtkTest'] = ['c-name' => "GtkTest", 'methods' => [], ];
$classes['GtkShortcutLabel'] = ['c-name' => "GtkShortcutLabel", 'parent'=>"GtkWidget", 'methods' => [], ];
$classes['GtkTextAttributes'] = ['c-name' => "GtkTextAttributes", 'methods' => [], ];
$classes['GtkTextIter'] = ['c-name' => "GtkTextIter", 'methods' => [], ];
$classes['GtkSymbolicColor'] = ['c-name' => "GtkSymbolicColor", 'methods' => [], ];
$classes['GtkStock'] = ['c-name' => "GtkStock", 'methods' => [], ];
// $classes['GtkRcParse'] = ['c-name' => "GtkRcParse", 'methods' => [], ];
$classes['GtkRc'] = ['c-name' => "GtkRc", 'methods' => [], ];
$classes['GtkIconSource'] = ['c-name' => "GtkIconSource", 'methods' => [], ];
$classes['GtkIconSet'] = ['c-name' => "GtkIconSet", 'methods' => [], ];
$classes['GtkIconSize'] = ['c-name' => "GtkIconSize", 'methods' => [], ];
$classes['GtkTooltip'] = ['c-name' => "GtkTooltip", 'methods' => [], ];
$classes['GtkTreePath'] = ['c-name' => "GtkTreePath", 'methods' => [], ];
$classes['GtkTreeRowReference'] = ['c-name' => "GtkTreeRowReference", 'methods' => [], ];
$classes['GtkTreeIter'] = ['c-name' => "GtkTreeIter", 'methods' => [], ];
// $classes['GtkTree'] = ['c-name' => "GtkTree", 'methods' => [], ];
$classes['GtkUIManager'] = ['c-name' => "GtkUIManager", 'methods' => [], ];
$classes['GtkRequisition'] = ['c-name' => "GtkRequisition", 'methods' => [], ];
$classes['GtkGradient'] = ['c-name' => "GtkGradient", 'methods' => [], ];
$classes['GtkPaint'] = ['c-name' => "GtkPaint", 'methods' => [], ];
$classes['GtkPrint'] = ['c-name' => "GtkPrint", 'methods' => [], ];
$classes['Gtk'] = ['c-name' => "Gtk", 'methods' => [], ];


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
 * Loop between functions, looking for function that can be added as method of a class
 */
foreach($parsed['function'] as $function_name => $function) {
	$camel_function = toCamelCase($function_name);

	// if($function_name == "gtk_vbox_new") {
	// 	die($camel_function);
	// }
	foreach($classes as $class_name => $class) {
		
		if(($pos = strpos($camel_function, $class_name)) !== FALSE) {

			$method_name = substr($camel_function, strlen($class_name));
			$method_name = explodeCamelCase($method_name);
			$method_name = implode("_", array_map("strtolower", $method_name));

			$classes[$class_name]['methods'][$method_name] = $function;

			unset($parsed['function'][$function_name]);
			break;
		}

	}
}

var_dump($parsed['function']);

/**
 * Add classes to indexes
 * 
 * @verify use of array_values
 */
$objects = [];
foreach($classes as $class) {
	$objects[] = $class;
}
$classes = $objects;

/**
 * order classes by hierarchy
 */
$pos = 0;
while($pos < count($classes)-1) {

	if(!isset($classes[$pos]['parent'])) {
		$classes[$pos]['parent'] = "GObject";

		$object = $classes[$pos];
		unset($classes[$pos]);

		array_splice($classes, 0, 0, [$object]);
		
		$pos++;
		continue;
	}

	$test = $pos+1;
	while($test >= 0) {

		// if parent of this, it this class
		if($classes[$pos]['parent'] == ($classes[$test]['c-name'])) {

			$object = $classes[$pos];
			unset($classes[$pos]);

			array_splice($classes, $test+1, 0, [$object]);
		}

		$test--;
	}

	$pos++;
}



/**
 * create main C files of php extension
 */


 /**
  * create object specific bind code
  */
foreach($classes as $class) {

	// create header file

	// create code file

	// add to include of extension

	// add object and methods to the main

	if(isset($class['parent'])) {
		echo $class['c-name'] . " (parent of " . $class['parent'] . ")\n";
	}
	else {
		echo $class['c-name'] . " (parent of GObject)\n";
	}
}




