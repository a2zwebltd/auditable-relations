<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Pivots;

use A2ZWeb\AuditableRelations\Relations\AuditableBelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static Builder<static>|AuditablePivot newModelQuery()
 * @method static Builder<static>|AuditablePivot newQuery()
 * @method static Builder<static>|AuditablePivot query()
 *
 * @mixin \Eloquent
 */
class AuditablePivot extends Pivot
{
    protected ?AuditableBelongsToMany $parentRelation = null;

    public function __construct(?Pivot $pivot = null, ?AuditableBelongsToMany $parentRelation = null)
    {
        parent::__construct();

        if ($pivot) {
            foreach (get_object_vars($pivot) as $k => $v) {
                $this->{$k} = $v;
            }
        }

        $this->parentRelation = $parentRelation;
    }
}
