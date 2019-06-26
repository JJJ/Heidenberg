<?php
namespace Heidenberg\Common;

/**
 * Filter `the_content` and determine whether or not to cypher it.
 *
 * 1.0.0
 *
 * @param string $content
 * @return string
 */
function should_cypher( $content = '' ) {
	global $_heidenberg_cypher;

	// Strip Blocks
	$stripped = preg_replace( '/<!--(.|\s)*?-->/', '', $content );

	// Strip Tags
	$stripped = wp_strip_all_tags( $stripped );

	// Strip Shortcodes
	$stripped = strip_shortcodes( $stripped );

	// Whether or not to cypher
	if ( is_admin() || empty( $stripped ) ) {
		$_heidenberg_cypher = false;
	} else {
		$_heidenberg_cypher = true;
	}

	// Always return the original content
	return $content;
}
add_filter( 'the_content', 'Heidenberg\\Common\\should_cypher', 8 ); // Before do_blocks()

/**
 * Filters the_content, and adds zero-width spaces between chunks using a
 * somewhat predictable and repeatable algorithm. (This means that 2 strings
 * that are exactly equal on the same site will always produce the same results.)
 *
 * @since 1.0.0
 *
 * @global bool $_heidenberg_cypher
 * @param string $content
 *
 * @return string
 */
function cypher( $content = '' ) {
	global $_heidenberg_cypher;

	// Bail if not cyphering
	if ( empty( $_heidenberg_cypher ) ) {
		return $content;
	}

	// Default variables
	$char       = '&#8203;';
	$position   = 0;
	$new_chunks = array();

	// Hash based on the content
	$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
	$salt = get_home_url(); // @todo make customizable, generate salt, etc...
	$hash = hexdec( substr( hash_hmac( $algo, $content, $salt ), 0, 15 ) );

	// Add spaces between tags, then replace double spaces
	$stripped = strip_tags( str_replace( '<', ' <', $content ) );
	$stripped = str_replace( '  ', ' ', $stripped );

	// Words
	$type     = explode( ' ', $stripped );
	//str_split( $stripped, 1 )

	// Trim each chunk
	$chunks = array_map( 'trim', $type );

	// Get the maximum number of iterations
	$count = count( $chunks );

	// Use the hash to seed the RNG
	mt_srand( $hash );

	// Loop through chunks and
	for ( $i = 0; $i < $count; ++$i ) {
		$offset = mt_rand( 0, $count - 1 );

		// No duplicates
		if ( isset( $new_chunks[ $offset ] ) ) {
			continue;
		}

		// Combine arrays
	 	$new_chunks = $new_chunks + array_slice( $chunks, $offset, 1, true );
	}

	// Remove empties
	$new_chunks = array_filter( $new_chunks );

	// Re-key
	ksort( $new_chunks );

	// Loop through chunks
	foreach ( $new_chunks as $chunk ) {

		// Get the position
		$position = strpos( $content, $chunk, $position );

		// Make sure the word was actually found
		if ( false !== $position ) {
			$content = substr_replace( $content, $chunk . $char, $position, strlen( $chunk ) );
		}
	}

	// Return the cyphered content
	return $content;
}
add_filter( 'the_content', 'Heidenberg\\Common\\cypher', 99 ); // After do_shortcodes()
