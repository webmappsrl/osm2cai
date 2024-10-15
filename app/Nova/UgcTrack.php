<?php

namespace App\Nova;

use App\Nova\AbstractUgc;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Filters\RelatedUGCFilter;
use Laravel\Nova\Fields\BelongsToMany;
use Webmapp\WmEmbedmapsField\WmEmbedmapsField;
use App\Nova\Actions\DownloadFeatureCollection;
use App\Nova\Actions\DownloadGeojsonZipUgcTracks;
use Wm\MapMultiLinestringNova\MapMultiLinestringNova;
use Wm\MapMultiLinestringNova3\MapMultiLinestringNova3;

class UgcTrack extends AbstractUgc
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\UgcTrack::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        if ($this->name)
            return "{$this->name} ({$this->id})";
        else
            return "{$this->id}";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'id',
        'name',
    ];

    public static $priority = 2;

    public static function label()
    {
        $label = 'Track';

        return __($label);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $fields = parent::fields($request);

        if ($request->isCreateOrAttachRequest()) {
            $fields = [
                Text::make('Nome', 'raw_data->title')
                    ->sortable(),
                Textarea::make('Descrizione', 'raw_data->description'),
            ];
        }


        return array_merge($fields, $this->additionalFields($request));
    }

    public function additionalFields(Request $request)
    {
        $fields = [
            Text::make('Tassonomie Where', function ($model) {
                $wheres = $model->taxonomy_wheres;
                $words = explode(' ', $wheres);
                $lines = array_chunk($words, 3);
                $formattedWheres = implode('<br>', array_map(function ($line) {
                    return implode(' ', $line);
                }, $lines));
                return $formattedWheres;
            })->asHtml()
                ->onlyOnDetail(),
            $this->getRawDataField(),
            $this->getMetadataField(),
            // MapMultiLinestringNova3::make(__('Map'), 'geometry')
            //     ->withMeta([
            //         'center' => ["51", "4"],
            //         'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
            //         'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
            //         'minZoom' => 7,
            //         'maxZoom' => 16,
            //     ])
            //     ->hideFromIndex(),
        ];

        return $fields;
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new DownloadGeojsonZipUgcTracks())
                ->canSee(function ($request) {
                    return true;
                })->canRun(function ($request) {
                    return true;
                }),
            (new DownloadFeatureCollection())
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request) {
                    return true;
                })
        ];
    }
}
