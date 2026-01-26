<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Pivots;

use A2ZWeb\AuditableRelations\Relations\AuditableMorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

/**
 * @method static Builder<static>|AuditableMorphPivot newModelQuery()
 * @method static Builder<static>|AuditableMorphPivot newQuery()
 * @method static Builder<static>|AuditableMorphPivot query()
 *
 * @mixin \Eloquent
 */
class AuditableMorphPivot extends MorphPivot
{
    protected ?AuditableMorphToMany $parentRelation = null;

    public function __construct(?MorphPivot $pivot = null, ?AuditableMorphToMany $parentRelation = null)
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
