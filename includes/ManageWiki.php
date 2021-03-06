<?php

class ManageWiki {
	public static function checkSetup( string $module, bool $verbose = false, $out = false ) {
		global $wgManageWiki, $wgManageWikiCDBDirectory;

		// Checks ManageWiki module is enabled before doing anything
		// $verbose means output an error. Otherwise return true/false.

		if ( $wgManageWiki[$module] ) {
			if ( $module == 'cdb' ) {
				return (bool)$wgManageWikiCDBDirectory;
			}

			return true;
		} else {
			if ( $verbose && $out ) {
				$out->addWikiMsg( 'managewiki-disabled', $module );
			}

			return false;
		}
	}

	public static function listModules( bool $public = true ) {
		global $wgManageWiki, $wgManageWikiBackendModules;

		$enabledModules = array_keys( $wgManageWiki, true );

		if ( $public ) {
			return array_diff( $enabledModules, $wgManageWikiBackendModules );
		}

		return $enabledModules;
	}

	public static function checkPermission( RemoteWiki $rm, User $user, bool $verbose = false, string $perm = "" ) {
		$maxPerm = ( (bool)$perm ) ? $perm : 'managewiki';

		if ( !$user->isAllowed( $maxPerm ) ) {
			if ( $verbose ) {
				throw new PermissionsError( $maxPerm );
			}

			return false;
		}

		// Early implementation of MWLocked feature - down as RemoteWiki but will be WikiManager
		//if ( $rm->isMWLocked() ) {
		//	if ( $verbose ) {
		//		return wfMessage( 'managewiki-mwlocked' )->plain();
		//	}

		//	return false;
		//}

		return true;
	}

	public static function getTimezoneList() {
		$identifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

		$timeZoneList = [];

		if ( $identifiers !== false ) {
			foreach ( $identifiers as $identifier ) {
				$parts = explode( '/', $identifier, 2 );
				if ( count( $parts ) !== 2 && $parts[0] !== 'UTC' ) {
					continue;
				}

				$timeZoneList[$identifier] = $identifier;
			}
		}

		return $timeZoneList;
	}

	public static function handleMatrix( $conversion, $to ) {
		if ( $to == 'php' ) {
			// $to is php, therefore $conversion must be json
			$phpin = json_decode( $conversion, true );

			$phpout = [];

			foreach ( $phpin as $key => $value ) {
				// We may have an array, may not - let's make it one
				foreach ( (array)$value as $val ) {
					$phpout[] = "$key-$val";
				}
			}

			return $phpout;
		} elseif ( $to == 'phparray' ) {
			// $to is phparray therefore $conversion must be php as json will be already phparray'd
			$phparrayout = [];

			foreach ( $conversion as $phparray ) {
				$element = explode( '-', $phparray, 2 );
				$phparrayout[$element[0]][] = $element[1];
			}

			return $phparrayout;
		} elseif ( $to == 'json' ) {
			// $to is json, therefore $conversion must be php
			return json_encode( $conversion );
		}

		return null;
	}
}
