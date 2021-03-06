<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Mapping\Driver;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Mapping\MappingException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use ReflectionClass;
use RegexIterator;
use function array_merge;
use function array_unique;
use function assert;
use function get_class;
use function get_declared_classes;
use function in_array;
use function is_array;
use function is_dir;
use function is_string;
use function preg_match;
use function preg_quote;
use function realpath;
use function str_replace;
use function strpos;

/**
 * The AnnotationDriver reads the mapping metadata from docblock annotations.
 */
abstract class AnnotationDriver implements MappingDriver
{
    /**
     * The annotation reader.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * The paths where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected $paths = [];

    /**
     * The paths excluded from path where to look for mapping files.
     *
     * @var array<int, string>
     */
    protected $excludePaths = [];

    /**
     * The file extension of mapping documents.
     *
     * @var string
     */
    protected $fileExtension = '.php';

    /**
     * Cache for AnnotationDriver#getAllClassNames().
     *
     * @var array<int, string>|null
     */
    protected $classNames;

    /**
     * Name of the entity annotations as keys.
     *
     * @var array<string, int>
     */
    protected $entityAnnotationClasses = [];

    /**
     * Initializes a new AnnotationDriver that uses the given AnnotationReader for reading
     * docblock annotations.
     *
     * @param Reader               $reader The AnnotationReader to use, duck-typed.
     * @param string|string[]|null $paths  One or multiple paths where mapping classes can be found.
     */
    public function __construct(Reader $reader, $paths = null)
    {
        $this->reader = $reader;

        if ($paths === '' || $paths === [] || $paths === null) {
            return;
        }

        if (! is_array($paths)) {
            $paths = [$paths];
        }

        $this->addPaths($paths);
    }

    /**
     * Appends lookup paths to metadata driver.
     *
     * @param array<int, string> $paths
     */
    public function addPaths(array $paths) : void
    {
        $this->paths = array_unique(array_merge($this->paths, $paths));
    }

    /**
     * Retrieves the defined metadata lookup paths.
     *
     * @return array<int, string>
     */
    public function getPaths() : array
    {
        return $this->paths;
    }

    /**
     * Append exclude lookup paths to metadata driver.
     *
     * @param array<int, string> $paths
     */
    public function addExcludePaths(array $paths) : void
    {
        $this->excludePaths = array_unique(array_merge($this->excludePaths, $paths));
    }

    /**
     * Retrieve the defined metadata lookup exclude paths.
     *
     * @return array<int, string>
     */
    public function getExcludePaths() : array
    {
        return $this->excludePaths;
    }

    /**
     * Retrieve the current annotation reader
     */
    public function getReader() : Reader
    {
        return $this->reader;
    }

    /**
     * Gets the file extension used to look for mapping files under.
     */
    public function getFileExtension() : string
    {
        return $this->fileExtension;
    }

    /**
     * Sets the file extension used to look for mapping files under.
     *
     * @param string $fileExtension The file extension to set.
     */
    public function setFileExtension(string $fileExtension) : void
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Returns whether the class with the specified name is transient. Only non-transient
     * classes, that is entities and mapped superclasses, should have their metadata loaded.
     *
     * A class is non-transient if it is annotated with an annotation
     * from the {@see AnnotationDriver::entityAnnotationClasses}.
     */
    public function isTransient(string $className) : bool
    {
        $classAnnotations = $this->reader->getClassAnnotations(new ReflectionClass($className));

        foreach ($classAnnotations as $annot) {
            if (isset($this->entityAnnotationClasses[get_class($annot)])) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames() : array
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if ($this->paths === []) {
            throw MappingException::pathRequired();
        }

        $classes       = [];
        $includedFiles = [];

        foreach ($this->paths as $path) {
            if (! is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+' . preg_quote($this->fileExtension) . '$/i',
                RecursiveRegexIterator::GET_MATCH
            );

            foreach ($iterator as $file) {
                $sourceFile = $file[0];

                if (preg_match('(^phar:)i', $sourceFile) === false) {
                    $sourceFile = realpath($sourceFile);
                }

                foreach ($this->excludePaths as $excludePath) {
                    $realpath = realpath($excludePath);
                    assert(is_string($realpath));

                    $exclude = str_replace('\\', '/', $realpath);
                    $current = str_replace('\\', '/', $sourceFile);

                    if (strpos($current, $exclude) !== false) {
                        continue 2;
                    }
                }

                require_once $sourceFile;

                $includedFiles[] = $sourceFile;
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new ReflectionClass($className);

            $sourceFile = $rc->getFileName();

            if (! in_array($sourceFile, $includedFiles, true) || $this->isTransient($className)) {
                continue;
            }

            $classes[] = $className;
        }

        $this->classNames = $classes;

        return $classes;
    }
}
