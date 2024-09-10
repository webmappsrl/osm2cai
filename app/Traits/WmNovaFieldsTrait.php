<?php

namespace App\Traits;

use DKulyk\Nova\Tabs;
use Laravel\Nova\Fields\Field;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

trait WmNovaFieldsTrait
{
    public function jsonForm(string $columnName, array $formSchema = null)
    {
        // Ensure Laravel Nova is installed
        $this->ensureNovaIsInstalled();

        if (!isset($this->attributes) || !array_key_exists($columnName, $this->attributes)) {
            return $this->createNoDataField();
        }

        if ($formSchema === null) {
            $config = Cache::remember('geohub_config', 60 * 60, function () {
                $config = [];
                $geohubConfigApi = [
                    26 => 'https://geohub.webmapp.it/api/app/webmapp/26/config.json',
                    20 => 'https://geohub.webmapp.it/api/app/webmapp/20/config.json',
                    58 => 'https://geohub.webmapp.it/api/app/webmapp/58/config.json',
                ];

                foreach ($geohubConfigApi as $formId => $url) {
                    $response = Http::get($url);
                    $acquisitionForm = $response->json()['APP']['poi_acquisition_form'];
                    foreach ($acquisitionForm as $form) {
                        $config[$form['id']] = $form;
                    }
                }

                // Remove duplicates key in the config array
                return array_unique($config, SORT_REGULAR);
            });

            if (!isset($config[$this->form_id])) {
                return $this->createNoDataField();
            }

            $formConfig = $config[$this->form_id];
            $fields = $this->buildFieldsFromConfig($formConfig['fields'], $columnName);

            $tabsLabel = $formConfig['label']['it'] ?? $formConfig['label']['ït'] ?? $formConfig['label']['en'] ?? __('Form');
        } else {
            $fields = $this->buildFieldsFromConfig($formSchema, $columnName);
            $tabsLabel = __('Validation Permissions');
        }

        $tabs = new Tabs($tabsLabel, [
            ' ' => $fields
        ]);

        return $tabs;
    }

    protected function buildFieldsFromConfig(array $fieldsConfig, string $columnName): array
    {
        $fields = [];

        foreach ($fieldsConfig as $fieldSchema) {
            $novaField = $this->createFieldFromSchema($fieldSchema, $columnName);
            if ($novaField) {
                $fields[] = $novaField;
            }
        }

        return $fields;
    }

    protected function createFieldFromSchema(array $fieldSchema, string $columnName): ?Field
    {
        $key = $fieldSchema['name'] ?? null;
        $fieldType = $fieldSchema['type'] ?? 'text';
        $label = $fieldSchema['label']['it'] ?? $fieldSchema['label']['ït'] ?? $fieldSchema['label']['en'] ?? $key;
        $rules = $this->defineRules($fieldSchema);

        $field = null;

        switch ($fieldType) {
            case 'number':
                $field = \Laravel\Nova\Fields\Number::make(__($label), "$columnName->$key");
                break;
            case 'password':
                $field = \Laravel\Nova\Fields\Password::make(__($label), "$columnName->$key");
                break;
            case 'select':
                $options = $this->getSelectOptions($fieldSchema);
                $field = \Laravel\Nova\Fields\Select::make(__($label), "$columnName->$key")
                    ->options($options)
                    ->displayUsingLabels();
                break;
            case 'boolean':
                $field = \Laravel\Nova\Fields\Boolean::make($label, "$columnName->$key");
                break;
            case 'textarea':
                $field = \Laravel\Nova\Fields\Textarea::make(__($label), "$columnName->$key");
                break;
            default:
                $field = \Laravel\Nova\Fields\Text::make(__($label), "$columnName->$key");
        }

        if ($field) {
            $field->rules($rules)->hideFromIndex();

            if (isset($fieldSchema['helper'])) {
                $field->help($fieldSchema['helper']['it'] ?? $fieldSchema['helper']['en'] ?? '');
            }

            if (isset($fieldSchema['placeholder'])) {
                $field->placeholder($fieldSchema['placeholder']['it'] ?? $fieldSchema['placeholder']['en'] ?? '');
            }
        }

        return $field;
    }

    protected function getSelectOptions(array $fieldSchema): array
    {
        $options = [];
        if (isset($fieldSchema['values'])) {
            foreach ($fieldSchema['values'] as $option) {
                $options[$option['value']] = $option['label']['it'] ?? $option['label']['en'] ?? $option['value'];
            }
        }
        return $options;
    }

    protected function defineRules(array $fieldSchema): array
    {
        $rules = [];
        if (isset($fieldSchema['required']) && $fieldSchema['required']) {
            $rules[] = 'required';
        }
        return $rules;
    }

    protected function createNoDataField(): Field
    {
        return
            \Laravel\Nova\Fields\Text::make(__('No data for this form ID'), function () {
                return '/';
            })->hideFromIndex();
    }

    protected function ensureNovaIsInstalled()
    {
        if (!class_exists('Laravel\Nova\Fields\Field')) {
            throw new \Exception('Laravel Nova is not installed. Please install Laravel Nova to use this feature.');
        }
    }
}
