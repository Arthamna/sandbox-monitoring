<?php

namespace App\Services;

use App\Models\CtfLog;

class CtfLogger
{
    /**
     * Log an info-level message.
     */
    public static function info(string $source, string $message, array $context = []): CtfLog
    {
        return self::log('info', $source, $message, $context);
    }

    /**
     * Log a warning-level message.
     */
    public static function warning(string $source, string $message, array $context = []): CtfLog
    {
        return self::log('warning', $source, $message, $context);
    }

    /**
     * Log an error-level message.
     */
    public static function error(string $source, string $message, array $context = []): CtfLog
    {
        return self::log('error', $source, $message, $context);
    }

    /**
     * Log a debug-level message.
     */
    public static function debug(string $source, string $message, array $context = []): CtfLog
    {
        return self::log('debug', $source, $message, $context);
    }

    /**
     * Write a log entry to the ctf_logs table.
     */
    protected static function log(string $level, string $source, string $message, array $context = []): CtfLog
    {
        return CtfLog::create([
            'level' => $level,
            'source' => $source,
            'message' => $message,
            'context' => $context,
        ]);
    }
}
