<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Symm\Gisconverter\Gisconverter;

trait GeometryFeatureTrait
{

    /**
     * Calculate the kml on a model with geometry
     *
     * @return string
     */
    public function getKml(): ?string
    {
        $model = get_class($this);
        $geom = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;

        if (isset($geom)) {
            $formattedGeometry = Gisconverter::geojsonToKml($geom);

            $name = '<name>' . ($this->name ?? '') . '</name>';

            return $name . $formattedGeometry;
        } else
            return null;
    }

    /**
     * Calculate the gpx on a model with geometry
     *
     * @return mixed|null
     */
    public function getGpx()
    {
        $model = get_class($this);
        $geom = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;

        if (isset($geom)) {
            try {
                return Gisconverter::geojsonToGpx($geom);
            } catch (\Exception $e) {
                $geometry = json_decode($geom, true);
                if ($geometry['type'] === 'MultiLineString') {
                    return $this->convertMultiLineStringToGPX($geometry);
                } else {
                    // Gestisci altri tipi di geometria se necessario
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    private function convertMultiLineStringToGPX(array $multiLineString, $filename = 'output.gpx')
    {
        $gpxContent = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $gpxContent .= '<gpx version="1.1" creator="YourAppName" xmlns="http://www.topografix.com/GPX/1/1">' . PHP_EOL;
        $gpxContent .= '  <trk>' . PHP_EOL;
        $gpxContent .= '    <name>Converted MultiLineString</name>' . PHP_EOL;

        foreach ($multiLineString['coordinates'] as $lineString) {
            $gpxContent .= '    <trkseg>' . PHP_EOL;
            foreach ($lineString as $point) {
                $gpxContent .= '      <trkpt lat="' . $point[1] . '" lon="' . $point[0] . '"></trkpt>' . PHP_EOL;
            }
            $gpxContent .= '    </trkseg>' . PHP_EOL;
        }

        $gpxContent .= '  </trk>' . PHP_EOL;
        $gpxContent .= '</gpx>';

        file_put_contents($filename, $gpxContent);
        return $gpxContent; // Restituisce il contenuto GPX
    }
}
