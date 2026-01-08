<?php

namespace Tests\Feature;

use App\Models\Notifikasi;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_notifications_and_mark_all_as_read()
    {
        $user = Pengguna::factory()->create();

        // Create unread notifications
        Notifikasi::create(['pengguna_id' => $user->id, 'judul' => 'Test 1', 'pesan' => 'Pesan 1', 'tipe' => 'info', 'dibaca' => false]);
        Notifikasi::create(['pengguna_id' => $user->id, 'judul' => 'Test 2', 'pesan' => 'Pesan 2', 'tipe' => 'info', 'dibaca' => false]);

        $this->actingAs($user);

        $resp = $this->getJson('/api/notifikasi');
        $resp->assertStatus(200);
        $json = $resp->json();
        $this->assertEquals(2, $json['unread_count']);

        // Mark all as read
        $this->putJson('/api/notifikasi/read-all')->assertStatus(200);

        $resp2 = $this->getJson('/api/notifikasi');
        $this->assertEquals(0, $resp2->json()['unread_count']);
    }

    public function test_user_can_mark_single_notification_as_read()
    {
        $user = Pengguna::factory()->create();
        $n = Notifikasi::create(['pengguna_id' => $user->id, 'judul' => 'T', 'pesan' => 'P', 'tipe' => 'info', 'dibaca' => false]);

        $this->actingAs($user)->putJson('/api/notifikasi/'.$n->id.'/read')->assertStatus(200);

        $this->assertTrue($n->fresh()->dibaca);
    }

    public function test_pages_show_mark_all_button()
    {
        $user = Pengguna::factory()->create();
        $this->actingAs($user)->get('/search')->assertStatus(200)->assertSee('Tandai semua dibaca');
        $this->actingAs($user)->get('/home')->assertStatus(200)->assertSee('Tandai semua dibaca');
    }
}
