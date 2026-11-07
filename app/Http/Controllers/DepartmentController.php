<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Shift;
use App\Models\WorkArea;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->withIsReferenced()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $categoriesTable = Schema::hasTable('kategori_temuan') ? 'kategori_temuan' : 'hazard_categories';
        $categoryCount = Schema::hasTable($categoriesTable) ? DB::table($categoriesTable)->count() : 0;
        $shiftCount = 0;
        try {
            $shiftCount = Shift::count();
        } catch (\Throwable $e) {
            $shiftTable = Schema::hasTable('shift') ? 'shift' : 'shifts';
            $shiftCount = Schema::hasTable($shiftTable) ? DB::table($shiftTable)->count() : 0;
        }

        $areaCount = 0;
        try {
            $areaCount = WorkArea::count();
        } catch (\Throwable $e) {
            $areaTable = Schema::hasTable('area_kerja') ? 'area_kerja' : 'work_areas';
            $areaCount = Schema::hasTable($areaTable) ? DB::table($areaTable)->count() : 0;
        }

        $counts = [
            'departments' => Department::count(),
            'shifts' => $shiftCount,
            'categories' => $categoryCount,
            'areas' => $areaCount,
        ];

        return view('departments.index', [
            'departments' => $departments,
            'counts' => $counts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDepartment($request);

        $department = DB::transaction(function () use ($request, $validated) {
            $department = Department::create([
                'code' => $this->generateUniqueCode($validated['name']),
                'name' => $validated['name'],
                'is_hse' => false,
                'is_active' => true,
            ]);

            $this->audit($request, 'department.created', $department, null, $department->toArray());

            return $department;
        });

        return redirect()->route('admin.departments.index')->with('success', 'Data master berhasil disimpan.');
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $this->validateDepartment($request, $department);

        // If department is referenced and user attempts to change name, reject
        if ($department->isReferenced() && Str::lower(trim($validated['name'])) !== Str::lower(trim($department->name))) {
            return back()
                ->withErrors(['name' => 'Nama tidak dapat diubah karena sudah digunakan.'])
                ->with('error', 'Nama tidak dapat diubah karena sudah digunakan.');
        }

        DB::transaction(function () use ($request, $department, $validated) {
            $before = $department->toArray();

            $department->update([
                'name' => $validated['name'],
            ]);

            $this->audit($request, 'department.updated', $department, $before, $department->fresh()->toArray());
        });

        return redirect()->route('admin.departments.index')->with('success', 'Data master berhasil disimpan.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        if ($department->is_hse || $department->isReferenced()) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'Tidak dapat dihapus: data ini masih dirujuk oleh pengguna atau aktivitas, termasuk arsip.');
        }

        try {
            DB::transaction(function () use ($request, $department) {
                $before = $department->toArray();
                $department->delete();
                $this->audit($request, 'department.deleted', $department, $before, []);
            });
        } catch (QueryException $e) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'Tidak dapat dihapus: data ini masih dirujuk oleh pengguna atau aktivitas, termasuk arsip.');
        }

        return redirect()->route('admin.departments.index')->with('success', 'Data master dihapus.');
    }

    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        $name = trim((string) $request->input('name'));

        $messages = [
            'name.required' => 'Nama wajib diisi dan tidak boleh sama dengan data yang ada.',
            'name.string' => 'Nama wajib diisi dan tidak boleh sama dengan data yang ada.',
            'name.max' => 'Nama wajib diisi dan tidak boleh sama dengan data yang ada.',
        ];

        $request->validate([
            'name' => ['required', 'string', 'max:90'],
        ], $messages);

        // Check case-insensitive unique name
        $query = Department::whereRaw('LOWER(name) = ?', [Str::lower($name)]);
        if ($department) {
            $query->where('id', '!=', $department->id);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => ['Nama wajib diisi dan tidak boleh sama dengan data yang ada.'],
            ]);
        }

        return [
            'name' => $name,
        ];
    }

    private function generateUniqueCode(string $name): string
    {
        $base = Str::upper(Str::limit(Str::slug($name, ''), 26, '')) ?: 'DEPT';
        $code = $base;
        $counter = 1;

        while (Department::where('code', $code)->exists()) {
            $suffix = (string) $counter;
            $code = Str::limit($base, 30 - strlen($suffix), '') . $suffix;
            $counter++;
        }

        return $code;
    }

    private function audit(Request $request, string $action, Department $department, ?array $before, array $after): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()?->id,
            'action' => $action,
            'entity_type' => Department::class,
            'entity_id' => $department->id,
            'before_values' => $before ? json_encode($before) : null,
            'after_values' => json_encode($after),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
