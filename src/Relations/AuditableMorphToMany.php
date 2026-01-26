<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Relations;

use A2ZWeb\AuditableRelations\Pivots\AuditableMorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Override;

/**
 * A wrapper for MorphToMany relationships that creates audit trails for changes.
 */
class AuditableMorphToMany extends MorphToMany
{
    use SavesAuditsForRelations;

    public function __construct(MorphToMany $relation)
    {
        foreach (get_object_vars($relation) as $k => $v) {
            $this->{$k} = $v;
        }
    }

    #[Override]
    public function newPivot(...$args): AuditableMorphPivot
    {
        $pivot = parent::newPivot(...$args);
        if ($pivot instanceof MorphPivot) {
            $pivot = new AuditableMorphPivot($pivot, $this);
        }

        return $pivot;
    }

    /**
     * Audit detach from the parent of the relation.
     *
     * {@inheritdoc}
     */
    public function detach($ids = null, $touch = true): int
    {
        $old = $this->getResults()->toArray();

        $result = parent::detach($ids, $touch);

        $this->auditRelationshipChange(
            relationName: $this->getRelationName(),
            eventName: 'detached',
            oldValues: $old,
            newValues: $this->getResults()->toArray()
        );

        return $result;
    }

    public function sync($ids, $detaching = true): array
    {
        $old = $this->getResults()->toArray();

        $result = parent::sync($ids, $detaching);

        $this->auditRelationshipChange(
            relationName: $this->getRelationName(),
            eventName: 'synced',
            oldValues: $old,
            newValues: $this->getResults()->toArray()
        );

        return $result;
    }
}
