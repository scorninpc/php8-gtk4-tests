<?php

$file = "defs/test.defs";
$contents = file_get_contents($file);




die("OK");

// configs
$regex_patterns = [
	'block' => '/\([^)(]*+(?:(?R)[^)(]*)*+\)/im',
	'define' => '/\\(define-(\w*)\s(\w*)/im',
	// 'parameters' => 
];

$defs = [];

// read def file and find blocks
$file = "defs/gdk_methods.defs";
$contents = file_get_contents($file);
preg_match_all($regex_patterns['block'], $contents, $matches, PREG_SET_ORDER, 0);

// loop blocks
foreach($matches as $match) {

	$block = $match[0];

	// find type of def (enum, function, method)
	preg_match_all($regex_patterns['define'], $block, $tmp, PREG_SET_ORDER, 0);
	$type = $tmp[0][1];
	$name = $tmp[0][2];

	// Method
	if($type == "method") {
		// Get object
		
	}
	$defs[$type][$name] = [];
	
	// debugs
	// die();
	
	// echo $type . "\n";
	
}


var_dump($defs);
