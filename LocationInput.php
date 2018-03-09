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

        if(isset($this->options['id'])){
            $this->id = $this->options['id'];
        }

        if(!isset($this->containerOptions['id'])){
            $this->containerOptions['id'] = $this->getId()."-container";
        }
        if(!isset($this->containerOptions['style'])){
            $this->containerOptions['style'] = 'width:' . $this->width . '; height:' . $this->height . ';';
        }
    }

    public function run()
    {
        parent::run();
        $this->registerJs();
        $html = Html::beginTag('div', $this->containerOptions);
        $html .= Html::endTag('div');
        if(!$this->disableLocationPicker){
            $html .= $this->renderInputHtml('hidden');
        }
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
    
    if(!{$this->disableLocationPicker}){
        google.maps.event.addListener(map, 'click', function(event) {
            changePos(event.latLng.lat()+"{$this->latLanDivider}"+event.latLng.lng());
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
        
        function changePos(latLan) {
            position = latLan;
            $("#{$this->getId()}").val(latLan).trigger('change');
        }
    }
}
JS;
        $this->getView()->registerJs($js, View::POS_HEAD);
        $this->getView()->registerJsFile('https://maps.googleapis.com/maps/api/js?key=' . $this->apiKey . '&callback=initMap',
            ['async' => true, 'defer' => true]);
    }


}