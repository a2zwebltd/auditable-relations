# Auditable Relations

Automatic auditing for Eloquent relationship changes (attach, detach, sync) using Laravel Auditing.

## Features

- 🔍 **Automatic Tracking**: Captures before/after state of relationship changes
- 📝 **Detailed Logs**: Stores complete related model data, not just IDs
- 🎯 **Event-Based**: Uses `owen-it/laravel-auditing` for consistent audit logs
- ⚡ **Zero Configuration**: Works out of the box after trait inclusion
- 🔧 **Flexible**: Supports BelongsToMany and MorphToMany relationships
- 📦 **Lightweight**: Minimal overhead, maximum value

## Installation

```bash
composer require a2zwebltd/auditable-relations
```

## Requirements

- PHP 8.1+
- Laravel 10/11/12
- owen-it/laravel-auditing 13/14

## Quick Start

### 1. Implement Auditable on Your Model

```php
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Post extends Model implements Auditable
{
    use AuditableTrait;
}
```

### 2. Add the Trait and Wrap Your Relationships

```php
use A2ZWeb\AuditableRelations\Traits\AuditsRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model implements Auditable
{
    use AuditableTrait;
    use AuditsRelationships;

    public function tags(): BelongsToMany
    {
        return $this->auditableRelation(
            $this->belongsToMany(Tag::class)
        );
    }
}
```

That's it! Now all changes to the `tags` relationship will be automatically audited.

## Usage Examples

### Basic Usage

```php
$post = Post::find(1);

// These operations are automatically audited
$post->tags()->attach([1, 2, 3]);
$post->tags()->detach([2]);
$post->tags()->sync([1, 3, 4]);
```

### What Gets Logged

Each operation creates an audit log entry like:

```php
[
    'event' => 'synced', // or 'attached', 'detached'
    'auditable_type' => 'App\Models\Post',
    'auditable_id' => 1,
    'old_values' => [
        'tags' => [
            ['id' => 1, 'name' => 'Laravel', 'created_at' => '...'],
            ['id' => 2, 'name' => 'PHP', 'created_at' => '...'],
        ]
    ],
    'new_values' => [
        'tags' => [
            ['id' => 1, 'name' => 'Laravel', 'created_at' => '...'],
            ['id' => 3, 'name' => 'Vue', 'created_at' => '...'],
            ['id' => 4, 'name' => 'Tailwind', 'created_at' => '...'],
        ]
    ],
    'user_id' => 123,
    'user_type' => 'App\Models\User',
]
```

### Multiple Relationships

You can audit multiple relationships on the same model:

```php
class Post extends Model implements Auditable
{
    use AuditableTrait;
    use AuditsRelationships;

    public function tags(): BelongsToMany
    {
        return $this->auditableRelation(
            $this->belongsToMany(Tag::class)
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->auditableRelation(
            $this->belongsToMany(Category::class)
        );
    }

    public function attachments(): MorphToMany
    {
        return $this->auditableRelation(
            $this->morphToMany(File::class, 'attachable')
        );
    }
}
```

### Polymorphic Relationships

Works seamlessly with polymorphic relationships:

```php
class Post extends Model implements Auditable
{
    use AuditableTrait;
    use AuditsRelationships;

    public function images(): MorphToMany
    {
        return $this->auditableRelation(
            $this->morphToMany(Image::class, 'imageable')
        );
    }
}
```

## Supported Relationships

- ✅ `BelongsToMany`
- ✅ `MorphToMany`
- ⏳ Other relationship types (planned)

## Configuration

The package respects Laravel Auditing's global configuration:

```php
// config/audit.php
return [
    'enabled' => true,        // Disable to stop all auditing
    'console' => false,       // Audit console commands
    // ... other audit config
];
```

## Advanced Usage

### Conditional Auditing

You can control auditing at runtime:

```php
// Temporarily disable auditing
config(['audit.enabled' => false]);
$post->tags()->sync([1, 2, 3]); // Not audited
config(['audit.enabled' => true]);
```

### Custom Event Names

The package uses standard event names:
- `attached` - When models are attached
- `detached` - When models are detached
- `synced` - When models are synced

### Accessing Audit Logs

```php
use OwenIt\Auditing\Models\Audit;

// Get all audits for a model
$audits = Audit::where('auditable_type', Post::class)
    ->where('auditable_id', 1)
    ->get();

// Get relationship change audits
$relationshipAudits = Audit::where('auditable_type', Post::class)
    ->whereIn('event', ['attached', 'detached', 'synced'])
    ->get();
```

## How It Works

### Architecture

1. **Trait Application**: `AuditsRelationships` trait wraps relationship definitions
2. **Relationship Proxy**: Creates auditable versions of `BelongsToMany` and `MorphToMany`
3. **Operation Interception**: Intercepts `attach()`, `detach()`, and `sync()` calls
4. **State Capture**: Records relationship state before and after the operation
5. **Event Dispatch**: Fires `AuditCustom` event with the captured data
6. **Audit Creation**: Laravel Auditing processes the event and creates the audit log

### Performance

- Minimal overhead: Only one additional query per operation (to capture current state)
- Efficient: Uses existing Laravel Auditing infrastructure
- Asynchronous-ready: Compatible with queued audit processing

## Comparison with Alternatives

Unlike other solutions:
- ✅ **Complete Data**: Stores full related model data, not just IDs
- ✅ **Native Integration**: Uses Laravel Auditing's standard audit model
- ✅ **Zero Config**: No additional tables or setup required
- ✅ **Framework-Native**: Uses Laravel's event system

## Troubleshooting

### Audits Not Appearing

1. Verify auditing is enabled:
```php
config('audit.enabled'); // Should be true
```

2. Check model implements `Auditable`:
```php
class Post extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;
}
```

3. Ensure relationship is wrapped:
```php
public function tags(): BelongsToMany
{
    return $this->auditableRelation( // Don't forget this!
        $this->belongsToMany(Tag::class)
    );
}
```

### Console Commands Not Audited

Enable console auditing in `config/audit.php`:
```php
'console' => true,
```

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please email contact@a2zweb.co.

## Credits

- [A2Z Web Ltd](https://a2zweb.co)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
