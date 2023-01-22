<?php

function vsprintf_named($format, $args) 
{
	$names = preg_match_all('/%\((.*?)\)/', $format, $matches, PREG_SET_ORDER);

	$values = array();
	foreach($matches as $match) {
		$values[] = $args[$match[1]];
	}

	$format = preg_replace('/%\((.*?)\)/', '%', $format);
	return vsprintf($format, $values);
}

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

function getCleanType($type, $removePointer=TRUE)
{
	$type = str_replace("const-", "", $type);
	$type = str_replace("const", "", $type);
	if($removePointer) {
		$type = str_replace("*", "", $type);
	}
	$type = rtrim(ltrim($type));

	return $type;
}

function parseParam($count, $param_name, $param_type)
{
	global $parsed;

	// Remove constant key
	$type = getCleanType($param_type, FALSE);
	$name = $param_name;
	
	$template_code = "";

	// Verifica o tipo
	switch($type) {

		// String
		case "gchar*":
			$template_code .= "\n\tstd::string c_%(param_name)s = parameters[%(param_count)s];\n";
			$template_code .= "\n\tgchar *%(param_name)s = (gchar *)c_%(param_name)s.c_str();";
			break;

		// Float
		case "gfloat":
			$template_code .= "\n\tdouble d_%(param_name)s = parameters[%(param_count)s];\n";
			$template_code .= "\n\tgfloat %(param_name)s = (float)d_%(param_name)s;";
			break;

		// Some simple casts
		case "guint*":
			$template_code .= "\n\t%(type)s %(param_name)s;";
			break;

		//
		case "guint":
			$template_code .= "\n\tguint %(param_name)s = (int)parameters[%(param_count)s];";
			break;

		// Some simple casts
		case "gint":
		case "gboolean":
			$template_code .= "\n\t%(type)s %(param_name)s = (%(type)s)parameters[%(param_count)s];";
			break;

		// Others
		default:
			if(isEnum($type)) {
				$template_code .= "\n\tint int_%(param_name)s = parameters[%(param_count)s];";
				$template_code .= "\n\t%(type)s %(param_name)s = (%(type)s) int_%(param_name)s;";
			}
			elseif(isClass($type)) {

				$type = getCleanType($type, TRUE);
				$cast_method = strtoupper(implode("_", explodeCamelCase($type)));

				$template_code .= "\n\t" . $type . " *%(param_name)s;";
				// $template_code .= "if(parameters.size() > 0) {";
				$template_code .= "\n\tPhp::Value object_%(param_name)s = parameters[%(param_count)s];";
				$template_code .= "\n\t" . $type . "_ *phpgtk_%(param_name)s = (" . $type . "_ *)object_%(param_name)s.implementation();";
				$template_code .= "\n\t%(param_name)s = " . $cast_method . "(phpgtk_%(param_name)s->get_instance());";
				// $template_code .= "}";
			}
			else {
				die($type . " [" . $name . "] cannot be parsed on parseParam function [" . __FILE__ . ":" . __LINE__ . "]\n");
			}
	}

	$result_code = vsprintf_named($template_code, [
		'param_count' => $count,
		'param_name' => $name,
		'type' => $type,
	]);

	return $result_code;
}

function parseReturn($return_type)
{
	$type = getCleanType($return_type, FALSE);
	
	// verify the type
	switch($type) {
		// normal returns
		case "gchar*":
		case "gfloat":
		case "guint":
		case "gint":
		case "gboolean":
			return "\n\treturn ret;";

		// this can be any class, not enum, not interface
		// @todo need to verify on array of classes if its exists
		default:
			
			$type = getCleanType($type);

			$str = "\n";
			$str .= "\t" . $type . "_ *phpgtk_ret = new " . $type . "_();\n";
			$str .= "\tphpgtk_ret->set_instance((gpointer *)ret);\n";
			$str .= "\treturn Php::Object(\"" . $type . "\", phpgtk_ret);";

			return $str;
	}
}

function isEnum($type)
{
	global $parsed;

	foreach($parsed['enum'] as $enum) {
		if($type == $enum['c-name']) {
			return TRUE;
		}
	}

	return FALSE;
}

function isClass($type)
{
	global $classes;

	$type = getCleanType($type, TRUE);

	foreach($classes as $class) {
		if($type == $class['c-name']) {
			return TRUE;
		}
	}

	return FALSE;

}


// $a = [1, 2, 3];

// array_splice($a, 1, 0, [1.5]);

// var_dump($a);

// die();
$files = [
	"defs/gdk_methods.defs",
	// "defs/gdk_enums.defs",
	"defs/gtk_methods.defs",
	// "defs/gtk_enums.defs",
];
$contents = "";
foreach($files as $file) {
	$contents .= file_get_contents($file);
}
// $file = "defs/gtk_methods.defs";
// $contents = file_get_contents($file);
$src_dir = "../src/";

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

// var_dump($parsed['object']);
// var_dump($parsed['enum']);
// var_dump($parsed['flags']);
// var_dump($parsed['function']);
// var_dump($parsed['method']);
// var_dump($parsed['method']);



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
$classes['GdkPixbuf'] = ['c-name' => "GdkPixbuf", 'parent'=>"GObject", 'methods' => [], ];
$classes['Gtk'] = ['c-name' => "Gtk", 'methods' => [], ];


// methods to ignore
$ignored_methods = [
	"gtk_event_box_get_type",
	"gtk_separator_menu_item_get_type",
	"gtk_separator_tool_item_get_type",
	"gtk_separator_get_type",
	"gtk_tree_sortable_get_type",
	"gtk_tree_sortable_set_sort_func",
	"gtk_tree_sortable_set_default_sort_func",
	"gtk_alignment_get_type",

	"gtk_status_icon_set_from_gicon",
	"gtk_status_icon_set_screen",
	"gtk_status_icon_get_geometry",
	"gtk_status_icon_new_from_gicon",
	"gtk_status_icon_get_type",
	"gtk_status_icon_get_gicon",
	"gtk_status_icon_get_storage_type",
	"gtk_status_icon_get_x11_window_id",
];

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

			// if its a constructor, verify if its the class of then
			if(isset($function['is-constructor-of'])) {
				if($class_name != $function['is-constructor-of']) {
					continue;
				}
			}

			$method_name = substr($camel_function, strlen($class_name));
			$method_name = explodeCamelCase($method_name);
			$method_name = implode("_", array_map("strtolower", $method_name));

			$classes[$class_name]['methods'][$method_name] = $function;

			unset($parsed['function'][$function_name]);
			break;
		}

	}
}


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
 * TEST ONLY FOR GTKEVENTBOX
 * 
 * - VARS OF TEMPLATES
 * classid => class name without _ and uppercase
 * classname => name of class
 * parentname => name of class of parent
 * includes => includes files 
 * methodsdef => list of definitions of methods
 * methods => list of code of methods
 * 
 * methodname => name of method
 * methodreturncast => cast of returned value of c function
 * cfunction => function name
 * 
 * classregister => is a main.cpp register class
 */
$template_vars = [];
foreach($classes as $class) {
	if($class['c-name'] == "GtkStatusIcon") {
		break;
	}
}

$overrides = include("./overrides.php");

// get object template vars
$template_vars['classid'] = strtoupper($class['c-name']);
$template_vars['classname'] = $class['c-name'];
$template_vars['parentname'] = $class['parent'];
$template_vars['methodsdef'] = "";
$template_vars['methods'] = "";
$template_vars['includes'] = "";
$template_vars['classregister'] = "";


$template_vars['classregister'] .= "\t\tPhp::Class<" . $class['c-name'] . "_> " . strtolower($class['c-name']) . "(\"" . $class['c-name'] . "\");\n";
$template_vars['classregister'] .= "\t\t\t" . strtolower($class['c-name']) . ".extends(" . strtolower($class['parent']) . ");\n";

$cast_method = strtoupper(implode("_", explodeCamelCase($class['c-name'])));

// loop the methods
foreach($class['methods'] as $method) {

	$method_name = $method['name'];
	$str = "";

	// verify if the method are on the list of not to be implemented
	if(in_array($method['c-name'], $ignored_methods)) {
		continue;
	}

	// verify if is a constructor
	if($method['is-constructor-of']??"" == $class['c-name']) {
		$method_name = "__construct";
	}

	// verify if the method are on the list of manual include
	if(isset($overrides[$method['c-name']])) {
		$str .= $overrides[$method['c-name']];

		$tmp = rtrim(preg_split('#\r?\n#', ltrim($str, " \n\r"), 2)[0]);

		$template_vars['methodsdef'] .= "\t\t\t" . str_replace($class['c-name'] . "_::", "", $tmp) . ";\n";
	}
	else {

		// Verify if has return
		if($method['return-type'] == "none") {
			$str .= "void";
		}
		else if($method_name == "__construct")
		{
			$str .= "void";
		}
		else { 
			$str .= "Php::Value";
		}

		// create the method name
		$str .= " " . $class['c-name'] . "_::" . $method_name;

		// verify parameters
		$str .= "(";
		if(count($method['parameters']??[]) > 0) {
			$str .= "Php::Parameters &parameters";
		}
		$str .= ")";

		// add this to method definition
		$template_vars['methodsdef'] .= "\t\t\t" . str_replace($class['c-name'] . "_::", "", $str) . ";\n";

		// continue to write code 
		$str .= "\n{";
			
		// retriave parameters
		var_dump($method);
		foreach($method['parameters']??[] as $count => $parameter) {
			$str .= parseParam($count, $parameter['name'], $parameter['type']) . "\n";
		}

		// Verify if has return of c function
		if($method['return-type'] == "none") {
			$str .= "\n\t";
		}
		else if($method_name == "__construct") {
			$str .= "\n\tinstance = (gpointer *)";
		}
		else { 
			$str .= "\n\t" . $method['return-type'] . " ret = ";
		}

		// call the c function
		$str .= $method['c-name'] . "(";
		$hasparam = false;

		if($method_name != "__construct") {
			$hasparam = true;
			$str .= $cast_method . "(instance), ";
		}

		foreach($method['parameters']??[] as $count => $parameter) {
			$hasparam = true;
			$str .= $parameter['name'] . ", ";
		}
		if($hasparam) {
			$str = substr($str, 0, strlen($str) - 2);
		}
		$str .= ");";

		// parse return of c function
		if($method['return-type'] == "none") {
			$str .= "";
		}
		else if($method_name == "__construct") {
			$str .= "";
		}
		else { 
			$str .= "\n" . parseReturn($method['return-type']);
		}

		// close method
		$str .= "\n}";
	}

	// add method to the template var
	$template_vars['methods'] .= $str . "\n\n";

	// add method to registration
	$template_vars['classregister'] .= "\t\t\t" . strtolower($class['c-name']) . ".method<&" . $class['c-name'] . "_::" . $method_name . ">(\"" . $method_name . "\");\n";
}

$header_template = include("./templates/header.php");
$header = vsprintf_named($header_template, $template_vars);

$code_template = include("./templates/code.php");
$code = vsprintf_named($code_template, $template_vars);

$header_file = $src_dir . $class['in-module'] . "/" . $class['c-name'] . ".h";
$code_file = $src_dir . $class['in-module'] . "/" . $class['c-name'] . ".cpp";

// write the files
file_put_contents($header_file, $header);
file_put_contents($code_file, $code);
echo $template_vars['classregister'] . "\n";
die();


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




