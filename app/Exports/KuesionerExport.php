<?php

namespace App\Exports;

use App\Models\Kuesioner;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class KuesionerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $artikel_id;
    protected $fields;
    protected $headings;

    /**
     * KuesionerExport constructor.
     *
     * @param mixed $artikel_id
     * @param array|null $fields  List of model attributes to export (order matters). Default: ['id','artikel_id','instansi','asal','deskripsi']
     * @param array|null $headings List of headings to use in the exported file. If null, generated from fields.
     */
    public function __construct($artikel_id, array $fields, array $headings)
    {
        $this->artikel_id = $artikel_id;
        $this->fields = $fields ?? ['id', 'artikel_id', 'instansi', 'asal', 'deskripsi'];
        $this->headings = $headings ?? array_map(function ($f) {
            return ucwords(str_replace('_', ' ', $f));
        }, $this->fields);
    }

    /**
     * Return collection of models (all selected fields are handled in map()).
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Kuesioner::where('artikel_id', $this->artikel_id)->get();
    }

    /**
     * Map a model to an array for export. Supports nested attributes using dot notation (e.g. 'user.name').
     *
     * @param mixed $kuesioner
     * @return array
     */
    public function map($kuesioner): array
    {
        $row = [];
        foreach ($this->fields as $field) {
            // detect if this field is a date-like field (ends with _at)
            if (strpos($field, '.') !== false) {
                $parts = explode('.', $field);
                $lastPart = end($parts);
                $isDateField = preg_match('/_at$/', $lastPart) === 1;

                $value = $kuesioner;
                foreach ($parts as $part) {
                    if ($value === null) break;
                    if (is_array($value)) {
                        $value = $value[$part] ?? null;
                    } else {
                        $value = $value->{$part} ?? null;
                    }
                }

                if ($isDateField && $value !== null) {
                    try {
                        $value = Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // leave original value if cannot be parsed
                    }
                }

                $row[] = $value;
            } else {
                $isDateField = preg_match('/_at$/', $field) === 1;
                $value = $kuesioner->{$field} ?? null;

                if ($isDateField && $value !== null) {
                    try {
                        $value = Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // leave original value if cannot be parsed
                    }
                }

                $row[] = $value;
            }
        }

        return $row;
    }

    /**
     * Headings for the exported file.
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
