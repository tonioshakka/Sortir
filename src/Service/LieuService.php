<?php

namespace App\Service;

use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

class LieuService
{
    public function getMap(float $latitude, float $longitude) {
        return (new Map('default'))
            ->center(new Point($latitude, $longitude))
            ->zoom(14)

            ->addMarker(new Marker(
                position: new Point($latitude, $longitude),
            ))

            ->options((new LeafletOptions())
                ->tileLayer(new TileLayer(
                    url: 'https://tile.openstreetmap.bzh/br/{z}/{x}/{y}.png',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles courtesy of <a href="http://www.openstreetmap.bzh/" target="_blank">Breton OpenStreetMap Team</a>',
                    options: ['maxZoom' => 19]
                ))
            );
    }

}