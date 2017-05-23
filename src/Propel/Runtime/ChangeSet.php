<?php


namespace Propel\Runtime;


class ChangeSet {

    protected $inserts = [];

    protected $updates = [];

    protected $deletions = [];

//    protected $commitId;
//
//    /**
//     * @var UnitOfwork
//     */
//    protected $unitOfWork;
//
//    function __construct($commitId, UnitOfWork $unitOfWork)
//    {
//        $this->commitId = $commitId;
//        $this->unitOfWork = $unitOfWork;
//    }

//    /**
//     * @return mixed
//     */
//    public function getCommitId()
//    {
//        return $this->commitId;
//    }

    public function scheduleInsert($entity): void
    {
        $id = spl_object_hash($entity);

        $this->inserts[$id] = $entity;
        unset($this->updates[$id]);
        unset($this->deletions[$id]);
    }

    public function scheduleUpdate($entity): void
    {
        $id = spl_object_hash($entity);

        $this->updates[$id] = $entity;
        unset($this->inserts[$id]);
        unset($this->deletions[$id]);
    }

    public function scheduleDelete($entity): void
    {
        $id = spl_object_hash($entity);

        $this->deletions[$id] = $entity;
        unset($this->inserts[$id]);
        unset($this->updates[$id]);
    }

    /**
     * @return array
     */
    public function getInserts(): array
    {
        return $this->inserts;
    }

    /**
     * @return array
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }

    /**
     * @return array
     */
    public function getDeletions(): array
    {
        return $this->deletions;
    }
}
