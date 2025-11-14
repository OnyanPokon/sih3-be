<?php

namespace App\Exports;

use App\Models\Kuesioner;
use Maatwebsite\Excel\Concerns\FromCollection;

class KuesionerExport implements FromCollection
{
    protected $unor_id;

    public function __construct(int $unor_id)
    {
        $this->unor_id = $unor_id;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $unor_id = $this->unor_id;
        $data = Kuesioner::whereHas('artikel', function ($query) use ($unor_id) {
            $query->where('unor_id', $unor_id);
        })->get();
        return $data;
    }
}
