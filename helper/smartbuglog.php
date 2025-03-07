<?php

//require_once __DIR__ . '/vendor/autoload.php'; // تأكد من تثبيت Monolog

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Ramsey\Uuid\Uuid;

function uuid() {
    return Uuid::uuid4()->toString();
}

function handlePDOException(PDOException $e) {
    $uuid = uuid();
    $errorMessage = sprintf(
        "[%s] PDOException: %s in %s on line %d (UUID: %s)\nSQLSTATE: %s\nCode: %s\nTrace: %s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $uuid,
        $e->getCode(),
        $e->getSqlState(),
        $e->getTraceAsString()
    );

    $log = new Logger('pdo_errors');
    $log->pushHandler(new StreamHandler(__DIR__ . '/pdo_error.log', Logger::ERROR));
    $log->error($errorMessage);

    // في وضع الإنتاج، قد نرغب في عرض رسالة خطأ عامة للمستخدمين
    if (defined('PRODUCTION_MODE') && PRODUCTION_MODE === true) {
        echo "An error occurred. Please try again later.";
    } else {
        echo "<pre>";
        echo $errorMessage . "\n";
        echo "</pre>";
    }
}

function handleError($errno = null, $errstr = null, $errfile = null, $errline = null) {
    static $isShutdown = false;

    if ($errno === null) {
        $isShutdown = true;
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR))) {
            $errno = $error['type'];
            $errstr = $error['message'];
            $errfile = $error['file'];
            $errline = $error['line'];
        } else {
            return;
        }
    }

    // التحقق من استثناءات PDOException
    if ($errstr instanceof PDOException) {
        handlePDOException($errstr);
        return; // لا داعي لمعالجة الخطأ العام بعد معالجة PDOException
    }

    $uuid = uuid();

    $errorType = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parsing Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
    ];

    $errorName = isset($errorType[$errno]) ? $errorType[$errno] : 'Unknown Error';
    $errorMessage = sprintf(
        "[%s] %s: %s in %s on line %d (UUID: %s)",
        date('Y-m-d H:i:s'),
        $errorName,
        $errstr,
        $errfile,
        $errline,
        $uuid
    );

    $log = new Logger('errors');
    $log->pushHandler(new StreamHandler(__DIR__ . '/error.log', Logger::ERROR));

    if ($errno & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
        $log->error($errorMessage);
    } else if ($errno & (E_WARNING | E_USER_WARNING | E_COMPILE_WARNING | E_CORE_WARNING)) {
        $log->warning($errorMessage);
    } else if ($errno & (E_NOTICE | E_USER_NOTICE | E_STRICT)) {
        $log->notice($errorMessage);
    } else {
        $log->error($errorMessage);
    }

    if (defined('PRODUCTION_MODE') && PRODUCTION_MODE === true) {
        echo "An error occured";
    } else {
        echo "<pre>";
        echo $errorMessage . "\n";
        echo "</pre>";
    }

    if ($errno === E_ERROR && !$isShutdown) {
        exit(1);
    }
}

set_exception_handler('handleError');
set_error_handler('handleError');
register_shutdown_function('handleError');
?>