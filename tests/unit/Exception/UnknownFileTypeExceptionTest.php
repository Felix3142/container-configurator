<?php

namespace tests\unit\TomPHP\ContainerConfigurator\Exception;

use DomainException;
use PHPUnit_Framework_TestCase;
use TomPHP\ContainerConfigurator\Exception\Exception;
use TomPHP\ContainerConfigurator\Exception\UnknownFileTypeException;

final class UnknownFileTypeExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testItImplementsTheBaseExceptionType()
    {
        assertInstanceOf(Exception::class, new UnknownFileTypeException());
    }

    public function testItIsADomainException()
    {
        assertInstanceOf(DomainException::class, new UnknownFileTypeException());
    }

    public function testItCanBeCreatedFromFileExtension()
    {
        $exception = UnknownFileTypeException::fromFileExtension('.yml', ['.json', '.php']);

        assertSame(
            'No reader configured for ".yml" files; readers are available for [".json", ".php"].',
            $exception->getMessage()
        );
    }
}
