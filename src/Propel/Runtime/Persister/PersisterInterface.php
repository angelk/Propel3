<?php

namespace Propel\Runtime\Persister;

use Propel\Runtime\Map\EntityMap;

interface PersisterInterface
{
    public function commit(EntityMap $entityMap, $entities): void;
    public function remove(EntityMap $entityMap, $entities): int;

    /**
     * Called when \Propel\Runtime\Session\Session::commit is finished (with all its rounds)
     */
    public function sessionCommitEnd();
}
