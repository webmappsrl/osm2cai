<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait for enriching data from OSM features.
 */
trait EnrichmentFromOsmfeaturesTrait
{
    /**
     * Update the local osmfeatures_data column with the provided data from osmfeatures.
     *
     * @param  array  $data  The data coming from osmfeatures api
     * @return void
     */
    public function updateOsmfeaturesData(array $data): void
    {
        // Check if properties data is present
        if (!isset($data['properties'])) {
            throw new \Exception('Properties data not found');
            Log::error('Properties data not found');
        }

        // Enrich the osmfeatures_data field
        $properties = $data['properties'];
        Log::info("Enriching osmfeatures_data for $this->name");
        $this->osmfeatures_data = json_encode($properties);
        $this->save();

        // Log the successful enrichment
        Log::info("Osmfeatures_data for $this->name Enriched successfully");
    }

    /**
     * Update the score field if it exists in the provided data.
     *
     * @param  array  $data  The data containing the score.
     * @return void
     */
    public function updateScoreIfExists(array $data): void
    {
        // Check if score data is present
        if (isset($data['properties']['score'])) {
            $this->score = $data['properties']['score'];
            $this->save();
            Log::info('Score updated successfully');
        } else {
            Log::info('Score not found in osmfeatures data');
        }
    }

    /**
     * Update the osmfeatures_id field with the provided data.
     *
     * @param  array  $data  The data containing the properties.
     * @return void
     */
    public function updateOsmfeaturesId(array $data): void
    {
        // Check if properties data is present
        if (!isset($data['properties'])) {
            throw new \Exception('Properties data not found');
            Log::error('Properties data not found');
        }

        // Enrich the osmfeatures_id field
        if (isset($data['properties']['osm_type']) && isset($data['properties']['osm_id'])) {
            $this->osmfeatures_id = $data['properties']['osm_type'] . $data['properties']['osm_id'];
            $this->save();
        } else {
            Log::info('Osm type and osm id not found in osmfeatures data');
        }

        // Log the successful enrichment
        Log::info('Osmfeatures_id updated successfully');
    }

    /**
     * Enrich the model with the provided data based on the elements provided.
     *
     * @param  array  $data      The data coming from osmfeatures api.
     * @param  array  $elements  The elements to enrich. Default is an empty array. Available elements are 'osmfeatures_data', 'score' and 'osmfeatures_id'.
     * @return void
     */
    public function enrich(array $data = [], array $elements = []): void
    {
        // Enrich all fields if elements array is empty
        if (empty($elements)) {
            $this->updateOsmfeaturesData($data);
            $this->updateScoreIfExists($data);
            $this->updateOsmfeaturesId($data);
        } else {
            // Enrich only the specified elements
            foreach ($elements as $element) {
                if ($element == 'osmfeatures_data') {
                    $this->updateOsmfeaturesData($data);
                }
                if ($element == 'score') {
                    $this->updateScoreIfExists($data);
                }
                if ($element == 'osmfeatures_id') {
                    $this->updateOsmfeaturesId($data);
                }
            }
        }

        // Log the successful enrichment
        Log::info('Enrichment from Osmfeatures completed successfully');
    }
}