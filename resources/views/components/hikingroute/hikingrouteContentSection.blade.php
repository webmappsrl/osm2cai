@props(['hikingroute'])

<div class="content">
@if ($hikingroute->osm2cai_status)
    <div id="tracksda" class="">
        <p class="">{{__('Stato di accatastamento: ')}}<strong>{!! $hikingroute->osm2cai_status !!}</strong></p>
    </div>
@endif
@if ($hikingroute->relation_id)
    <div id="tracksda" class="">
        <p class="">{{__('OSM ID: ')}}<strong>{!! $hikingroute->relation_id !!}</strong></p>
    </div>
@endif
@if ($hikingroute->cai_scale)
    <div id="tracksda" class="">
        <p class="">{{__('Difficoltà: ')}}<strong>{!! $hikingroute->cai_scale !!}</strong></p>
    </div>
@endif
@if ($hikingroute->from)
    <div id="tracksda" class="">
        <p class="">{{__('Località di partenza: ')}}<strong>{!! $hikingroute->from !!}</strong></p>
    </div>
@endif
@if ($hikingroute->to)
    <div id="tracksda" class="">
        <p class="">{{__('Località di arrivo: ')}}<strong>{!! $hikingroute->to !!}</strong></p>
    </div>
@endif
@if ($hikingroute->rwn_name)
    <div id="tracksda" class="">
        <p class="">{{__('Nome rete escursionistica: ')}}<strong>{!! $hikingroute->rwn_name !!}</strong></p>
    </div>
@endif
@if ($hikingroute->relation_id)
    <div id="tracksda" class="">
        <a target="_blank" href="https://www.openstreetmap.org/relation/{!! $hikingroute->relation_id !!}"><p><strong>{{__('Open Street Map')}}</strong></p></a>
    </div>
@endif
</div>