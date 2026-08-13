<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\Berita;
use App\Models\JenisPos;
use App\Models\Kategori;
use App\Models\Kuesioner;
use App\Models\Pemetaan;
use App\Models\Personil;
use App\Models\PosPengamatan;
use App\Models\ProdukHukum;
use App\Models\Tag;
use App\Models\Unor;
use App\Models\User;
use App\Models\WilayahSungai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINTS = [
        '/api/dashboard/statistik',
        '/api/dashboard/wilayah-sungai',
        '/api/dashboard/aktivitas-terbaru',
        '/api/dashboard/statistik-tambahan',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_dashboard_endpoints_require_authentication(): void
    {
        foreach (self::ENDPOINTS as $endpoint) {
            $this->getJson($endpoint)->assertStatus(401);
        }
    }

    public function test_statistik_superadmin_returns_kategori_breakdown_and_produk_hukum_total(): void
    {
        $user = $this->makeUser('superadmin');
        $unor = $this->makeUnor();
        $hidrologi = $this->makeKategori('hidrologi');
        $hidrometeorologi = $this->makeKategori('hidrometeorologi');

        $this->createArtikel($user, $unor, $hidrologi);
        $this->createArtikel($user, $unor, $hidrologi, ['status' => 'draft']);
        $this->createArtikel($user, $unor, $hidrometeorologi);
        $this->createBerita($user, $unor, $hidrologi, ['status' => 'draft']);
        $this->createBerita($user, $unor, $hidrometeorologi);
        $this->createPemetaan($user, $unor, $hidrologi);
        $jenisA = $this->createJenisPos($user, $unor, $hidrologi);
        $this->createJenisPos($user, $unor, $hidrometeorologi);
        $this->createPosPengamatan($jenisA);
        $this->createProdukHukum($user, $unor);
        $this->createProdukHukum($user, $unor, ['status' => 'draft']);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/statistik')
            ->assertOk()
            ->assertJson([
                'code' => 200,
                'status' => true,
                'message' => 'Data statistik dashboard berhasil diambil',
            ])
            ->assertJsonStructure([
                'data' => [
                    'kategoris' => [
                        '*' => ['slug', 'artikel', 'berita', 'pemetaan', 'pos'],
                    ],
                    'total_produk_hukum',
                ],
            ])
            ->assertJsonPath('data.kategoris.0', [
                'slug' => 'hidrologi',
                'artikel' => 2,
                'berita' => 1,
                'pemetaan' => 1,
                'pos' => 1,
            ])
            ->assertJsonPath('data.kategoris.1', [
                'slug' => 'hidrometeorologi',
                'artikel' => 1,
                'berita' => 1,
                'pemetaan' => 0,
                'pos' => 0,
            ])
            ->assertJsonPath('data.total_produk_hukum', 2);
    }

    public function test_statistik_admin_returns_quick_access_scoped_to_own_unor(): void
    {
        $unorSatu = $this->makeUnor('Unor Satu');
        $unorDua = $this->makeUnor('Unor Dua');
        $kategori = $this->makeKategori('hidrologi');
        $admin = $this->makeUser('admin', $unorSatu);
        $userLain = $this->makeUser('admin', $unorDua);

        $this->createArtikel($admin, $unorSatu, $kategori);
        $this->createArtikel($admin, $unorSatu, $kategori);
        $this->createArtikel($userLain, $unorDua, $kategori);
        $this->createArtikel($userLain, $unorDua, $kategori);
        $this->createArtikel($userLain, $unorDua, $kategori);
        $this->createBerita($admin, $unorSatu, $kategori);
        $this->createPemetaan($admin, $unorSatu, $kategori);
        $this->createPemetaan($userLain, $unorDua, $kategori);
        $this->createJenisPos($admin, $unorSatu, $kategori);
        $this->createJenisPos($userLain, $unorDua, $kategori);
        $this->createJenisPos($userLain, $unorDua, $kategori);
        $this->createPosPengamatan(JenisPos::where('unor_id', $unorSatu->id)->first());
        $this->createPosPengamatan(JenisPos::where('unor_id', $unorDua->id)->first());
        $this->createPosPengamatan(JenisPos::where('unor_id', $unorDua->id)->get()->last());

        Sanctum::actingAs($admin);

        $this->getJson('/api/dashboard/statistik')
            ->assertOk()
            ->assertJsonStructure(['data' => ['quick_access' => ['artikel', 'berita', 'pemetaan', 'pos']]])
            ->assertJsonPath('data.quick_access', [
                'artikel' => 2,
                'berita' => 1,
                'pemetaan' => 1,
                'pos' => 1,
            ]);
    }

    public function test_wilayah_sungai_returns_all_with_tag_reference_counts(): void
    {
        $user = $this->makeUser('superadmin');
        $unor = $this->makeUnor();
        $kategori = $this->makeKategori('hidrologi');
        $wsSatu = $this->createWilayahSungai($unor, $kategori, 'WS Satu');
        $wsDua = $this->createWilayahSungai($unor, $kategori, 'WS Dua');

        $tagSatu = $this->createTag($user, $wsSatu);
        $tagDua = $this->createTag($user, $wsDua);

        $artikelSatu = $this->createArtikel($user, $unor, $kategori);
        $artikelSatu->syncTags([$tagSatu->id]);
        $artikelDua = $this->createArtikel($user, $unor, $kategori);
        $artikelDua->syncTags([$tagDua->id]);
        $beritaSatu = $this->createBerita($user, $unor, $kategori);
        $beritaSatu->syncTags([$tagSatu->id]);
        $beritaDua = $this->createBerita($user, $unor, $kategori);
        $beritaDua->syncTags([$tagSatu->id]);
        $jenisPos = $this->createJenisPos($user, $unor, $kategori);
        $pos = $this->createPosPengamatan($jenisPos);
        $pos->syncTags([$tagSatu->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/wilayah-sungai')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'nama', 'thumbnail', 'artikel', 'berita', 'pos']]])
            ->assertJsonCount(2, 'data')
            ->assertJsonMissingPath('data.0.pemetaan')
            ->assertJsonPath('data.0', [
                'id' => $wsSatu->id,
                'nama' => 'WS Satu',
                'thumbnail' => 'default.png',
                'artikel' => 1,
                'berita' => 2,
                'pos' => 1,
            ])
            ->assertJsonPath('data.1', [
                'id' => $wsDua->id,
                'nama' => 'WS Dua',
                'thumbnail' => 'default.png',
                'artikel' => 1,
                'berita' => 0,
                'pos' => 0,
            ]);
    }

    public function test_aktivitas_terbaru_merges_all_types_sorted_desc_and_limited(): void
    {
        $user = $this->makeUser('superadmin');
        $unor = $this->makeUnor();
        $kategori = $this->makeKategori('hidrologi');

        $this->createArtikel($user, $unor, $kategori, ['created_at' => now()->subMinutes(10)]);
        $this->createBerita($user, $unor, $kategori, ['created_at' => now()->subMinutes(20)]);
        $this->createPemetaan($user, $unor, $kategori, ['created_at' => now()->subMinutes(30)]);
        $this->createProdukHukum($user, $unor, ['created_at' => now()->subMinutes(5)]);
        $draft = $this->createArtikel($user, $unor, $kategori, ['status' => 'draft', 'created_at' => now()]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/aktivitas-terbaru?per_page=3')
            ->assertOk()
            ->assertJsonStructure(['data' => [['jenis', 'judul', 'unor_nama', 'tanggal']]])
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0', [
                'jenis' => 'artikel',
                'judul' => $draft->judul,
                'unor_nama' => $unor->nama,
                'tanggal' => $draft->created_at->format('Y-m-d H:i:s'),
            ])
            ->assertJsonPath('data.1.jenis', 'produk_hukum')
            ->assertJsonPath('data.2.jenis', 'artikel');
    }

    public function test_aktivitas_terbaru_defaults_to_five_items(): void
    {
        $user = $this->makeUser('superadmin');
        $unor = $this->makeUnor();
        $kategori = $this->makeKategori('hidrologi');

        for ($i = 1; $i <= 6; $i++) {
            $this->createArtikel($user, $unor, $kategori, ['created_at' => now()->subMinutes($i)]);
        }

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/aktivitas-terbaru')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_statistik_tambahan_returns_top_artikel_pos_per_jenis_and_kuesioner(): void
    {
        $user = $this->makeUser('superadmin');
        $unor = $this->makeUnor();
        $kategori = $this->makeKategori('hidrologi');

        foreach ([1, 2, 3, 4, 5, 6] as $kunjungan) {
            $this->createArtikel($user, $unor, $kategori, [
                'judul' => "Artikel Kunjungan {$kunjungan}",
                'total_kunjungan' => $kunjungan,
                'total_unduh' => $kunjungan * 2,
            ]);
        }

        $jenisA = $this->createJenisPos($user, $unor, $kategori, ['nama' => 'Jenis A']);
        $jenisB = $this->createJenisPos($user, $unor, $kategori, ['nama' => 'Jenis B']);
        $this->createJenisPos($user, $unor, $kategori, ['nama' => 'Jenis C']);
        $this->createPosPengamatan($jenisA);
        $this->createPosPengamatan($jenisB);
        $this->createPosPengamatan($jenisB);

        $artikel = Artikel::first();
        $this->createKuesioner($artikel, ['nilai' => 4]);
        $this->createKuesioner($artikel, ['nilai' => 6]);
        $this->createKuesioner($artikel, ['nilai' => 8]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/statistik-tambahan')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'top_artikel' => [['judul', 'total_kunjungan', 'total_unduh']],
                'pos_per_jenis' => [['nama', 'total']],
                'kuesioner' => ['rata_rata_nilai', 'jumlah_responden'],
            ]])
            ->assertJsonCount(5, 'data.top_artikel')
            ->assertJsonPath('data.top_artikel.0', [
                'judul' => 'Artikel Kunjungan 6',
                'total_kunjungan' => 6,
                'total_unduh' => 12,
            ])
            ->assertJsonPath('data.top_artikel.4', [
                'judul' => 'Artikel Kunjungan 2',
                'total_kunjungan' => 2,
                'total_unduh' => 4,
            ])
            ->assertJsonCount(3, 'data.pos_per_jenis')
            ->assertJsonPath('data.pos_per_jenis.0', ['nama' => 'Jenis A', 'total' => 1])
            ->assertJsonPath('data.pos_per_jenis.1', ['nama' => 'Jenis B', 'total' => 2])
            ->assertJsonPath('data.pos_per_jenis.2', ['nama' => 'Jenis C', 'total' => 0])
            ->assertJsonPath('data.kuesioner', ['rata_rata_nilai' => 6, 'jumlah_responden' => 3]);
    }

    public function test_admin_scoping_applies_to_unor_bearing_endpoints(): void
    {
        $unorSatu = $this->makeUnor('Unor Satu');
        $unorDua = $this->makeUnor('Unor Dua');
        $kategori = $this->makeKategori('hidrologi');
        $admin = $this->makeUser('admin', $unorSatu);
        $userLain = $this->makeUser('admin', $unorDua);

        $wsSatu = $this->createWilayahSungai($unorSatu, $kategori, 'WS Satu');
        $tagSatu = $this->createTag($admin, $wsSatu);

        $artikelSatu = $this->createArtikel($admin, $unorSatu, $kategori, [
            'judul' => 'Artikel Milik Satu',
            'total_kunjungan' => 100,
            'created_at' => now()->subMinutes(60),
        ]);
        $artikelSatu->syncTags([$tagSatu->id]);
        $artikelDua = $this->createArtikel($userLain, $unorDua, $kategori, [
            'judul' => 'Artikel Milik Dua',
            'total_kunjungan' => 999,
            'created_at' => now()->subMinutes(30),
        ]);
        $artikelDua->syncTags([$tagSatu->id]);
        $this->createBerita($userLain, $unorDua, $kategori, ['created_at' => now()->subMinutes(10)]);
        $this->createPemetaan($userLain, $unorDua, $kategori);
        $this->createJenisPos($admin, $unorSatu, $kategori, ['nama' => 'Jenis Satu']);
        $this->createJenisPos($userLain, $unorDua, $kategori, ['nama' => 'Jenis Dua']);
        $this->createKuesioner($artikelDua, ['nilai' => 5]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/dashboard/wilayah-sungai')
            ->assertOk()
            ->assertJsonPath('data.0.artikel', 1);

        $this->getJson('/api/dashboard/aktivitas-terbaru?per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.jenis', 'artikel')
            ->assertJsonPath('data.0.judul', 'Artikel Milik Satu');

        $this->getJson('/api/dashboard/statistik-tambahan')
            ->assertOk()
            ->assertJsonCount(1, 'data.top_artikel')
            ->assertJsonPath('data.top_artikel.0.judul', 'Artikel Milik Satu')
            ->assertJsonCount(1, 'data.pos_per_jenis')
            ->assertJsonPath('data.kuesioner.jumlah_responden', 0);
    }

    private function makeUser(string $role, ?Unor $unor = null): User
    {
        $user = User::create([
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
        ]);

        $user->assignRole($role);

        Personil::create([
            'user_id' => $user->id,
            'unor_id' => $unor?->id,
            'nama' => fake()->name(),
            'status' => 'aktif',
        ]);

        return $user;
    }

    private function makeUnor(string $nama = 'Unor Test'): Unor
    {
        return Unor::create([
            'nama' => $nama,
            'wilayah' => 'Provinsi',
        ]);
    }

    private function makeKategori(string $slug): Kategori
    {
        return Kategori::create([
            'nama_kategori' => ucfirst($slug),
            'slug' => $slug,
        ]);
    }

    private function createArtikel(User $user, Unor $unor, Kategori $kategori, array $overrides = []): Artikel
    {
        return Artikel::create(array_merge([
            'kategori_id' => $kategori->id,
            'unor_id' => $unor->id,
            'user_id' => $user->id,
            'judul' => 'Artikel '.fake()->sentence(3),
            'slug' => 'artikel-'.fake()->unique()->slug(),
            'konten' => 'Konten artikel',
            'tanggal_publikasi' => now()->toDateString(),
            'status' => 'publikasi',
        ], $overrides));
    }

    private function createBerita(User $user, Unor $unor, Kategori $kategori, array $overrides = []): Berita
    {
        return Berita::create(array_merge([
            'unor_id' => $unor->id,
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'judul' => 'Berita '.fake()->sentence(3),
            'slug' => 'berita-'.fake()->unique()->slug(),
            'konten' => 'Konten berita',
            'status' => 'publikasi',
        ], $overrides));
    }

    private function createPemetaan(User $user, Unor $unor, Kategori $kategori, array $overrides = []): Pemetaan
    {
        return Pemetaan::create(array_merge([
            'kategori_id' => $kategori->id,
            'unor_id' => $unor->id,
            'user_id' => $user->id,
            'nama_pemetaan' => 'Pemetaan '.fake()->sentence(2),
            'tipe' => 'manual',
            'status' => 'publikasi',
        ], $overrides));
    }

    private function createProdukHukum(User $user, Unor $unor, array $overrides = []): ProdukHukum
    {
        return ProdukHukum::create(array_merge([
            'unor_id' => $unor->id,
            'user_id' => $user->id,
            'judul' => 'Produk Hukum '.fake()->sentence(2),
            'slug' => 'produk-hukum-'.fake()->unique()->slug(),
            'deskripsi' => 'Deskripsi produk hukum',
            'tahun' => now()->year,
            'file' => 'produk_hukum/contoh.pdf',
            'status' => 'publikasi',
        ], $overrides));
    }

    private function createJenisPos(User $user, Unor $unor, Kategori $kategori, array $overrides = []): JenisPos
    {
        return JenisPos::create(array_merge([
            'unor_id' => $unor->id,
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'nama' => 'Jenis Pos '.fake()->sentence(2),
            'slug' => 'jenis-pos-'.fake()->unique()->slug(),
            'kewenangan' => 'Kewenangan jenis pos',
            'status' => 'publikasi',
        ], $overrides));
    }

    private function createPosPengamatan(JenisPos $jenisPos, array $overrides = []): PosPengamatan
    {
        return PosPengamatan::create(array_merge([
            'jenis_pos_id' => $jenisPos->id,
            'nama' => 'Pos '.fake()->sentence(2),
            'koordinat' => '0.5, 123.0',
            'alamat' => 'Alamat pos',
        ], $overrides));
    }

    private function createWilayahSungai(Unor $unor, Kategori $kategori, string $nama, array $overrides = []): WilayahSungai
    {
        return WilayahSungai::create(array_merge([
            'kategori_id' => $kategori->id,
            'unor_id' => $unor->id,
            'kewenangan' => 'Provinsi',
            'nama' => $nama,
            'luas_wilayah' => '1000 km2',
            'total_titik_pantau' => 5,
        ], $overrides));
    }

    private function createTag(User $user, WilayahSungai $ws): Tag
    {
        return Tag::create([
            'user_id' => $user->id,
            'nama' => 'Tag '.$ws->nama,
            'referensi_type' => WilayahSungai::class,
            'referensi_id' => $ws->id,
        ]);
    }

    private function createKuesioner(Artikel $artikel, array $overrides = []): Kuesioner
    {
        return Kuesioner::create(array_merge([
            'artikel_id' => $artikel->id,
            'nama' => fake()->name(),
            'instansi' => 'Instansi',
            'nilai' => 0,
        ], $overrides));
    }
}
