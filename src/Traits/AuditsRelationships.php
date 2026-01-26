<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Traits;

use A2ZWeb\AuditableRelations\Relations\AuditableBelongsToMany;
use A2ZWeb\AuditableRelations\Relations\AuditableMorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

trait AuditsRelationships
{
    /**
     * Wrap a supported relationship to make it auditable.
     * To be used inside the relationship definition method on a model.
     *
     * @param  Relation  $relation  The original Eloquent relationship instance.
     */
    protected function auditableRelation(Relation $relation): Relation
    {
        if ($relation instanceof MorphToMany) {
            return new AuditableMorphToMany($relation);
        }
        if ($relation instanceof BelongsToMany) {
            return new AuditableBelongsToMany($relation);
        }

        // In the future, other supported relation types can be added here.
        // E.g., if ($relation instanceof HasMany) { ... }

        throw new InvalidArgumentException(get_class($relation).' relationship type is not currently supported for auditing.');
    }
}
