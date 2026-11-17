<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('administrationPages')]
    public function test_admin_navigation_preserves_available_destinations(string $routeName, string $role): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['role' => $role]);
        $admin->department->forceFill(['created_at' => now()])->save();

        $response = $this->actingAs($admin)->get(route($routeName));

        $response->assertOk();
        $this->assertAuthenticatedAs($admin);
        $document = new DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $sidebar = new DOMXPath($document);

        foreach ([
            'Ringkasan' => 'dashboard',
            'Laporan Bahaya' => 'hazards.index',
            'Inspeksi Harian' => 'daily-inspections.index',
            'Pencapaian SAP' => 'sap.index',
            'Kelola Karyawan' => 'admin.pengguna.index',
            'Data Master' => 'admin.data-master',
        ] as $label => $destination) {
            $links = $sidebar->query('//aside//a[span[normalize-space(.)="'.$label.'"]]');
            $this->assertCount(1, $links, $label);
            $this->assertSame(route($destination), $links->item(0)->getAttribute('href'), $label);
        }

        foreach (['Safety Talk', 'Notifikasi', 'Ekspor Laporan', 'Jejak Audit'] as $label) {
            $links = $sidebar->query('//aside//a[span[normalize-space(.)="'.$label.'"]]');
            $this->assertCount(1, $links, $label);
            $this->assertSame('true', $links->item(0)->getAttribute('aria-disabled'), $label);
            $this->assertFalse($links->item(0)->hasAttribute('href'), $label);
            $this->assertStringNotContainsString('Belum tersedia', $links->item(0)->textContent, $label);
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function administrationPages(): array
    {
        $pages = [];

        foreach (['admin', 'super_admin'] as $role) {
            foreach (['admin.pengguna.index', 'dashboard', 'sap.index', 'hazards.index', 'daily-inspections.index', 'shifts.index', 'admin.departments.index', 'admin.work-areas.index'] as $routeName) {
                $pages[$role.' '.$routeName] = [$routeName, $role];
            }
        }

        return $pages;
    }

    public function test_employee_navigation_hides_administration_and_does_not_link_to_admin_sap(): void
    {
        $this->withoutVite();
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('daily-inspections.index'));

        $response->assertOk()
            ->assertDontSee('Kelola Karyawan')
            ->assertDontSee('Ekspor Laporan')
            ->assertDontSee('Jejak Audit')
            ->assertDontSee('href="'.route('sap.index').'"', false);
    }
}
