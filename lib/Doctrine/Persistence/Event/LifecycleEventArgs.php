<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Lifecycle Events are triggered by the UnitOfWork during lifecycle transitions
 * of entities.
 */
class LifecycleEventArgs extends EventArgs
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var object */
    private $object;

    public function __construct(object $object, ObjectManager $objectManager)
    {
        $this->object        = $object;
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated entity.
     *
     * @deprecated
     */
    public function getEntity() : object
    {
        return $this->object;
    }

    /**
     * Retrieves the associated object.
     */
    public function getObject() : object
    {
        return $this->object;
    }

    /**
     * Retrieves the associated ObjectManager.
     */
    public function getObjectManager() : ObjectManager
    {
        return $this->objectManager;
    }
}
