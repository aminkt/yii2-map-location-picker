How to install this module:

Step1: Add flowing line to require part of `composer.json` :
```
"aminkt/yii2-map-location-picker": "@dev",
```

And after that run bellow command in your composer :
```
Composer update aminkt/yii2-map-location-picker
```

Step2: Add flowing lines in your application view file:

```php
echo LocationInput::widget([
    'apiKey' => 'Your api key',
    'name' => 'field name',
    'id' => 'field id',
    'options' => [
        // Input options
    ],
    'mapOptions' => [
        'center' => [
            'lat' => 37.4419,
            'lng' => -122.1419,
        ],
        'zoom' => 13,
        // Other google map options.
    ],
    'markerOptions' => [
        'draggable'=> true,
        // Other google map maker options.
    ],
    'disableLocationPicker' => 0, // Or 1 to define input become enabled or not to use just map view.
    'width' => '100%', // Map container width
    'height' => '200px', // Map container height
    'containerOptions' => [
        'class' => 'map-container' // Map container html options.
    ]
]);
```