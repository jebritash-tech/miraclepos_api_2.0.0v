<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
// Import the required interface
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, CanResetPasswordTrait;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'password',
        'role',
        'salary',
        'pin_hash',
        'is_active',   // ✅ جديد
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'pin_set_at'        => 'datetime', 
        ];
    }
    public function setPin(string $pin): void
    {
        $this->pin_hash = bcrypt($pin);
        $this->pin_set_at = now();
    }

    public function verifyPin(string $pin): bool
    {
        if (empty($this->pin_hash)) return false;
        return \Hash::check($pin, $this->pin_hash);
    }

    public function hasPin(): bool
    {
        return !empty($this->pin_hash);
    }

    public function clearPin(): void
    {
        $this->pin_hash = null;
        $this->pin_set_at = null;
    }
    public function branch() {
        return $this->belongsTo(Branch::class);
    }
    
    public function isAdmin() {
        return $this->role === 'admin';
    }

    /**
     * Customizes the password reset notification to point to your frontend
     */
    public function sendPasswordResetNotification($token)
    {
        $url = "https://miraclepos-api.test/reset-password.html?token={$token}&email={$this->email}";

        $this->notify(new ResetPasswordNotification($token));
       
        // Note: To fully customize the link, you may need to create a custom Notification 
        // class that overrides the 'toMail' method to use your $url variable.
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function activeShift()
    {
        return $this->hasOne(Shift::class)
            ->where('status','open');
    }

   public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class);
    }
    public function profile()
    {
        return $this->hasOne(

            EmployeeProfile::class

        );
    }

    public function salaries()
    {
        return $this->hasMany(

            Salary::class

        );
    }
}