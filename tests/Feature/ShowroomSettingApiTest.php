<?php

namespace Tests\Feature;

use App\Models\ShowroomSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowroomSettingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Andara',
            'username' => 'admin',
            'email' => 'admin@andaramotor.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        $this->token = $this->admin->createToken('admin-token')->plainTextToken;

        ShowroomSetting::create([
            'nama_showroom' => 'Andara Motor',
            'no_whatsapp' => '6281234567890',
            'alamat' => 'Jl. Andara Raya No. 88, Jakarta Selatan',
            'jam_operasional' => '08:00 - 17:00 WIB',
        ]);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_admin_can_update_showroom_settings(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/admin/pengaturan', [
                'nama_showroom' => 'Andara Motor Showroom',
                'no_whatsapp' => '6289998887776',
                'alamat' => 'Jl. Baru No. 1, Karawang',
                'jam_operasional' => 'Setiap Hari 09:00 - 20:00 WIB',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama_showroom', 'Andara Motor Showroom')
            ->assertJsonPath('data.no_whatsapp', '6289998887776');

        $this->assertDatabaseHas('showroom_settings', [
            'nama_showroom' => 'Andara Motor Showroom',
            'no_whatsapp' => '6289998887776',
        ]);
    }
}
