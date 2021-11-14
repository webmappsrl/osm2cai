<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SallableTrait
{
    /**
     * It returns the corresponding view
     *
     * @return string
     */
    public function getView(): string
    {
        return $this->getTable() . '_view';
    }

    public function getSal()
    {
        $sal = DB::table($this->getView())
            ->select(DB::raw('(tot1*0.25 + tot2*0.50 + tot3*0.75 + tot4)/num_expected as sal'))
            ->where('id', $this->id)
            ->get();
        return $sal[0]->sal;
    }
}
