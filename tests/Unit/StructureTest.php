<?php

namespace LibreCodeCoop\SimpleSmtp\Tests\Unit;

use LibreCodeCoop\SimpleSmtp\Tests\Support\PluginFiles;
use PHPUnit\Framework\TestCase;

final class StructureTest extends TestCase {

	private const PLUGIN_FILE = 'wp-simple-smtp.php';

	/**
	 * @dataProvider provide_plugin_files
	 */
	public function test_a_file_of_the_plugin_is_covered_by_the_test_named_after_it( $file ) {
		$this->assert_one_exists( self::tests_covering( $file ), $file . ' is not covered by' );
	}

	public static function provide_plugin_files() {
		$files = array_merge(
			array( self::PLUGIN_FILE ),
			PluginFiles::under( 'src', '.php' )
		);

		foreach ( $files as $file ) {
			yield $file => array( $file );
		}
	}

	/**
	 * @dataProvider provide_test_files
	 */
	public function test_a_test_covers_a_file_of_the_plugin( $file ) {
		$this->assert_one_exists( self::files_covered_by( $file ), $file . ' does not cover' );
	}

	public static function provide_test_files() {
		$files = array_merge(
			PluginFiles::under( 'tests/Unit', 'Test.php' ),
			PluginFiles::under( 'tests/Integration', 'Test.php' ),
			PluginFiles::under( 'tests/E2E', '.spec.ts' )
		);

		foreach ( $files as $file ) {
			if ( 'tests/Unit/StructureTest.php' !== $file ) {
				yield $file => array( $file );
			}
		}
	}

	private static function tests_covering( $file ) {
		if ( self::PLUGIN_FILE === $file ) {
			return array( 'tests/Integration/' . self::studly( basename( $file, '.php' ) ) . 'Test.php' );
		}

		$name = substr( $file, strlen( 'src/' ), -strlen( '.php' ) );

		return array(
			'tests/Unit/' . $name . 'Test.php',
			'tests/Integration/' . $name . 'Test.php',
		);
	}

	private static function files_covered_by( $file ) {
		if ( str_starts_with( $file, 'tests/E2E/' ) ) {
			return array( self::kebab( substr( $file, strlen( 'tests/E2E/' ), -strlen( '.spec.ts' ) ) ) . '.php' );
		}

		if ( str_starts_with( $file, 'tests/Unit/' ) ) {
			return array( 'src/' . substr( $file, strlen( 'tests/Unit/' ), -strlen( 'Test.php' ) ) . '.php' );
		}

		$name = substr( $file, strlen( 'tests/Integration/' ), -strlen( 'Test.php' ) );

		return array(
			'src/' . $name . '.php',
			self::kebab( $name ) . '.php',
		);
	}

	private function assert_one_exists( array $files, $subject ) {
		$found = array_filter(
			$files,
			static function ( $file ) {
				return file_exists( PluginFiles::root() . '/' . $file );
			}
		);

		$this->assertNotEmpty( $found, sprintf( '%s %s.', $subject, implode( ' or ', $files ) ) );
	}

	private static function studly( $name ) {
		return str_replace( ' ', '', ucwords( str_replace( '-', ' ', $name ) ) );
	}

	private static function kebab( $name ) {
		return strtolower( (string) preg_replace( '/(?<!^)[A-Z]/', '-$0', $name ) );
	}
}
