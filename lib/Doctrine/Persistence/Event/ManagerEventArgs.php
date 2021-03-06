<?php

declare(strict_types=1);

namespace Doctrine\Persistence\Event;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\ObjectManager;

/**
 * Provides event arguments for the preFlush event.
 */
class ManagerEventArgs extends EventArgs
{
    /** @var ObjectManager */
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieves the associated ObjectManager.
     */
    public function getObjectManager() : ObjectManager
    {
        return $this->objectManager;
    }
}
