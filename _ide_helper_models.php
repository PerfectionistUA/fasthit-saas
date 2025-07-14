<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */

namespace App\Models{
    /**
     * @property int $id
     * @property string $name
     * @property string $domain
     * @property string $status
     * @property string|null $trial_ends_at
     * @property string|null $expires_at
     * @property string $timezone
     * @property string $locale
     * @property int|null $created_by
     * @property int|null $updated_by
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $deleted_at
     *
     * @method static \Database\Factories\TenantFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedBy($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDeletedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDomain($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereExpiresAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereLocale($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStatus($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereTimezone($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereTrialEndsAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedBy($value)
     */
    class Tenant extends \Eloquent {}
}

namespace App\Models{
    /**
     * @property int $id
     * @property string $name
     * @property string $email
     * @property \Illuminate\Support\Carbon|null $email_verified_at
     * @property string $password
     * @property string|null $remember_token
     * @property \Illuminate\Support\Carbon|null $created_at
     * @property \Illuminate\Support\Carbon|null $updated_at
     * @property string|null $two_factor_secret
     * @property string|null $two_factor_recovery_codes
     * @property string|null $two_factor_confirmed_at
     * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
     * @property-read int|null $notifications_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
     * @property-read int|null $permissions_count
     * @property-read string $profile_photo_url
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
     * @property-read int|null $roles_count
     * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
     * @property-read int|null $tokens_count
     *
     * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
     * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
     */
    class User extends \Eloquent {}
}
