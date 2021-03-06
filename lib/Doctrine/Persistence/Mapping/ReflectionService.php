<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping;

use ReflectionClass;
use ReflectionProperty;

/**
 * Very simple reflection service abstraction.
 *
 * This is required inside metadata layers that may require either
 * static or runtime reflection.
 */
interface ReflectionService
{
    /**
     * Returns an array of the parent classes (not interfaces) for the given class.
     *
     * @return string[]
     *
     * @throws MappingException
     */
    public function getParentClasses(string $class) : array;

    /**
     * Returns the shortname of a class.
     */
    public function getClassShortName(string $class) : string;

    public function getClassNamespace(string $class) : string;

    /**
     * Returns a reflection class instance or null.
     */
    public function getClass(string $class) : ?ReflectionClass;

    /**
     * Returns an accessible property (setAccessible(true)) or null.
     */
    public function getAccessibleProperty(string $class, string $property) : ?ReflectionProperty;

    /**
     * Checks if the class have a public method with the given name.
     */
    public function hasPublicMethod(string $class, string $method) : bool;
}
