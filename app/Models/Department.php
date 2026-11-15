<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'code',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'is_hse' => 'boolean',
            'is_active' => 'boolean',
            'is_referenced' => 'boolean',
        ];
    }

    public function employeeRegistries(): HasMany
    {
        $table = Schema::hasTable('nik') ? 'nik' : 'employee_registry';
        return $this->hasMany(EmployeeRegistry::class, 'department_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Get definitions of all tables and columns referencing departments.
     *
     * @return array<int, array{table: string, column: string}>
     */
    public static function getReferenceDefinitions(): array
    {
        $nikTable = Schema::hasTable('nik') ? 'nik' : 'employee_registry';
        $areaTable = Schema::hasTable('area_kerja') ? 'area_kerja' : 'work_areas';
        $resolutionTable = Schema::hasTable('penyelesaian') ? 'penyelesaian' : 'hazard_resolutions';
        $inspectionTable = Schema::hasTable('daily_inspection') ? 'daily_inspection' : 'daily_inspections';
        $talkTable = Schema::hasTable('safety_talk') ? 'safety_talk' : 'safety_talks';
        $sapTable = Schema::hasTable('history_sap') ? 'history_sap' : 'sap_monthly_summaries';

        return [
            ['table' => 'users', 'column' => 'department_id'],
            ['table' => $nikTable, 'column' => 'department_id'],
            ['table' => $areaTable, 'column' => 'department_id'],
            ['table' => 'hazard_reports', 'column' => 'reporter_department_id'],
            ['table' => 'hazard_reports', 'column' => 'repair_department_id'],
            ['table' => $resolutionTable, 'column' => 'department_id'],
            ['table' => $inspectionTable, 'column' => 'department_id'],
            ['table' => $talkTable, 'column' => 'department_id'],
            ['table' => $sapTable, 'column' => 'department_id'],
        ];
    }

    /**
     * Check if this department is referenced by any other table in the system.
     */
    public function isReferenced(): bool
    {
        if (array_key_exists('is_referenced', $this->attributes)) {
            return (bool) $this->attributes['is_referenced'];
        }

        foreach (static::getReferenceDefinitions() as $ref) {
            if (Schema::hasTable($ref['table']) && Schema::hasColumn($ref['table'], $ref['column'])) {
                if (DB::table($ref['table'])->where($ref['column'], $this->id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Scope to select whether each department is referenced, avoiding N+1 queries.
     */
    public function scopeWithIsReferenced(Builder $query): Builder
    {
        $subqueries = [];
        $uniqueRefs = [];

        foreach (static::getReferenceDefinitions() as $ref) {
            $key = $ref['table'] . '.' . $ref['column'];
            if (isset($uniqueRefs[$key])) {
                continue;
            }
            $uniqueRefs[$key] = true;

            if (Schema::hasTable($ref['table']) && Schema::hasColumn($ref['table'], $ref['column'])) {
                $table = $ref['table'];
                $col = $ref['column'];
                $subqueries[] = "EXISTS (SELECT 1 FROM `{$table}` WHERE `{$table}`.`{$col}` = `departments`.`id`)";
            }
        }

        if (empty($subqueries)) {
            return $query->selectRaw('departments.*, false as is_referenced');
        }

        $rawSql = '(' . implode(' OR ', $subqueries) . ') as is_referenced';

        return $query->select('departments.*')->selectRaw($rawSql);
    }
}
