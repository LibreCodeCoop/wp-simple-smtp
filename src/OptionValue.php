<?php

namespace LibreCodeCoop\SimpleSmtp;

final class OptionValue {

	public static function smtp_auth( mixed $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_numeric( $value ) ) {
			return (bool) $value;
		}
		if ( is_string( $value ) ) {
			return in_array( strtoupper( $value ), array( 'TRUE', 'ON', '1', 'OK' ), true );
		}
		return false;
	}

	public static function ssl( mixed $value ): ?bool {
		if ( '' === $value || false === $value ) {
			return null;
		}
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	}

	public static function smtp_secure( mixed $value ): ?string {
		if ( false === $value || null === $value ) {
			return '';
		}
		if ( ! is_string( $value ) ) {
			return null;
		}
		$encryption = strtolower( trim( $value ) );
		return in_array( $encryption, array( '', 'tls', 'ssl' ), true ) ? $encryption : null;
	}
}
