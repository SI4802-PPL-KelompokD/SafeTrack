<?php

namespace App\Models;

use App\Support\IdFormat;
use Database\Factories\HazardReportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HazardReport extends Model
{
    /** @use HasFactory<HazardReportFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending_verification';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    /** Label tampilan (UI) untuk nilai enum database. */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu Verifikasi',
        self::STATUS_REJECTED => 'Ditolak',
        self::STATUS_OPEN => 'Open',
        self::STATUS_IN_PROGRESS => 'Proses',
        self::STATUS_CLOSED => 'Close',
    ];

    public const STATUS_BADGES = [
        self::STATUS_PENDING => 'amber',
        self::STATUS_REJECTED => 'red',
        self::STATUS_OPEN => 'slate',
        self::STATUS_IN_PROGRESS => 'blue',
        self::STATUS_CLOSED => 'green',
    ];

    /** Status yang boleh dilihat karyawan lain (laporan sudah diverifikasi HSE). */
    public const PUBLIC_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_CLOSED,
    ];

    public const RISK_LABELS = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'];
    public const RISK_BADGES = ['low' => 'gray', 'medium' => 'amber', 'high' => 'red'];

    protected $table = 'hazard_reports';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'verified_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------- relasi

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id')->withTrashed();
    }

    public function reporterDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'reporter_department_id');
    }

    public function repairDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'repair_department_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HazardCategory::class, 'category_id');
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class, 'work_area_id')->withTrashed();
    }

    public function repairPic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'repair_pic_id')->withTrashed();
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id')->withTrashed();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by')->withTrashed();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(HazardPhoto::class);
    }

    public function resolutions(): HasMany
    {
        return $this->hasMany(HazardResolution::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(HazardStatusHistory::class)->orderBy('created_at')->orderBy('id');
    }

    // ------------------------------------------------------------- tampilan

    /** Kode tampil, mis. HZ-2026-0012 (tidak disimpan di database). */
    public function code(): string
    {
        $year = ($this->reported_at ?? $this->created_at ?? now())->copy()->timezone(IdFormat::TZ)->format('Y');

        return sprintf('HZ-%s-%04d', $year, $this->id);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'gray';
    }

    public function riskLabel(): string
    {
        return self::RISK_LABELS[$this->risk_level] ?? (string) $this->risk_level;
    }

    public function riskBadge(): string
    {
        return self::RISK_BADGES[$this->risk_level] ?? 'gray';
    }

    // --------------------------------------------------------------- scope

    /**
     * Admin: semua. Karyawan: laporan miliknya (semua status) + laporan terverifikasi milik siapa pun.
     * Laporan terarsip otomatis tersaring oleh SoftDeletes.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('reporter_id', $user->id)
                ->orWhereIn('status', self::PUBLIC_STATUSES);
        });
    }

    /** @param array<string, mixed> $filters */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.IdFormat::escapeLike($term).'%';

                $q->where(function (Builder $w) use ($like, $term) {
                    $w->where('title', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhereHas('reporter', fn (Builder $r) => $r->where('name', 'like', $like));

                    // Pencarian kode: "HZ-2026-0012" atau "HZ-12" -> id 12
                    if (preg_match('/^HZ-(?:\d{4}-)?0*(\d+)$/i', trim($term), $m)) {
                        $w->orWhere('id', (int) $m[1]);
                    }
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when($filters['department_id'] ?? null, fn (Builder $q, $v) => $q->where('reporter_department_id', $v))
            ->when($filters['from'] ?? null, fn (Builder $q, string $v) => $q->where('reported_at', '>=', IdFormat::startOfDayUtc($v)))
            ->when($filters['to'] ?? null, fn (Builder $q, string $v) => $q->where('reported_at', '<=', IdFormat::endOfDayUtc($v)));
    }
}
