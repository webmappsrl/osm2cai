<?php

namespace App\Imports;

use App\Models\Region;
use App\Models\Section;
use Maatwebsite\Excel\Concerns\ToModel;

class SubSectionsImport implements ToModel
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
        $region = Region::where('name', ucwords(strtolower($row[3])))->first();
        return Section::updateOrCreate([
            'name' => $row[1] . ' - ' . $row[2]
        ], [
            'cai_code' => $row[0],
            'region_id' => $region->id ?? null,
        ]);
    }
}
