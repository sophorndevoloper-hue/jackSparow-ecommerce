<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_approved', 'approved_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Orders placed by this user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the backend profile for this user.
     *
     * @return HasOne<BackendProfile, $this>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(BackendProfile::class, 'user_id');
    }

    /**
     * Determine if the user has an uploaded custom avatar in their backend profile.
     */
    public function hasCustomAvatar(): bool
    {
        return (bool) ($this->profile && $this->profile->avatar && Storage::disk('public')->exists($this->profile->avatar));
    }

    /**
     * Get the URL for the user's avatar image via profile.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->hasCustomAvatar()) {
            return Storage::disk('public')->url($this->profile->avatar);
        }

        return asset('assets/images/avatar/avatar.jpg');
    }

    protected string $guard_name = 'backend';

    /**
     * Determine the default guard name for Spatie permissions on this model.
     */
    protected function getDefaultGuardName(): string
    {
        return 'backend';
    }

    /**
     * Check if user has backend administrative access.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin', 'backend');
    }

    /**
     * Determine if this administrator has been approved.
     */
    public function isApproved(): bool
    {
        return (bool) $this->is_approved;
    }

    /**
     * Approve administrator access.
     */
    public function approve(): void
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }

    /**
     * Revoke administrator approval.
     */
    public function revokeApproval(): void
    {
        $this->update([
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }
}
