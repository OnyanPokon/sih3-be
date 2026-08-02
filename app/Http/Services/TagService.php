<?php

namespace App\Http\Services;

use Exception;
use App\Models\Tag;
use App\Models\WilayahSungai;
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagService
{

    protected $model;

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    private function mapReferensiType(?string $type): ?string
    {
        if (!$type) return null;

        return match ($type) {
            'wilayah_sungai', WilayahSungai::class => WilayahSungai::class,
            default => $type,
        };
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $user = $request->user();

        $query = $this->model->with(['user', 'referensi']);

        if ($user && $user->hasRole('admin')) {
            $unorId = $user->personil?->unor_id;

            if ($unorId) {
                $categoryIds = DB::table('kategori_unor')
                    ->where('unor_id', $unorId)
                    ->pluck('kategori_id');

                if ($categoryIds->isNotEmpty()) {
                    $query->where(function ($q) use ($categoryIds, $unorId) {
                        // Tag created by user in same Unor
                        $q->whereHas('user.personil', function ($pQ) use ($unorId) {
                            $pQ->where('unor_id', $unorId);
                        })
                            // Or tag whose referensi WilayahSungai matches user category
                            ->orWhereHasMorph('referensi', [WilayahSungai::class], function ($wQ) use ($categoryIds) {
                                $wQ->whereIn('kategori_id', $categoryIds);
                            });
                    });
                }
            }
        }

        if ($search = $request->query('search')) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        if ($referensiType = $request->query('referensi_type')) {
            $mappedType = $this->mapReferensiType($referensiType);
            $query->where('referensi_type', $mappedType);
        }

        if ($referensiId = $request->query('referensi_id')) {
            $query->where('referensi_id', $referensiId);
        }

        $query->latest();

        if ($request->query('per_page') === 'all') {
            return $query->get();
        }

        return $query->paginate((int)$per_page);
    }

    public function store($request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['user_id'] = auth()->id();

            if (!empty($validatedData['referensi_type'])) {
                $validatedData['referensi_type'] = $this->mapReferensiType($validatedData['referensi_type']);
            }

            return $this->model->create($validatedData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function show($id)
    {
        $data = $this->model->with(['user', 'referensi'])->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tag tidak ditemukan');
        }
        return $data;
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);

            if (isset($validatedData['referensi_type'])) {
                $validatedData['referensi_type'] = $this->mapReferensiType($validatedData['referensi_type']);
            }

            $data->update($validatedData);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->show($id);
            $data->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function multiDestroy($ids)
    {
        DB::beginTransaction();
        try {
            $idArray = is_array($ids) ? $ids : explode(",", $ids);
            $data = $this->model->whereIn('id', $idArray)->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }

            $this->model->whereIn('id', $idArray)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
