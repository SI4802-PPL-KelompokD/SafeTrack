<?php

namespace Database\Factories;

use App\Models\HazardReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends Factory<HazardReport>
 */
class HazardReportFactory extends Factory
{
    protected $model = HazardReport::class;

    public function definition(): array
    {
        return [
            'client_request_id' => (string) Str::uuid(),
            'reporter_id' => User::factory(),
            'reporter_department_id' => fn (array $attrs) => User::query()->find($attrs['reporter_id'])->department_id,
            'category_id' => fn () => self::categoryId(),
            'title' => fake()->sentence(4),
            'location' => 'Area Produksi A',
            'description' => fake()->paragraph(),
            'immediate_action' => fake()->sentence(),
            'risk_level' => 'medium',
            'status' => HazardReport::STATUS_PENDING,
            'reported_at' => now()->subHours(3),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => HazardReport::STATUS_PENDING]);
    }

    public function rejected(string $reason = 'Foto kurang jelas.'): static
    {
        return $this->state(fn () => [
            'status' => HazardReport::STATUS_REJECTED,
            'verification_notes' => $reason,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => HazardReport::STATUS_OPEN, 'verified_at' => now()]);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => HazardReport::STATUS_IN_PROGRESS, 'verified_at' => now()]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => HazardReport::STATUS_CLOSED,
            'verified_at' => now()->subDay(),
            'closed_at' => now(),
        ]);
    }

    /** Laporan terarsip (soft delete) beserta pelaku & alasan. */
    public function archived(): static
    {
        return $this->state(fn (array $attrs) => [
            'deleted_at' => now(),
            'deleted_by' => $attrs['reporter_id'],
            'deletion_reason' => 'Laporan duplikat.',
        ]);
    }

    private static function categoryId(): int
    {
        $id = DB::table('kategori_temuan')->value('id');

        return $id ?: DB::table('kategori_temuan')->insertGetId([
            'code' => 'unsafe_condition',
            'name' => 'Kondisi Tidak Aman',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
