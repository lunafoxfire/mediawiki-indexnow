<?php

namespace MediaWiki\Extension\IndexNowNotifier;

use MediaWiki\Logger\LoggerFactory;

class Utils
{
    public static function handleCreate( $hookName, $title, $namespace, $user ) {
        global $wgIndexNowNotifier_NotifyCreate;
        global $wgIndexNowNotifier_IgnoreBot;
        Log::hookDebug( $hookName, $title, 'detected page creation' );

        if ( !$wgIndexNowNotifier_NotifyCreate ) {
            Log::hookDebug( $hookName, $title, 'NotifyCreate disabled: skipping' );
			return;
        }
        if ( $namespace !== 0 ) {
            Log::hookDebug( $hookName, $title, 'Not main namespace: skipping' );
            return;
        }
        if ( $wgIndexNowNotifier_IgnoreBot && $user->isBot() ) {
            Log::hookDebug( $hookName, $title, 'IgnoreBot set: skipping' );
			return;
		}

        $url = $title->getFullURL();
        self::notifyURL( $hookName, $title, $url );
    }

    public static function handleEdit( $hookName, $title, $namespace, $user, $isMinor, $isNull ) {
        global $wgIndexNowNotifier_NotifyEdit;
        global $wgIndexNowNotifier_IgnoreMinor;
        global $wgIndexNowNotifier_IgnoreBot;
        Log::hookDebug( $hookName, $title, 'detected page edit' );

        if ( !$wgIndexNowNotifier_NotifyEdit ) {
            Log::hookDebug( $hookName, $title, 'NotifyEdit disabled: skipping' );
			return;
        }
        if ( $namespace !== 0 ) {
            Log::hookDebug( $hookName, $title, 'Not main namespace: skipping' );
            return;
        }
        if ( $wgIndexNowNotifier_IgnoreBot && $user->isBot() ) {
            Log::hookDebug( $hookName, $title, 'IgnoreBot set: skipping' );
			return;
		}
		if ( $isNull ) {
            Log::hookDebug( $hookName, $title, 'Null edit: skipping' );
			return;
		}
        if ( $wgIndexNowNotifier_IgnoreMinor && $isMinor ) {
            Log::hookDebug( $hookName, $title, 'IgnoreMinor set: skipping' );
			return;
		}

        $url = $title->getFullURL();
        self::notifyURL( $hookName, $title, $url );
    }

    public static function handleDelete( $hookName, $title, $namespace, $user ) {
        global $wgIndexNowNotifier_NotifyDelete;
        global $wgIndexNowNotifier_IgnoreBot;
        Log::hookDebug( $hookName, $title, 'detected page delete' );

        if ( !$wgIndexNowNotifier_NotifyDelete ) {
            Log::hookDebug( $hookName, $title, 'NotifyDelete disabled: skipping' );
			return;
        }
        if ( $namespace !== 0 ) {
            Log::hookDebug( $hookName, $title, 'Not main namespace: skipping' );
            return;
        }
        if ( $wgIndexNowNotifier_IgnoreBot && $user->isBot() ) {
            Log::hookDebug( $hookName, $title, 'IgnoreBot set: skipping' );
			return;
		}

        $url = $title->getFullURL();
        self::notifyURL( $hookName, $title, $url );
    }

    public const USER_AGENT = 'IndexNowNotifier/1.0 (github.com/lunafoxfire)';
    public static function notifyURL( $hookName, $pageTitle, $url ) {
        global $wgIndexNowNotifier_SecretKey;
        global $wgIndexNowNotifier_IndexNowEndpoint;

        $url = rawurldecode($url);
        $host = parse_url( $url, PHP_URL_HOST );
        $payload = json_encode([
            'host' => $host,
            'key' => $wgIndexNowNotifier_SecretKey,
            'urlList' => [ $url ]
        ]);

        Log::hookInfo( $hookName, $pageTitle, "IndexNow request: Host: $host | URL: $url" );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $wgIndexNowNotifier_IndexNowEndpoint );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_USERAGENT, self::USER_AGENT );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen( $payload )
        ]);

        $response = curl_exec( $ch );
        $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        Log::hookInfo( $hookName, $pageTitle, "IndexNow response: ($httpCode) $response" );
    }
}
