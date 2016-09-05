<?php

namespace tests\unit\TomPHP\ConfigServiceProvider\Exception;

use PHPUnit_Framework_TestCase;
use TomPHP\ConfigServiceProvider\Exception\UnknownFileTypeException;

final class UnknownFileTypeExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testItImplementsTheBaseExceptionType()
    {
        $this->assertInstanceOf(
            'TomPHP\ConfigServiceProvider\Exception\Exception',
            new UnknownFileTypeException()
        );
    }

    public function testItIsADomainException()
    {
        $this->assertInstanceOf('DomainException', new UnknownFileTypeException());
    }

    public function testItCanBeCreatedFromFileExtension()
    {
        $exception = UnknownFileTypeException::fromFileExtension('.yml', ['.json', '.php']);

        $this->assertSame(
            'No reader configured for ".yml" files; readers are available for [".json", ".php"].',
            $exception->getMessage()
        );
    }
}