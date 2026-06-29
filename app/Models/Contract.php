<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id', 'plot_id', 'pre_need_plan_id', 'columbary_niche_id',
    'contract_date', 'lot_type', 'lot_area', 'dimension', 'contract_type', 'ordinance_period',
    'commencement_date', 'expiration_date',
    'total_amount', 'payment_type', 'status', 'pdf_path',
    'prepared_by', 'approved_by_treasurer_at', 'approved_by_mayor_at',
    'af_51_number', 'af_51_date', 'death_certificate_number',
])]
class Contract extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'commencement_date' => 'date',
            'expiration_date' => 'date',
            'af_51_date' => 'date',
            'total_amount' => 'float',
            'lot_area' => 'float',
            'approved_by_treasurer_at' => 'datetime',
            'approved_by_mayor_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function preNeedPlan(): BelongsTo
    {
        return $this->belongsTo(PreNeedPlan::class);
    }

    public function columbaryNiche(): BelongsTo
    {
        return $this->belongsTo(ColumbaryNiche::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function installmentSchedules(): HasMany
    {
        return $this->hasMany(InstallmentSchedule::class);
    }

    public function burials(): HasMany
    {
        return $this->hasMany(Burial::class);
    }

    public function burialPermits(): HasMany
    {
        return $this->hasMany(BurialPermit::class);
    }
}
