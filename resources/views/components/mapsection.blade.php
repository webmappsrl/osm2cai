@props(['hikingroute'])
@php
    use App\Models\hikingroute;

    $res = DB::select('SELECT ST_ASGeoJSON(geometry) as geojson from hiking_routes where id='.$hikingroute->id.'');
    ddd($res);
    $startPoint = DB::select('SELECT ST_ASGeoJSON(ST_StartPoint(geometry)) as geojson from hiking_routes where id='.$hikingroute->id.''); 
    $endPoint = DB::select('SELECT ST_ASGeoJSON(ST_EndPoint(geometry)) as geojson from hiking_routes where id='.$hikingroute->id.''); 
    $startPoint_geometry = json_decode($startPoint[0]->geojson)->coordinates;
    $startPoint_geometry = [$startPoint_geometry[1],$startPoint_geometry[0]];
    
    $endPoint_geometry = json_decode($endPoint[0]->geojson)->coordinates;
    $endPoint_geometry = [$endPoint_geometry[1],$endPoint_geometry[0]];

    $start_end_distance = computeDistance($startPoint_geometry[0],$startPoint_geometry[1],$endPoint_geometry[0],$endPoint_geometry[1]);

    $linearTrip = false;
    if ($start_end_distance > 100) {
        $linearTrip = true;
    }
    $geometry = $res[0]->geojson;
    $geometry = json_decode($geometry);
    $geometry = $geometry->coordinates;
    $geometry = array_map(function($array){
        $new_array = [$array[1],$array[0]];
        return $new_array;
    },$geometry);
    $geometry = json_encode($geometry);

@endphp
<x-schemaOrg :hikingroute="$hikingroute" :startPoint="$startPoint_geometry" :appSocialText="$appSocialText"/>
<div id="map" class="h-full v-full poiLeafletMap">
</div>
<script>
    var pois_collection = @json($pois_collection);
    var map = L.map('map', { dragging: !L.Browser.mobile }).setView([43.689740, 10.392279], 12);
    L.tileLayer('https://api.webmapp.it/tiles/{z}/{x}/{y}.png', {
        attribution: '<a  href="http://webmapp.it" target="blank"> © Webmapp </a><a _ngcontent-wbl-c140="" href="https://www.openstreetmap.org/about/" target="blank">© OpenStreetMap </a>',
        maxZoom: 16,
        tileSize: 256,
        scrollWheelZoom: false,
    }).addTo(map);
    var polyline = L.polyline({{$geometry}}, {color: 'white',weight:7}).addTo(map);
    var polyline2 = L.polyline({{$geometry}}, {color: 'red',weight:3}).addTo(map);
    for (const [poiID, value] of Object.entries(pois_collection)) {
        var greenIcon = L.icon({
            radius: 200,
            className: 'poi-'+poiID,
            iconUrl: value.image,
            iconSize:     [38, 38], // size of the icon
            iconAnchor:   [22, 38], // point of the icon which will correspond to marker's location
        });
        L.marker(JSON.parse(value.geometry), {icon: greenIcon,id:'poi-'+poiID}).addTo(map).on('click', openmodal)
    }
    
    var startIcon = L.icon({
        radius: 200,
        className: 'poi-start endstart',
        iconUrl: "{{asset('images/start-point.png')}}",
        iconSize:     [38, 38], // size of the icon
        iconAnchor:   [22, 15], // point of the icon which will correspond to marker's location
    });
    L.marker(@json($startPoint_geometry), {icon: startIcon}).addTo(map)
    
    if ({!! json_encode($linearTrip, JSON_HEX_TAG) !!}) {
        var endIcon = L.icon({
        radius: 200,
        className: 'poi-end endstart',
        iconUrl: "{{asset('images/end-point.png')}}",
        iconSize:     [38, 38], // size of the icon
        iconAnchor:   [22, 15], // point of the icon which will correspond to marker's location
    });
    L.marker(@json($endPoint_geometry), {icon: endIcon}).addTo(map)
    }
    function openmodal(e) {
        flash('relatedpois');
        $('#'+this.options.id).click();
    }
    // zoom the map to the polyline
    map.fitBounds(polyline.getBounds());
</script>