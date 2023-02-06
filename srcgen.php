<?php

defined("PATH") || define("PATH", realpath(__DIR__));

include PATH . "/srcgen/DefsParser.php";
include PATH . "/srcgen/Strings.php";

// load methods to ignore and override
$ignores = include PATH . "/srcgen/ignores.php";
$overrides = include PATH . "/srcgen/overrides.php";

// create a single def contents
$defs_content = "";
$dir = scandir(PATH . "/srcgen/defs/");
foreach($dir as $filename) {
	if(substr($filename, strrpos($filename, ".")) == ".defs") {
		$filepath = PATH . "/srcgen/defs/" . $filename;

		$defs_content .= "\n" . file_get_contents($filepath);
	}
}

/**
 * parse all defs
 */
$parser = new DefsParser($defs_content);
$parsed = $parser->parse();

echo "\n";
echo "-------------------------\n";
echo count($parsed['object']) . " objects found\n";
echo count($parsed['enum']) . " enums found\n";
echo count($parsed['flags']) . " flags found\n";
echo count($parsed['function']) . " functions found\n";
echo count($parsed['method']) . " methods found\n";

// var_dump($parsed['object']);
// var_dump($parsed['enum']);
// var_dump($parsed['flags']);
// var_dump($parsed['function']);
// var_dump($parsed['method']);
// var_dump($parsed['method']);

// add the c-name as key of object array and create methods index
$classes = [];
foreach($parsed['object'] as $object) {
	$name = $object['c-name'];
	
	$classes[$name] = $object;
	$classes[$name]['methods'] = [];
}

// loop methods
$methods_count = 0;
foreach($parsed['method'] as $method_name => $method) {

	$class_name = $method['of-object'];

	// verify if the c-name of method are on $ignores list
	if(isset($ignores[$method_name])) {
		continue;
	}

	// if object not exists on $class, this is a struct or a interface. try a way to implement that or reserve it for another parse
	if(!isset($classes[$class_name])) {
		continue;
	}

	// add the method to the object
	$classes[$class_name]['methods'][$method_name] = $method;
	$methods_count++;
}
		
// @todo ver o que fazer com as funções, se da pra dar um parse e achar o objeto, ou se coloca a função no namespace

// @todo ver o qeu fazer com os enums

// @todo ver o qeu fazer com os flags

echo "\n";
echo "-------------------------\n";
echo count($classes) . " will binded\n";
echo $methods_count . " methods will binded\n";

/**
 * back $classes to index
 */
$objects = [];
foreach($classes as $class) {
	$objects[] = $class;
}
$classes = $objects;

/**
 * order the $classes, to parents stay on start of array
 */
$pos = 0;
while($pos < count($classes)-1) {

	if((isset($classes[$pos]['parent'])) && ($classes[$pos]['parent'] == "GInitiallyUnowned")) {
		$classes[$pos]['parent'] = "GObject";
	}
	

	// if not has parent
	if(!isset($classes[$pos]['parent'])) {

		// its parent is GObject
		$classes[$pos]['parent'] = "GObject";

		$object = $classes[$pos];
		unset($classes[$pos]);

		array_splice($classes, 0, 0, [$object]);
		
		$pos++;
		continue;
	}

	// order this class
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
 * start code write
 */
$code_phpclasses = "";
$code_extensions = "";
$code_main_includes = "";

$phpclasses_template = file_get_contents(PATH . "/srcgen/templates/phpcpp_class.template");
$phpextension_template = file_get_contents(PATH . "/srcgen/templates/phpcpp_extension.template");
$method_cpp_template = file_get_contents(PATH . "/srcgen/templates/method.cpp.template");
$method_header_template = file_get_contents(PATH . "/srcgen/templates/method.h.template");
$class_cpp_template = file_get_contents(PATH . "/srcgen/templates/class.cpp.template");
$class_header_template = file_get_contents(PATH . "/srcgen/templates/class.h.template");

// loop classes
foreach($classes as $class) {

	if(!in_array($class['c-name'], ["GtkWidget", "GtkContainer", "GtkBin", "GtkWindow"])) {
		continue;
	}

	// template vars
	$template_vars = [];
	$template_vars['module'] = $class['in-module'];
	$template_vars['classid'] = strtoupper($class['c-name']);
	$template_vars['classname'] = $class['c-name'];
	$template_vars['lower_classname'] = strtolower($class['c-name']);
	$template_vars['parentname'] = $class['parent'];
	$template_vars['lower_parentname'] = strtolower($class['parent']);
	$template_vars['cpp_methods'] = "";
	$template_vars['h_methods'] = "";
	$template_vars['includes'] = "";
	
	// new class, reset funcion vars and cast method
	$code_cpp_methods = "";
	$code_h_methods = "";
	$cast_method = strtoupper(implode("_", Strings::explode_camelcase($class['c-name'])));

	$template_vars['includes'] .= "#include \""  . $template_vars['parentname'] . ".h\"\n";

	// loop methods
	foreach($class['methods'] as $method) {
		// store method name and c-name
		$method_name = $method['name'];
		$method_cname = $method['c-name'];
		
		// clear the method vars
		$template_vars['method_name'] = $method_name;
		$template_vars['method_code'] = "";
		$template_vars['method_return'] = "";
		$template_vars['method_parameter'] = "";
		$method_code = "";

		// verify if is a constructor
		if($method['is-constructor-of']??"" == $method_cname) {
			$method_name = "__construct";
		}

		// verify if has parameters
		if(count($method['parameters']??[]) > 0) {
			$template_vars['method_parameter'] = "Php::Parameters &parameters";
		}

		// verify if has return
		if($method['return-type'] == "none") {
			$template_vars['method_return'] = "void";
		}
		else if($method_name == "__construct")
		{
			$template_vars['method_return'] = "void";
		}
		else { 
			$template_vars['method_return'] = "Php::Value";
		}
		
		// verify if the method are on the override list
		if(isset($overrides[$method_cname])) {

			// get the code
			$code_cpp_methods .= $overrides[$method_cname];

			// get the first line of the code
			$tmp = rtrim(preg_split('#\r?\n#', ltrim($method_code, " \n\r"), 2)[0]);
			$code_h_methods .= "\n\t" . str_replace($class['c-name'] . "_::", "", $tmp) . ";";
		}
		else {

			$type = DefsParser::get_method_type($method);
			
			// only a caller
			if($type == DefsParser::TYPE_CALLER) { 
				$method_code .= "" . $method_cname . "(";

				if($method_name != "__construct") {
					$hasparam = true;
					$method_code .= $cast_method . "(instance)";
				}

				$method_code .= ");";
			}

			 // simple get
			else if($type == DefsParser::TYPE_GETTER) {
				
				// $method_code .= parseReturn($method['return-type']);
				$method_code .= "getter";

			}

			// simple set
			else if($type == DefsParser::TYPE_SETTER) { 
				$method_code .= "setter";
			}

			// get with reference parameter
			else if($type == DefsParser::TYPE_GETBYREF) { 
				$method_code .= "getter by reference";
			}

			// list return
			else if($type == DefsParser::TYPE_LIST) {
				$method_code .= "return a list";
			}

			// callback 
			else if($type == DefsParser::TYPE_CALLBACK) {
				$method_code .= "callback";
				// @todo work on callbacks
				continue; 
			}
			else {
				echo "\n";
				echo "-------------------------\n";
				echo "problem on get method type\n";
				echo var_dump($method);
				die();
			}

			// add code of method
			$template_vars['method_code'] = $method_code;
			$code_cpp_methods .= Strings::sprintf_named($method_cpp_template, $template_vars);
			
			// add definitions of method
			$code_h_methods .= Strings::sprintf_named($method_header_template, $template_vars);
			
		}


	}

	// add to template vars
	$template_vars['h_methods'] = $code_h_methods;
	$template_vars['cpp_methods'] = $code_cpp_methods;
	
	// create files
	file_put_contents(PATH . "/src/" . $template_vars['module'] . "/" . $template_vars['classname'] . ".cpp", Strings::sprintf_named($class_cpp_template, $template_vars));
	file_put_contents(PATH . "/src/" . $template_vars['module'] . "/" . $template_vars['classname'] . ".h", Strings::sprintf_named($class_header_template, $template_vars));

	// add class to php-cpp (main.cpp)
	$code_phpclasses .= Strings::sprintf_named($phpclasses_template, $template_vars);
	$code_extensions .= Strings::sprintf_named($phpextension_template, $template_vars);

	// include to main.h

}

// create main.c

// create main.h








