<?php

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

if ( 2 > count( $argv ) ) {
	die( '引数の数が足りません。' );
}

list( $file, $slug ) = $argv;
$year = isset( $argv[2] ) ? $argv[2] : 0;

$only_work = false;
foreach ( $argv as $arg ) {
	$arg = trim( $arg );
	if ( '--only-work' === $arg ) {
		$only_work = true;
	}
}

$parser = new \Genron\Scraper\Parser();
$students = $parser->get_students( $slug, $year );
$rows = [];
if ( $only_work ) {
	$students = array_filter( $students, function( $student ) {
		return $student['work'];
	} );
	$students = array_map( function( $student ) {
		return [ $student['title'], $student['name'], $student['url'], $student['selected'] ? '○' : '-', (int) $student['character'] ];
	}, $students );
} else {
	$students = array_map( function( $student ) {
		return [ $student['title'], $student['name'], $student['url'] ];
	}, $students );
}

echo implode( "\n", array_map( function( $row ) {
	return implode( "\t", $row );
}, $students ) );
