<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [ // Заповнювані поля при створенні/оновленні орендаря
        'name',
        'domain',
        'status',
        'trial_ends_at',
        'expires_at',
        'timezone',
        'locale',
        'uuid',
        'parent_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [ //
        'trial_ends_at' => 'datetime', // коли закінчується trial
        'expires_at' => 'datetime', // коли закінчується підписка
    ];

    /* ----------  зв’язки  ---------- */

    public function users(): BelongsToMany
    {
        // Зв'язок "багато-до-багатьох" з моделлю User
        // Використовуємо проміжну таблицю 'tenant_users'
        // Додаємо поле 'is_owner' для визначення власника орендаря
        // Додаємо timestamps для відстеження створення/оновлення зв'язків
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot('is_owner')
            ->withTimestamps();
    }

    // Зв'язок з моделлю User для створення та оновлення
    // Використовуємо поля 'created_by' та 'updated_by' для зберігання ID користувачів
    // які створили або оновили запис орендаря
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Зв'язок з моделлю Tenant для батьківського орендаря
    // Використовуємо поле 'parent_id' для зберігання ID батьківського орендаря
    // Це дозволяє створювати ієрархію орендарів (наприклад, компанія - підрозділ)
    // Додаємо методи для отримання батьківського орендаря та дочірніх орендарів
    // Батьківський орендар - це той, який створив цей орендар
    // Дочірні орендарі - це ті, які створені під цим орендарем
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Tenant::class, 'parent_id');
    }

    /* ----------  авто-UUID  ---------- */

    protected static function booted(): void
    {
        // Автоматично генеруємо UUID при створенні нового орендаря
        // Якщо UUID не заданий, то генеруємо його
        // Це дозволяє уникнути дублювання UUID при створенні нових записів
        static::creating(function (Tenant $tenant) {
            if (! $tenant->uuid) {
                $tenant->uuid = (string) Str::uuid(); // генеруємо UUID при створенні
            }
        });
    }

    /* ----------  route model binding по uuid  ---------- */

    public function getRouteKeyName(): string
    {
        // Використовуємо UUID для route model binding
        // Це дозволяє використовувати UUID в URL замість ID
        // Наприклад: /tenants/123e4567-e89b-12d3-a456-426614174000
        // Це робить URL більш читабельними і унікальними
        // Повертаємо 'uuid' для використання в route model binding
        return 'uuid';
    }
}
