<?php

namespace LibreCodeCoop\SimpleSmtp\Tests\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class PluginFiles {

	public static function root() {
		return dirname( __DIR__, 2 );
	}

	public static function under( $directory, $suffix ) {
		$root = self::root() . '/' . $directory;

		if ( ! is_dir( $root ) ) {
			return array();
		}

		$paths = array();
		$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

		foreach ( $files as $file ) {
			if ( $file->isFile() && str_ends_with( $file->getPathname(), $suffix ) ) {
				$paths[] = substr( $file->getPathname(), strlen( self::root() ) + 1 );
			}
		}

		sort( $paths );

		return $paths;
	}
}
