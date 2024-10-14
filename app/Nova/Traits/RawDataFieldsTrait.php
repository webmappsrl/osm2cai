<?php

namespace App\Nova\Traits;

use Laravel\Nova\Fields\Code;

/**
 * Trait RawDataFieldsTrait
 * 
 * This trait provides methods for handling raw data fields in JSON format.
 */
trait RawDataFieldsTrait
{
    /**
     * Get the raw data fields.
     * 
     * This method returns an array of raw data fields, including form data, device data, Nominatim data, and raw data.
     * 
     * @return array
     */
    protected function getRawDataFields()
    {
        return [
            $this->getFormDataField(),
            $this->getDeviceDataField(),
            $this->getNominatimField(),
            $this->getRawDataField(),
        ];
    }

    /**
     * Get the form data field.
     * 
     * This method returns a form data field in JSON format, excluding some specific keys.
     * 
     * @return Code
     */
    protected function getFormDataField()
    {
        return Code::make(__('Form data'), function ($model) {
            $jsonRawData = $this->getJsonRawData($model);
            if ($jsonRawData) {
                $excludeKeys = ['position', 'displayPosition', 'city', 'date', 'nominatim'];
                $filteredData = array_diff_key($jsonRawData, array_flip($excludeKeys));
                return json_encode($filteredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return null;
        })->onlyOnDetail()->language('json')->rules('json');
    }

    /**
     * Get the device data field.
     * 
     * This method returns a device data field in JSON format, including only some specific keys.
     * 
     * @return Code
     */
    protected function getDeviceDataField()
    {
        return Code::make(__('Device data'), function ($model) {
            $jsonRawData = $this->getJsonRawData($model);
            if ($jsonRawData) {
                $includeKeys = ['position', 'displayPosition', 'city', 'date'];
                $filteredData = array_intersect_key($jsonRawData, array_flip($includeKeys));
                return json_encode($filteredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return null;
        })->onlyOnDetail()->language('json')->rules('json');
    }

    /**
     * Get the Nominatim field.
     * 
     * This method returns a Nominatim field in JSON format, if present.
     * 
     * @return Code
     */
    protected function getNominatimField()
    {
        return Code::make(__('Nominatim'), function ($model) {
            $jsonRawData = $this->getJsonRawData($model);
            if (isset($jsonRawData['nominatim'])) {
                return json_encode($jsonRawData['nominatim'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return null;
        })->onlyOnDetail()->language('json')->rules('json');
    }

    /**
     * Get the raw data field.
     * 
     * This method returns a raw data field in JSON format, if present.
     * 
     * @return Code
     */
    protected function getRawDataField()
    {
        return Code::make(__('Raw data'), function ($model) {
            $jsonRawData = $this->getJsonRawData($model);
            if ($jsonRawData) {
                return json_encode($jsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return null;
        })->onlyOnDetail()->language('json')->rules('json');
    }

    /**
     * Get the metadata field.
     * 
     * This method returns a metadata field in JSON format, if present (for tracks).
     * 
     * @return Code
     */
    protected function getMetadataField()
    {
        return Code::make(__('Metadata'), function ($model) {
            $jsonMetadata = $model->metaData;
            if ($jsonMetadata) {
                return json_encode($jsonMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return null;
        })->onlyOnDetail()->language('json')->rules('json');
    }

    /**
     * Get the raw data in JSON format.
     * 
     * This method returns the raw data in JSON format, decoding the JSON string if necessary.
     * 
     * @param $model
     * @return array|null
     */
    protected function getJsonRawData($model)
    {
        return is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data ?? null;
    }
}
