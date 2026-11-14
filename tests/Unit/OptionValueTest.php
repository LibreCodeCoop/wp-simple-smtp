<?php

namespace LibreCodeCoop\SimpleSmtp\Tests\Unit;

use LibreCodeCoop\SimpleSmtp\OptionValue;
use PHPUnit\Framework\TestCase;

final class OptionValueTest extends TestCase {

	/**
	 * @dataProvider provide_smtp_auth_values
	 */
	public function test_reads_smtp_auth( $value, $expected ) {
		$this->assertSame( $expected, OptionValue::smtp_auth( $value ) );
	}

	public static function provide_smtp_auth_values() {
		yield 'true'               => array( true, true );
		yield 'false'              => array( false, false );
		yield 'one'                => array( '1', true );
		yield 'zero'               => array( '0', false );
		yield 'a number above one' => array( '2', true );
		yield 'the integer one'    => array( 1, true );
		yield 'true as text'       => array( 'true', true );
		yield 'true in uppercase'  => array( 'TRUE', true );
		yield 'on'                 => array( 'on', true );
		yield 'ok'                 => array( 'Ok', true );
		yield 'false as text'      => array( 'false', false );
		yield 'yes'                => array( 'yes', false );
		yield 'off'                => array( 'off', false );
		yield 'empty'              => array( '', false );
		yield 'missing'            => array( null, false );
		yield 'an array'           => array( array( '1' ), false );
	}

	/**
	 * @dataProvider provide_ssl_values
	 */
	public function test_reads_an_ssl_option( $value, $expected ) {
		$this->assertSame( $expected, OptionValue::ssl( $value ) );
	}

	public static function provide_ssl_values() {
		yield 'one'                   => array( '1', true );
		yield 'true'                  => array( 'true', true );
		yield 'true in uppercase'     => array( 'TRUE', true );
		yield 'on'                    => array( 'on', true );
		yield 'yes'                   => array( 'yes', true );
		yield 'zero'                  => array( '0', false );
		yield 'false'                 => array( 'false', false );
		yield 'off'                   => array( 'off', false );
		yield 'no'                    => array( 'no', false );
		yield 'surrounding spaces'    => array( ' no ', false );
		yield 'empty keeps default'   => array( '', null );
		yield 'missing keeps default' => array( false, null );
		yield 'sim keeps default'     => array( 'sim', null );
		yield 'enabled keeps default' => array( 'enabled', null );
		yield 'ok keeps default'      => array( 'ok', null );
	}
}
