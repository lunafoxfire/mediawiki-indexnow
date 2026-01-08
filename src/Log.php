<?php

namespace MediaWiki\Extension\IndexNowNotifier;

use MediaWiki\Logger\LoggerFactory;

class Log
{
    private static $logger = null;
    public static function getLogger() {
        if ( self::$logger === null ) {
            self::$logger = LoggerFactory::getInstance('IndexNowNotifier');
        }
        return self::$logger;
    }

    public const OFF = 'off';
    public const ERROR = 'error';
    public const WARN = 'warn';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    private static $logValueMap = [
        self::OFF => -1,
        self::ERROR => 0,
        self::WARN  => 1,
        self::INFO  => 2,
        self::DEBUG => 3,
    ];

    private static $logMethodMap = [
        self::ERROR => 'error',
        self::WARN  => 'warning',
        self::INFO  => 'info',
        self::DEBUG => 'debug',
    ];

    private static $logTagMap = [
        self::ERROR => 'ERR ',
        self::WARN  => 'WARN',
        self::INFO  => 'INFO',
        self::DEBUG => 'DBG ',
    ];

    private static function getLogValue( $logLevel ) {
        return self::$logValueMap[$logLevel] ?? -1;
    }

    private static function getLogMethod( $logLevel ) {
        return self::$logMethodMap[$logLevel] ?? null;
    }

    private static function getLogTag( $logLevel ) {
        return self::$logTagMap[$logLevel] ?? "    ";
    }

    private static function hookLogInternal( $logLevel, $hookName, $pageTitle, $message ) {
        $logValue = self::getLogValue( $logLevel );
        $logMethod = self::getLogMethod( $logLevel );
        $logTag = self::getLogTag( $logLevel );
        if ( $logMethod === null ) return;

        global $wgIndexNowNotifier_LogLevel;
        $configLogValue = self::getLogValue( $wgIndexNowNotifier_LogLevel );
        if ($logValue > $configLogValue) return;

        $fullMessage = "$hookName\n[$logTag][$pageTitle] $message";
        self::getLogger()->$logMethod($fullMessage);
    }

    public static function hookError( $hookName, $pageTitle, $message ) {
        self::hookLogInternal( self::ERROR, $hookName, $pageTitle, $message );
    }

    public static function hookWarn( $hookName, $pageTitle, $message ) {
        self::hookLogInternal( self::WARN, $hookName, $pageTitle, $message );
    }

    public static function hookInfo( $hookName, $pageTitle, $message ) {
        self::hookLogInternal( self::INFO, $hookName, $pageTitle, $message );
    }

    public static function hookDebug( $hookName, $pageTitle, $message ) {
        self::hookLogInternal( self::DEBUG, $hookName, $pageTitle, $message );
    }
}
