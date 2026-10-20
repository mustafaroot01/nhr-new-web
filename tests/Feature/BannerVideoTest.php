<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Tests\TestCase;

class BannerVideoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'nahr_newweb',
            'database.connections.mysql.username' => 'admin_nbcvh',
            'database.connections.mysql.password' => '862pC@2xz',
        ]);
    }

    public function test_banner_pages_render_and_video_banner_saves(): void
    {
        $user = User::find(1);

        foreach (['/admin/banners', '/admin/banners/create'] as $path) {
            $res = $this->actingAs($user)->get($path);
            $this->assertLessThan(500, $res->getStatusCode(), "{$path} => {$res->getStatusCode()}");
            echo $path . ' => ' . $res->getStatusCode() . PHP_EOL;
        }

        // A video banner can be stored and read back
        $b = Banner::create([
            'media_type' => 'video',
            'video_path' => 'media/banners/videos/test.mp4',
            'title_ar' => 'بانر فيديو',
            'is_active' => true,
            'sort_order' => 99,
        ]);
        $fresh = $b->fresh();
        $this->assertEquals('video', $fresh->media_type);
        $this->assertEquals('media/banners/videos/test.mp4', $fresh->video_path);
        echo 'video banner saved: type=' . $fresh->media_type . ' path=' . $fresh->video_path . PHP_EOL;

        // Home controller exposes it with type + video url
        $home = $this->get('/ar');
        $home->assertStatus(200);
        echo 'home with video banner => 200' . PHP_EOL;

        $b->forceDelete();
    }
}
