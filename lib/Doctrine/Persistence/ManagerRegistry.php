<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

/**
 * Contract covering object managers for a Doctrine persistence layer ManagerRegistry class to implement.
 */
interface ManagerRegistry extends ConnectionRegistry
{
    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName() : string;

    /**
     * Gets a named object manager.
     *
     * @param string $name The object manager name (null for the default one).
     */
    public function getManager(?string $name = null) : ObjectManager;

    /**
     * Gets an array of all registered object managers.
     *
     * @return array<string, ObjectManager> An array of ObjectManager instances
     */
    public function getManagers() : array;

    /**
     * Resets a named object manager.
     *
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     */
    public function resetManager(?string $name = null) : ObjectManager;

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     *
     * @return string The full namespace.
     */
    public function getAliasNamespace(string $alias) : string;

    /**
     * Gets all object manager names.
     *
     * @return array<string, string> An array of object manager names.
     */
    public function getManagerNames() : array;

    /**
     * Gets the ObjectRepository for a persistent object.
     *
     * @param string $persistentObject      The name of the persistent object.
     * @param string $persistentManagerName The object manager name (null for the default one).
     */
    public function getRepository(
        string $persistentObject,
        ?string $persistentManagerName = null
    ) : ObjectRepository;

    /**
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     */
    public function getManagerForClass(string $class) : ?ObjectManager;
}
