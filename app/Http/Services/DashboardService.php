<?php

namespace App\Http\Services;

use App\Models\Artikel;
use App\Models\Berita;
use App\Models\JenisPos;
use App\Models\Kategori;
use App\Models\Kuesioner;
use App\Models\Pemetaan;
use App\Models\PosPengamatan;
use App\Models\ProdukHukum;
use App\Models\WilayahSungai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardService
{
    public function statistik(Request $request): array
    {
        $unorId = $this->unorId($request);

        if ($request->user()->hasRole('superadmin')) {
            $kategoris = Kategori::query()
                ->orderBy('id')
                ->get()
                ->map(fn (Kategori $kategori) => [
                    'slug' => $kategori->slug,
                    'artikel' => Artikel::where('kategori_id', $kategori->id)->count(),
                    'berita' => Berita::where('kategori_id', $kategori->id)->count(),
                    'pemetaan' => Pemetaan::where('kategori_id', $kategori->id)->count(),
                    'pos' => PosPengamatan::whereHas('jenis_pos', fn (Builder $query) => $query->where('kategori_id', $kategori->id))->count(),
                ])
                ->values()
                ->all();

            return [
                'kategoris' => $kategoris,
                'total_produk_hukum' => ProdukHukum::count(),
            ];
        }

        return [
            'quick_access' => [
                'artikel' => Artikel::where('unor_id', $unorId)->count(),
                'berita' => Berita::where('unor_id', $unorId)->count(),
                'pemetaan' => Pemetaan::where('unor_id', $unorId)->count(),
                'pos' => PosPengamatan::whereHas('jenis_pos', fn (Builder $query) => $query->where('unor_id', $unorId))->count(),
            ],
        ];
    }

    public function wilayahSungai(Request $request): array
    {
        $unorId = $this->unorId($request);

        return WilayahSungai::query()
            ->orderBy('id')
            ->get()
            ->map(function (WilayahSungai $wilayahSungai) use ($unorId) {
                return [
                    'id' => $wilayahSungai->id,
                    'nama' => $wilayahSungai->nama,
                    'thumbnail' => $wilayahSungai->thumbnail,
                    'artikel' => $this->referenceCount(Artikel::query(), $wilayahSungai, $unorId, 'unor_id'),
                    'berita' => $this->referenceCount(Berita::query(), $wilayahSungai, $unorId, 'unor_id'),
                    'pos' => $this->referenceCount(PosPengamatan::query(), $wilayahSungai, $unorId),
                ];
            })
            ->values()
            ->all();
    }

    public function aktivitasTerbaru(Request $request): array
    {
        $perPage = (int) ($request->query('per_page') ?? 5);
        $unorId = $this->unorId($request);

        $artikels = Artikel::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->with('unor')
            ->latest('created_at')
            ->limit($perPage)
            ->get();

        $beritas = Berita::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->with('unor')
            ->latest('created_at')
            ->limit($perPage)
            ->get();

        $pemetaans = Pemetaan::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->with('unor')
            ->latest('created_at')
            ->limit($perPage)
            ->get();

        $produkHukums = ProdukHukum::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->with('unor')
            ->latest('created_at')
            ->limit($perPage)
            ->get();

        return collect()
            ->merge($artikels->map(fn (Artikel $artikel) => ['jenis' => 'artikel', 'model' => $artikel]))
            ->merge($beritas->map(fn (Berita $berita) => ['jenis' => 'berita', 'model' => $berita]))
            ->merge($pemetaans->map(fn (Pemetaan $pemetaan) => ['jenis' => 'pemetaan', 'model' => $pemetaan]))
            ->merge($produkHukums->map(fn (ProdukHukum $produkHukum) => ['jenis' => 'produk_hukum', 'model' => $produkHukum]))
            ->sortByDesc(fn (array $item) => $item['model']->created_at->timestamp)
            ->take($perPage)
            ->values()
            ->map(fn (array $item) => [
                'jenis' => $item['jenis'],
                'judul' => $item['model']->judul ?? $item['model']->nama_pemetaan,
                'unor_nama' => $item['model']->unor?->nama,
                'tanggal' => $item['model']->created_at->format('Y-m-d H:i:s'),
            ])
            ->all();
    }

    public function statistikTambahan(Request $request): array
    {
        $unorId = $this->unorId($request);

        $topArtikel = Artikel::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->orderByDesc('total_kunjungan')
            ->limit(5)
            ->get(['judul', 'total_kunjungan', 'total_unduh'])
            ->map(fn (Artikel $artikel) => [
                'judul' => $artikel->judul,
                'total_kunjungan' => $artikel->total_kunjungan,
                'total_unduh' => $artikel->total_unduh,
            ])
            ->all();

        $posPerJenis = $this->posPerJenis($unorId);

        $kuesionerQuery = Kuesioner::query();
        if ($unorId !== null) {
            $kuesionerQuery->whereHas('artikel', fn (Builder $query) => $query->where('unor_id', $unorId));
        }

        $rataRata = $kuesionerQuery->avg('nilai');

        return [
            'top_artikel' => $topArtikel,
            'pos_per_jenis' => $posPerJenis,
            'kuesioner' => [
                'rata_rata_nilai' => $rataRata === null ? 0 : round((float) $rataRata, 2),
                'jumlah_responden' => $kuesionerQuery->count(),
            ],
        ];
    }

    private function posPerJenis(?int $unorId): array
    {
        $jenisPos = JenisPos::query()
            ->when($unorId, fn (Builder $query) => $query->where('unor_id', $unorId))
            ->orderBy('id')
            ->get(['id', 'nama']);

        $counts = PosPengamatan::query()
            ->when($unorId, fn (Builder $query) => $query->whereHas('jenis_pos', fn (Builder $jenis) => $jenis->where('unor_id', $unorId)))
            ->selectRaw('jenis_pos_id, count(*) as total')
            ->groupBy('jenis_pos_id')
            ->pluck('total', 'jenis_pos_id');

        return $jenisPos
            ->map(fn (JenisPos $jenisPos) => [
                'nama' => $jenisPos->nama,
                'total' => (int) ($counts[$jenisPos->id] ?? 0),
            ])
            ->values()
            ->all();
    }

    private function referenceCount(Builder $query, WilayahSungai $wilayahSungai, ?int $unorId, ?string $unorColumn = null): int
    {
        $query->whereHas('tags', function (Builder $tagsQuery) use ($wilayahSungai) {
            $tagsQuery->where('referensi_type', WilayahSungai::class)
                ->where('referensi_id', $wilayahSungai->id);
        });

        if ($unorId !== null) {
            if ($unorColumn !== null) {
                $query->where($unorColumn, $unorId);
            } else {
                $query->whereHas('jenis_pos', fn (Builder $jenisQuery) => $jenisQuery->where('unor_id', $unorId));
            }
        }

        return $query->count();
    }

    private function unorId(Request $request): ?int
    {
        if ($request->user()->hasRole('superadmin')) {
            return null;
        }

        return $request->user()?->personil?->unor?->id;
    }
}
