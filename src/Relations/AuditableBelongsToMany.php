<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Relations;

use A2ZWeb\AuditableRelations\Pivots\AuditablePivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A wrapper for BelongsToMany relationships that creates audit trails for changes.
 */
class AuditableBelongsToMany extends BelongsToMany
{
    use SavesAuditsForRelations;

    public function __construct(BelongsToMany $relation)
    {
        parent::__construct($relation->related->newQueryWithoutRelationships(),
            $relation->parent,
            $relation->getTable(),
            $relation->getForeignPivotKeyName(),
            $relation->getRelatedPivotKeyName(),
            $relation->getParentKeyName(),
            $relation->getRelatedKeyName(),
            $relation->getRelationName()
        );
    }

    #[Override]
    public function newPivot(...$args): AuditablePivot
    {
        $pivot = parent::newPivot(...$args);
        if (! $pivot instanceof Auditable && $pivot instanceof Pivot) {
            $pivot = new AuditablePivot($pivot, $this);
        }

        return $pivot;
    }

    public function attach($id, array $attributes = [], $touch = true): void
    {
        $old = $this->getResults()->toArray();

        parent::attach($id, $attributes, $touch);

        $this->auditRelationshipChange(
            relationName: $this->getRelationName(),
            eventName: 'attached',
            oldValues: $old,
            newValues: $this->getResults()->toArray()
        );
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
