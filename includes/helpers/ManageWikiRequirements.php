<?php

class ManageWikiRequirements {
	public static function process( string $dbname, array $actions, IContextSource $context, array $formData = [] ) {
		// Produces an array of steps and results (so we can fail what we can't do but apply what works)
		$stepResponse = [];

		foreach ( $actions as $action => $data ) {
			if ( $action == 'permissions' ) {
				$stepResponse['permissions'] = self::permissions( $data, $context );
			} elseif ( $action == 'extensions' ) {
				$stepResponse['extensions'] = self::extensions( $dbname, $data, $formData );
			} elseif ( $action == 'articles' ) {
				$stepResponse['articles'] = self::articles( $data );
			} elseif ( $action == 'pages' ) {
				$stepResponse['pages'] = self::pages( $data );
			} else {
				return false;
			}
		}

		return !(bool)array_search( false, $stepResponse );
	}

	private static function permissions( array $data, IContextSource $context ) {
		foreach ( $data as $perm ) {
			if ( !$context->getUser()->isAllowed( $perm ) ) {
				return false;
			}
		}

		return true;
	}

	private static function extensions( string $dbname, array $data, array $formData ) {
		$remoteWiki = RemoteWiki::newFromName( $dbname );

		foreach ( $data as $extension ) {
			if ( !$remoteWiki->hasExtension( $extension ) || isset( $formData["ext-$extension"] ) && !$formData["ext-$extension"] ) {
				return false;
			}
		}

		return true;
	}

	private static function articles( string $lim ) {
		return eval( "return " . SiteStats::articles() . " $lim;" );
	}

	private static function pages( string $lim ) {
		return eval( "return " . SiteStats::pages() . " $lim;" );
	}
}
