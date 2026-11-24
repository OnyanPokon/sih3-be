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
use Illuminate\Support\Collection;

class DashboardService
{
    private const SCOPE_UNOR = 'unor_id';

    private const SCOPE_JENIS_POS = 'jenis_pos';

    private const SCOPE_ARTIKEL = 'artikel';

    public function statistik(Request $request): array
    {
        if (! $this->isAdmin($request)) {
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

        $unorId = $this->unorId($request);

        return [
            'quick_access' => [
                'artikel' => $this->scoped(Artikel::query(), $unorId)->count(),
                'berita' => $this->scoped(Berita::query(), $unorId)->count(),
                'pemetaan' => $this->scoped(Pemetaan::query(), $unorId)->count(),
                'pos' => $this->scoped(PosPengamatan::query(), $unorId, self::SCOPE_JENIS_POS)->count(),
            ],
        ];
    }

    public function wilayahSungai(Request $request): array
    {
        $isAdmin = $this->isAdmin($request);
        $unorId = $this->unorId($request);

        return WilayahSungai::query()
            ->orderBy('id')
            ->get()
            ->map(function (WilayahSungai $wilayahSungai) use ($isAdmin, $unorId) {
                return [
                    'id' => $wilayahSungai->id,
                    'nama' => $wilayahSungai->nama,
                    'thumbnail' => $wilayahSungai->thumbnail,
                    'artikel' => $this->referenceCount(Artikel::query(), $wilayahSungai, $isAdmin, $unorId),
                    'berita' => $this->referenceCount(Berita::query(), $wilayahSungai, $isAdmin, $unorId),
                    'pos' => $this->referenceCount(PosPengamatan::query(), $wilayahSungai, $isAdmin, $unorId, self::SCOPE_JENIS_POS),
                ];
            })
            ->values()
            ->all();
    }

    public function aktivitasTerbaru(Request $request): array
    {
        $perPage = (int) ($request->query('per_page') ?? 5);
        $isAdmin = $this->isAdmin($request);
        $unorId = $this->unorId($request);

        $models = [
            'artikel' => Artikel::class,
            'berita' => Berita::class,
            'pemetaan' => Pemetaan::class,
            'produk_hukum' => ProdukHukum::class,
        ];

        $items = collect();
        foreach ($models as $jenis => $modelClass) {
            foreach ($this->recent($modelClass, $perPage, $isAdmin, $unorId) as $model) {
                $items->push(['jenis' => $jenis, 'model' => $model]);
            }
        }

        return $items
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
        $isAdmin = $this->isAdmin($request);
        $unorId = $this->unorId($request);

        $topArtikel = Artikel::query()
            ->when($isAdmin, fn (Builder $query) => $this->scoped($query, $unorId))
            ->orderByDesc('total_kunjungan')
            ->limit(5)
            ->get(['judul', 'total_kunjungan', 'total_unduh'])
            ->map(fn (Artikel $artikel) => [
                'judul' => $artikel->judul,
                'total_kunjungan' => $artikel->total_kunjungan,
                'total_unduh' => $artikel->total_unduh,
            ])
            ->all();

        $kuesionerQuery = Kuesioner::query()
            ->when($isAdmin, fn (Builder $query) => $this->scoped($query, $unorId, self::SCOPE_ARTIKEL));

        $rataRata = $kuesionerQuery->avg('nilai');

        return [
            'top_artikel' => $topArtikel,
            'pos_per_jenis' => $this->posPerJenis($request),
            'kuesioner' => [
                'rata_rata_nilai' => $rataRata === null ? 0 : round((float) $rataRata, 2),
                'jumlah_responden' => $kuesionerQuery->count(),
            ],
        ];
    }

    private function posPerJenis(Request $request): array
    {
        $isAdmin = $this->isAdmin($request);
        $unorId = $this->unorId($request);

        $jenisPos = JenisPos::query()
            ->when($isAdmin, fn (Builder $query) => $this->scoped($query, $unorId))
            ->orderBy('id')
            ->get(['id', 'nama']);

        $counts = PosPengamatan::query()
            ->when($isAdmin, fn (Builder $query) => $this->scoped($query, $unorId, self::SCOPE_JENIS_POS))
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

    private function recent(string $modelClass, int $perPage, bool $isAdmin, ?int $unorId): Collection
    {
        return $modelClass::query()
            ->when($isAdmin, fn (Builder $query) => $this->scoped($query, $unorId))
            ->with('unor')
            ->latest('created_at')
            ->limit($perPage)
            ->get();
    }

    private function referenceCount(Builder $query, WilayahSungai $wilayahSungai, bool $isAdmin, ?int $unorId, string $scope = self::SCOPE_UNOR): int
    {
        $query->whereHas('tags', function (Builder $tagsQuery) use ($wilayahSungai) {
            $tagsQuery->where('referensi_type', WilayahSungai::class)
                ->where('referensi_id', $wilayahSungai->id);
        });

        if ($isAdmin) {
            $this->scoped($query, $unorId, $scope);
        }

        return $query->count();
    }

    private function scoped(Builder $query, ?int $unorId, string $scope = self::SCOPE_UNOR): Builder
    {
        if ($unorId === null) {
            return $query->whereRaw('1 = 0');
        }

        return match ($scope) {
            self::SCOPE_JENIS_POS => $query->whereHas('jenis_pos', fn (Builder $jenisQuery) => $jenisQuery->where('unor_id', $unorId)),
            self::SCOPE_ARTIKEL => $query->whereHas('artikel', fn (Builder $artikelQuery) => $artikelQuery->where('unor_id', $unorId)),
            default => $query->where('unor_id', $unorId),
        };
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()->hasRole('admin');
    }

    private function unorId(Request $request): ?int
    {
        return $request->user()->personil?->unor?->id;
    }
}
