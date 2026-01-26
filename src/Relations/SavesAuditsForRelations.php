<?php

declare(strict_types=1);

namespace A2ZWeb\AuditableRelations\Relations;

use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;

trait SavesAuditsForRelations
{
    /**
     * Create an audit log for a relationship change.
     * This is called by the Auditable Relation wrapper.
     *
     * @param  string  $relationName  The name of the relationship that changed.
     * @param  string  $eventName  The name of the event (e.g., 'attached', 'detached', 'synced').
     * @param  array  $oldValues  The old values before the change.
     * @param  array  $newValues  The new values after the change.
     */
    public function auditRelationshipChange(string $relationName, string $eventName, array $oldValues, array $newValues): void
    {
        // Skip auditing if globally disabled
        if (! config('audit.enabled')) {
            return;
        }

        // Skip auditing if running in console and console auditing is disabled
        if (app()->runningInConsole() && ! config('audit.console')) {
            return;
        }

        $model = clone $this->parent;
        $model->auditEvent = $eventName;
        $model->isCustomEvent = true;
        $model->auditCustomOld = [$relationName => $oldValues];
        $model->auditCustomNew = [$relationName => $newValues];

        Event::dispatch(new AuditCustom($model));
    }
}
