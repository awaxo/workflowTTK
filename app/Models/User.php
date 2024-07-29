<?php

namespace App\Models;

use App\Traits\ImapTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Webklex\IMAP\Facades\Client;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, ImapTrait;

    /**
     * Specify custom table name
     */
    protected $table = 'wf_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'notification_preferences',
        'workgroup_id',
        'email_verified_at',
        'password',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted' => 'boolean',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'deleted' => 0,
        'notification_preferences' => '{"email":{"recruitment":{"approval_notification":true}}}'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the workgroup associated with the user.
     */
    public function workgroup()
    {
        return $this->belongsTo(Workgroup::class, 'workgroup_id');
    }

    /**
     * Get the user who created this user.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this user.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return  array<string, string>|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        return [$this->email => $this->name];
    }

    /**
     * Get the users from the same workgroup as the current user
     */
    public function getUsersFromSameWorkgroup()
    {
        return User::where('workgroup_id', $this->workgroup_id)->get();
    }

    /**
     * Get the supervisor of the current user
     */
    public function getSupervisor()
    {
        if ($this->id == Workgroup::where('workgroup_number', 901)->first()->leader_id) {
            return $this;
        }

        if (Workgroup::whereIn('workgroup_number', [100, 300, 400, 500, 600, 700, 800, 900, 903, 905, 908])->where('leader_id', $this->id)->exists()) {
            return User::find(Workgroup::where('workgroup_number', 901)->first()->leader_id);
        }

        if (Workgroup::whereIn('workgroup_number', [907, 910, 911, 912, 914, 915])->where('leader_id', $this->id)->exists()) {
            return User::find(Workgroup::where('workgroup_number', 903)->first()->leader_id);
        }

        if (Workgroup::where('leader_id', $this->id)
            ->whereRaw('CAST(workgroup_number AS UNSIGNED) < 900')
            ->whereRaw('CAST(workgroup_number AS UNSIGNED) % 100 != 0')
            ->exists()) {
                $workgroup_number = Workgroup::where('leader_id', $this->id)
                    ->whereRaw('CAST(workgroup_number AS UNSIGNED) < 900')
                    ->whereRaw('CAST(workgroup_number AS UNSIGNED) % 100 != 0')
                    ->first()
                    ->workgroup_number;

                $first_digit = substr($workgroup_number, 0, 1);

                return User::find(Workgroup::where('workgroup_number', $first_digit . '00')->first()->leader_id);
        }

        return $this->workgroup->leader;
    }

    public function getDelegates(string $delegationType = null)
    {
        if ($this->id == Workgroup::where('workgroup_number', 903)->first()->leader_id && $delegationType === 'financial_counterparty_approver') {
            return User::find(Workgroup::where('workgroup_number', 910)->first()->leader_id);
        }

        if ($this->id == Workgroup::where('workgroup_number', 910)->first()->leader_id && $delegationType === 'financial_counterparty_approver') {
            return User::find(Workgroup::where('workgroup_number', 903)->first()->leader_id);
        }

        if ($this->id == Workgroup::where('workgroup_number', 911)->first()->leader_id && $delegationType === 'financial_counterparty_approver') {
            return User::find(Workgroup::where('workgroup_number', 903)->first()->leader_id);
        }

        if ($this->id == Workgroup::where('workgroup_number', 901)->first()->leader_id && $delegationType === 'obligee_approver') {
            $workgroups = Workgroup::whereIn('workgroup_number', [100, 300, 400, 500, 600, 700, 800, 900])->get();
            $leaderIds = $workgroups->pluck('leader_id');
            return User::whereIn('id', $leaderIds)->orderBy('name')->get();
        }

        if (Workgroup::whereIn('workgroup_number', [100, 300, 400, 500, 600, 700, 800, 900, 903])->where('leader_id', $this->id)->exists() && $delegationType === 'obligee_approver') {
            return User::find(Workgroup::where('workgroup_number', 901)->first()->leader_id);
        }

        if (Workgroup::whereIn('workgroup_number', [100, 300, 400, 500, 600, 700, 800, 900])->where('leader_id', $this->id)->exists()) {
            $users = $this->getUsersFromSameWorkgroup();

            $leaderOf901 = User::find(Workgroup::where('workgroup_number', 901)->first()->leader_id);

            if ($leaderOf901) {
                $users->push($leaderOf901);
            }

            $firstCharOfWorkgroup = substr($this->workgroup->workgroup_number, 0, 1);

            $leaders = User::whereHas('workgroup', function ($query) use ($firstCharOfWorkgroup) {
                $query->whereRaw('LEFT(workgroup_number, 1) = ?', [$firstCharOfWorkgroup])->where('leader_id', $this->id);
            })->get();

            return $users->concat($leaders)->unique('name')->sortBy('name')->values();
        }

        $currentUser = $this;
        $users = $this->getUsersFromSameWorkgroup()->push($this->getSupervisor())->unique('name')->sortBy('name')->values();
        
        // Filter out the current user from the collection
        $filteredUsers = $users->reject(function ($user) use ($currentUser) {
            return $user->id === $currentUser->id;
        })->values();

        return $filteredUsers;
    }

    public function canViewMenuItem(string $menuItem)
    {
        $workgroup908 = Workgroup::where('workgroup_number', 908)->first();
        $workgroup910 = Workgroup::where('workgroup_number', 910)->first();
        $workgroup911 = Workgroup::where('workgroup_number', 911)->first();
        $workgroup912 = Workgroup::where('workgroup_number', 912)->first();
        $workgroup915 = Workgroup::where('workgroup_number', 915)->first();

        $pagesForAdminOnly = ['settings', 'auxiliary-data-authorizations-permissions'];
        $pagesForSecretaries = ['workflows-employee-recruitment-new'];
        $pagesForAdminOrWg915Leader = ['auxiliary-data-pages-user-list', 'auxiliary-data', 'auxiliary-data-external-access'];
        $pagesForWg912Leader = ['auxiliary-data', 'auxiliary-data-workgroup', 'auxiliary-data-institute'];
        $pagesForWg910Wg911Users = ['auxiliary-data', 'auxiliary-data-costcenter'];
        $pagesForWg910Wg911 = ['auxiliary-data', 'auxiliary-data-costcenter-type'];
        $pagesForWg908 = ['auxiliary-data', 'auxiliary-data-position'];

        if (in_array($menuItem, $pagesForAdminOnly)) {
            if ($this->hasRole('adminisztrator')) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForSecretaries)) {
            if ($this->hasAnyRole(['titkar_1', 'titkar_3', 'titkar_4', 'titkar_5', 'titkar_6', 'titkar_7', 'titkar_8', 'titkar_9_fi', 'titkar_9_gi'])) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForAdminOrWg915Leader)) {
            if ($this->hasRole('adminisztrator') || ($workgroup915 && $workgroup915->leader_id === $this->id)) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForWg912Leader)) {
            if ($this->hasRole('adminisztrator') || ($workgroup912 && $workgroup912->leader_id === $this->id)) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForWg910Wg911Users)) {
            if ($this->hasRole('adminisztrator') || $this->workgroup->workgroup_number == 910 || $this->workgroup->workgroup_number == 911) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForWg910Wg911)) {
            if ($this->hasRole('adminisztrator') || ($workgroup910 && $workgroup910->leader_id === $this->id) || ($workgroup911 && $workgroup911->leader_id === $this->id)) {
                return true;
            }
        }

        if (in_array($menuItem, $pagesForWg908)) {
            if ($this->hasRole('adminisztrator') || ($workgroup908 && $workgroup908->leader_id === $this->id)) {
                return true;
            }
        }

        return false;
    }

    public function connectToImap($username, $password) {
        Log::info('Attempting IMAP connection with username: ' . $username);
        $client = Client::make([
            'host'          => config('imap.accounts.default.host'),
            'port'          => config('imap.accounts.default.port'),
            'encryption'    => config('imap.accounts.default.encryption'),
            'validate_cert' => config('imap.accounts.default.validate_cert'),
            'username'      => $username,
            'password'      => $password,
            'authentication'=> config('imap.accounts.default.authentication'),
        ]);

        try {
            $client->connect();
            $this->imapClient = $client;
            Log::info('IMAP connection successful for username: ' . $username);
        } catch (\Exception $e) {
            Log::error('IMAP connection failed for username: ' . $username . ' with error: ' . $e->getMessage());
            Log::error($e);
            $this->imapClient = null;
        }
    }

    public function checkImapConnection() {
        return $this->imapClient && $this->imapClient->isConnected();
    }
}
