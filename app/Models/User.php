<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type', // 1: Investor, 2: LP/GP, 3: Startup
        'fund_type',
        'fund_manager',
        'geo_preferences',
        'sector_preferences',
        'company_stage_preferences',
        'global_preference',
        'agnostic_preference',
    ];

    protected $casts = [
        'geo_preferences' => 'array',
        'sector_preferences' => 'array',
        'company_stage_preferences' => 'array',
        'global_preference' => 'boolean',
        'agnostic_preference' => 'boolean',
    ];

    public function getScoringData()
    {
        return [
            'fund_type' => $this->fund_type,
            'fund_manager' => $this->fund_manager,
            'geo_preferences' => $this->geo_preferences,
            'sector_preferences' => $this->sector_preferences,
            'company_stage_preferences' => $this->company_stage_preferences,
            'global_preference' => $this->global_preference,
            'agnostic_preference' => $this->agnostic_preference,
        ];
    }
} 