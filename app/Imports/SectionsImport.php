<?php

namespace App\Imports;

use App\Models\Region;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\ToModel;

class SectionsImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        //skip the first row (header)
        if ($row[0] == 'Codice') {
            return null;
        }

        $region = Region::where('name', ucwords(strtolower($row[2])))->first();
        return Section::updateOrCreate(['name' => $row[1]], [
            'cai_code' => $row[0],
            'region_id' => $region->id ?? null,
        ]);
    }
}
