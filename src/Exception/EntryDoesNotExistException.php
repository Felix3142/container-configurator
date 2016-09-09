<?php

namespace TomPHP\ConfigServiceProvider\Exception;

use DomainException;
use TomPHP\ExceptionConstructorTools;

final class EntryDoesNotExistException extends DomainException implements Exception
{
    use ExceptionConstructorTools;

    /**
     * @param string $key
     *
     * @return self
     */
    public static function fromKey($key)
    {
        return self::create('No entry found for "%s".', [$key]);
    }
}
