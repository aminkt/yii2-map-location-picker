<?php

namespace aminkt\widgets\google\map;


use yii\base\Widget;
use yii\bootstrap\Html;
use yii\bootstrap\Modal;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * Class LocationInput
 *
 * Widget to user jQuery uploader
 *
 * @package frontend\runtime\uploader
 */
class LocationInput extends InputWidget
{
    public $apiKey;
    public $width = "100%;";
    public $height = "200px";
    public $containerOptions = [];
    public $mapOptions = [];
    public $markerOptions = [];
    public $latLanDivider = ',';
    public $disableLocationPicker = 0;


    public function init()
    {
        parent::init();

        if (isset($this->options['id'])) {
            $this->id = $this->options['id'];
        }

        if (!isset($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->getId() . "-container";
        }
        if (!isset($this->containerOptions['style'])) {
            $this->containerOptions['style'] = 'width:' . $this->width . '; height:' . $this->height . ';';
        }
    }

    public function run()
    {
        parent::run();
        $this->registerJs();
        $html = Html::beginTag('div', $this->containerOptions);
        $html .= Html::endTag('div');
        $html .= $this->renderInputHtml('hidden');
        $html .= Html::input('hidden', 'StaticPageForm[zoom]', '', [
            'id' => 'staticpageform-zoom',
            'class' => 'form-control inline-editable',
        ]);

        $html .= Html::input('text', 'city-search', '', [
            'id' => 'pac-input',
            'class' => 'controls',
            'placeholder' => 'نام یا مختصات شهر ...',
            'size' => '100',
        ]);

        return $html;
    }

    public function registerJs()
    {
        $containerId = $this->containerOptions['id'];
        $mapOptions = Json::encode($this->mapOptions);

        \Yii::warning($mapOptions);

        $markerOptions = Json::encode($this->markerOptions);
        $js = <<<JS
let map;
let marker;
let position = "{$this->value}";

function initMap() {
    map = new google.maps.Map(document.getElementById("{$containerId}"), $mapOptions);
    let latLan = position.split("{$this->latLanDivider}");
    if(latLan.length == 2){
        latLan = {
            lat: parseFloat(latLan[0]),
            lng: parseFloat(latLan[1])
        };
        map.setCenter(latLan);
        let arr = {
            position: latLan,
            map: map
        };
        console.log(arr);
        let m = {$markerOptions};
        marker = new google.maps.Marker($.extend( true, arr, m));
        marker.addListener("dragend", e => {
           changePos(e.latLng.lat()+"{$this->latLanDivider}"+e.latLng.lng());
        });
    }
    
    google.maps.event.addListener(map, 'click', function(event) {
        changePos(event.latLng.lat()+"{$this->latLanDivider}"+event.latLng.lng());
        var zoom = map.getZoom();
            changeZoom(zoom);
        if(marker){
           marker.setMap(null);
        }
        let arr = {
           position: event.latLng,
           map: map
        };
        let m = {$markerOptions};
        marker = new google.maps.Marker($.extend( true, arr, m));
        marker.addListener("dragend", e => {
           changePos(e.latLng.lat()+"{$this->latLanDivider}"+e.latLng.lng());
        });
    });
                
    var input = document.getElementById('pac-input');
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        

        // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }
          // clear marker of dragged location
        if (marker) {
            marker.setMap(null);
        }
          
          // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            changePos(place.geometry.location.lat()+"{$this->latLanDivider}"+place.geometry.location.lng());
            var zoom = map.getZoom();
            changeZoom(zoom);

            if (!place.geometry) {
              console.log("Returned place contains no geometry");
              return;
            }

            let arr = {
                position: place.geometry.location,
                map: map
            };
            let m = {$markerOptions};
            marker = new google.maps.Marker($.extend( true, arr, m));
            marker.addListener("dragend", e => {
                changePos(e.latLng.lat()+"{$this->latLanDivider}"+e.latLng.lng());
            });
            
            if (place.geometry.viewport) {
              // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
          map.fitBounds(bounds);
    });
        
    function changePos(latLan) {
        position = latLan;
        $("#{$this->getId()}").val(latLan).trigger('change');
    }
    
    function changeZoom(zoom) {
        
        $("#staticpageform-zoom").val(zoom).trigger('change');
    }
}
JS;
        $this->getView()->registerJs($js, View::POS_HEAD);
        $this->getView()->registerJsFile('https://maps.googleapis.com/maps/api/js?key=' . $this->apiKey . '&libraries=places' . '&callback=initMap',
            ['async' => true, 'defer' => true]);
    }


}