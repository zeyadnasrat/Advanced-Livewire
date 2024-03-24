<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;

class Livewire
{
    function initialRender($class)
    {
        $component = new $class;

        if(method_exists($component, 'mount')){
            $component->mount();
        }

        [$html, $snapshot] = $this->toSnapshot($component);

        $snapshotAttribute = htmlentities(json_encode($snapshot));

        return <<<HTML
            <div wire:snapshot="{$snapshotAttribute}">
                {$html}
            </div>
          HTML;
    }

    function getProperties($component): array
    {
        $properties = [];

        $reflectedProperties = (new ReflectionClass($component))->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($reflectedProperties as $property){
            $properties[$property->getName()] = $property->getValue($component);
        }

        return $properties;
    }

    public function fromSnapshot($snapshot)
    {
        $this->verifyChecksum($snapshot);

        $class = $snapshot['class'];
        $data = $snapshot['data'];
        $meta = $snapshot['meta'];

        $component = new $class;

        $properties = $this->hydrateProperties($data, $meta);

        $this->setProperties($component, $properties);

        return $component;
    }

    function setProperties($component, $properties)
    {
        foreach ($properties as $key => $value) {
            $component->{$key} = $value;
        }
    }

    public function callMethod($component, $method)
    {
        $component->{$method}();
    }

    public function toSnapshot( $component)
    {
        $html = Blade::render($component->render(),
            $properties = $this->getProperties($component)
        );

        [$data, $meta] = $this->dehydrateProperties($properties);

        $snapshot = [
            'class' => get_class($component),
            'data' => $data,
            'meta' => $meta
        ];

        $snapshot['checksum'] = $this->generateChecksum($snapshot);

        return [$html, $snapshot];
    }

    public function updateProperty($component, $property, $value): void
    {
        $component->{$property} = $value;
/*
        $updatedHook = 'updated'.Str::title($property);

        if( method_exists($component, $updatedHook)){
            $component->{$updatedHook}();
        }*/
    }

    public function dehydrateProperties($properties)
    {
        $data = $meta = [];

        foreach ($properties as $key => $value) {
            if($value instanceof Collection){
                $value = $value->toArray();
                $meta[$key] = 'collection';
            }
            $data[$key] = $value;
        }
        return [$data, $meta];
    }

    public function hydrateProperties(
        mixed $data,
        mixed $meta
    ) {
        $properties = [];

        foreach ($data as $key => $value){
            if(isset($meta[$key]) && $meta[$key] === 'collection'){
                $value = collect($value);
            }

            $properties[$key] = $value;
        }
        return $properties;
    }

    private function generateChecksum(array $snapshot)
    {
        return md5(json_encode($snapshot));
    }

    private function verifyChecksum($snapshot)
    {
        $checksum = $snapshot['checksum'];

        unset($snapshot['checksum']);

        if($checksum !== $this->generateChecksum($snapshot)){
            throw new \Exception('Stop hacking my site');
        }
    }
}
