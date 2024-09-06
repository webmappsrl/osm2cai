<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UgcUserFilter extends Filter
{
    public $component = 'select-filter';

    public function apply(Request $request, $query, $value)
    {
        $type = $value['type'];
        $search = $value['search'];

        if ($type === 'user') {
            return $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        } elseif ($type === 'user_no_match') {
            return $query->where('user_no_match', 'like', "%{$search}%");
        }

        return $query;
    }

    public function options(Request $request)
    {
        return [
            'Utente registrato' => 'user',
            'Email non registrata' => 'user_no_match',
        ];
    }

    public function name()
    {
        return __('Filtro Utente');
    }
}
