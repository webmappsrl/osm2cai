<?php

namespace App\Traits;

use DKulyk\Nova\Tabs;
use App\Models\UgcPoi;
use App\Models\UgcTrack;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

const GEOHUB_API_URL = 'https://geohub.webmapp.it/api/app/webmapp/';

trait WmNovaFieldsTrait
{
    /**
     * Generate Nova fields based on a JSON schema or a provided schema array.
     *
     * @param string|null $name The name of the column where the form JSON is stored. Default is null.
     * @param array|null $formSchema The optional schema for fields.
     * @return array
     * @throws \Exception
     */
    public function jsonForm(string $columnName, array $formSchema = null)
    {
        // Ensure Laravel Nova is installed
        $this->ensureNovaIsInstalled();

        if (!$formSchema) {
            if (!isset($this->attributes) || !array_key_exists($columnName, $this->attributes)) {
                return [];
            }

            if (strpos($this->app_id, 'osm2cai') !== false) {
                return [];
            }

            $appId = strpos($this->app_id, 'geohub_') !== false
                ? substr($this->app_id, strpos($this->app_id, 'geohub_') + strlen('geohub_'))
                : $this->app_id;

            $geohubAppConfig = GEOHUB_API_URL . $appId . '/config.json';
            $config = Cache::remember('geohub_app_config_' . $appId, now()->addHour(), function () use ($geohubAppConfig) {
                $response = Http::get($geohubAppConfig);
                return $response->json();
            });
            $acquisitionForm = $this->getAcquisitionForm($config);

            $fields = [];
            foreach ($acquisitionForm as $formSection) {
                if ($this->form_id != $formSection['id']) {
                    continue;
                }
                $tabsLabel = $this->getTabsLabel($formSection);
                foreach ($formSection['fields'] as $fieldSchema) {
                    $novaField = $this->createFieldFromSchema($fieldSchema, $columnName);
                    if ($novaField) {
                        $fields[] = $novaField;
                    }
                }
            }

            $tabs = new Tabs($tabsLabel, [
                ' ' => $fields
            ]);
            return $tabs;
        } else {
            foreach ($formSchema as $fieldSchema) {
                $novaField = $this->createFieldFromSchema($fieldSchema, $columnName);
                if ($novaField) {
                    $fields[] = $novaField;
                }
            }
            return $fields;
        }
    }

    /**
     * Create a Nova field based on the field schema.
     *
     * @param array $fieldSchema
     * @param string|null $columnName
     * @return \Laravel\Nova\Fields\Field|null
     */
    protected function createFieldFromSchema(array $fieldSchema, $columnName = null)
    {
        $key = $fieldSchema['name'] ?? null;
        $fieldType = $fieldSchema['type'] ?? 'text';
        $label = $fieldSchema['label']['it'] ?? $fieldSchema['label']['ït'] ?? $fieldSchema['label']['en'];
        $rules = $this->defineRules($fieldSchema);
        if (isset($fieldSchema['required']) && $fieldSchema['required']) {
            $rules[] = 'required';
        }

        $field = null;

        if ($fieldType === 'number') {
            $field = \Laravel\Nova\Fields\Number::make(__($label), "$columnName->$key")
                ->rules($rules)
                ->hideFromIndex();
        } elseif ($fieldType === 'password') {
            $field = \Laravel\Nova\Fields\Password::make(__($label), "$columnName->$key")
                ->rules($rules)
                ->hideFromIndex();
        } elseif ($fieldType === 'select') {
            $options = [];
            if (isset($fieldSchema['values'])) {
                foreach ($fieldSchema['values'] as $option) {
                    $options[$option['value']] = $option['label']['it'];
                }
            }
            $field = \Laravel\Nova\Fields\Select::make(__($label), "$columnName->$key")
                ->options($options)
                ->rules($rules)
                ->displayUsingLabels()
                ->hideFromIndex();
        } elseif ($fieldType === 'boolean') {
            $field = \Laravel\Nova\Fields\Boolean::make($label, "$columnName->$key")
                ->rules($rules)
                ->hideFromIndex();
        } else {
            $field = \Laravel\Nova\Fields\Text::make(__($label), "$columnName->$key")
                ->rules($rules)
                ->hideFromIndex();
        }

        return $field;
    }

    /**
     * Ensure Laravel Nova is installed in the project.
     *
     * @throws \Exception
     */
    protected function ensureNovaIsInstalled()
    {
        if (!class_exists('Laravel\Nova\Fields\Field')) {
            throw new \Exception('Laravel Nova is not installed. Please install Laravel Nova to use this feature.');
        }
    }

    /**
     * Define the rules for the nova fields
     * 
     * @param array $fieldSchema
     * @return array
     */
    protected function defineRules(array $fieldSchema)
    {
        $rules = [];
        if (isset($fieldSchema['rules'])) {
            foreach ($fieldSchema['rules'] as $rule) {
                if ($rule['name'] === 'required') {
                    $rules[] = 'required';
                } elseif ($rule['name'] === 'email') {
                    $rules[] = 'email';
                } elseif ($rule['name'] === 'minLength' && isset($rule['value'])) {
                    $rules[] = 'min:' . $rule['value'];
                }
            }
        }

        return $rules;
    }

    /**
     * Get the acquisition form from the config based on the ugc type
     * 
     * @param array $config
     * @return array
     */
    protected function getAcquisitionForm(array $config)
    {
        $acquisitionForm = [];

        if ($this instanceof UgcPoi) {
            $acquisitionForm = $config['APP']['poi_acquisition_form'] ?? [];
        } elseif ($this instanceof UgcTrack) {
            $acquisitionForm = $config['APP']['track_acquisition_form'] ?? [];
        }

        return $acquisitionForm;
    }

    /**
     * Get the tabs label from the form section
     * 
     * @param array $formSection
     * @return string
     */
    protected function getTabsLabel(array $formSection)
    {
        return $formSection['label']['it'] ?? $formSection['label']['ït'] ?? $formSection['label']['en'];
    }
}
