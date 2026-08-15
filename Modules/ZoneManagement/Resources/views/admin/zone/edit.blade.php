@extends('adminmodule::layouts.master')

@section('title', translate('Edit_Zone_Setup'))

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3 justify-content-between mb-4">
                <h2 class="fs-22 text-capitalize">{{ translate('zone_setup') }}</h2>
            </div>
            <form id="zone_form" action="{{ route('admin.zone.update', ['id'=>$zone->id]) }}"
                  enctype="multipart/form-data" method="POST">
                @csrf
                @method('put')
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row justify-content-between">
                                    <div class="col-lg-5 col-xl-4 mb-5 mb-lg-0">
                                        <h5 class="text-primary text-uppercase mb-4">{{ translate('instructions') }}</h5>
                                        <div class="d-flex flex-column">
                                            <p>{{ translate('create_zone_by_click_on_map_and_connect_the_dots_together') }}</p>

                                            <div class="media mb-2 gap-3 align-items-center">
                                                <img
                                                    src="{{dynamicAsset('public/assets/admin-module/img/map-drag.png') }}"
                                                    alt="">
                                                <div class="media-body ">
                                                    <p>{{ translate('use_this_to_drag_map_to_find_proper_area') }}</p>
                                                </div>
                                            </div>

                                            <div class="media gap-3 align-items-center">
                                                <img
                                                    src="{{dynamicAsset('public/assets/admin-module/img/map-draw.png') }}"
                                                    alt="">
                                                <div class="media-body ">
                                                    <p>{{ translate('click_this_icon_to_start_pin_points_in_the_map_and_connect_them_
                                                            to_draw_a_
                                                            zone_._Minimum_3_points_required') }}</p>
                                                </div>
                                            </div>
                                            <div class="map-img mt-4">
                                                <img
                                                    src="{{ dynamicAsset('public/assets/admin-module/img/instructions.gif') }}"
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="mb-4">
                                            <label for="zone_name"
                                                   class="form-label text-capitalize">{{ translate('zone_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="name" id="zone_name"
                                                   value="{{ $zone->name }}" placeholder="{{ translate('ex') }}: {{ translate('Dhanmondi') }}" required>
                                        </div>

                                        <div class="form-group mb-3 d-none">
                                            <label class="input-label"
                                                   for="coordinates">{{ translate('coordinates') }}
                                                <span
                                                    class="input-label-secondary">{{ translate('draw_your_zone_on_the_map') }}</span>
                                            </label>
                                            <textarea type="text" rows="8" name="coordinates" id="coordinates" class="form-control" readonly>@foreach($zone->coordinates[0]->toArray()['coordinates'] as $key=>$coords)<?php if (count($zone->coordinates[0]->toArray()['coordinates']) != $key + 1) {if ($key != 0) echo(','); ?>({{$coords[1]}}, {{$coords[0]}})<?php } ?>@endforeach</textarea>
                                        </div>

                                        <!-- Start Map -->
                                        <div class="map-warper position-relative map-pac-controller overflow-hidden rounded">
                                            <input id="pac-input" class="controls rounded map-search-box"
                                                   title="{{ translate('search_your_location_here') }}" type="text"
                                                   placeholder="{{ translate('search_here') }}"/>
                                            <div id="map-canvas" class="map-height"></div>
                                        </div>
                                        <!-- End Map -->
                                    </div>

                                    <div class="d-flex justify-content-end gap-3 mt-3">
                                        <button class="btn btn-secondary cmn_reset" type="reset" id="reset_btn">
                                            {{ translate('reset') }}
                                        </button>
                                        <button class="btn btn-primary cmn_focus" type="submit">
                                            {{ translate('update') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <!-- End Main Content -->
@endsection

@push('script')
    @php($map_key = businessConfig(GOOGLE_MAP_API)?->value['map_api_key'] ?? null)

    <script src="https://maps.googleapis.com/maps/api/js?key={{ $map_key }}&libraries=places&v=weekly&loading=async"></script>
    <script>
        "use strict";

        function auto_grow() {
            let element = document.getElementById("coordinates");
            if (element) {
                element.style.height = "5px";
                element.style.height = (element.scrollHeight) + "px";
            }
        }

        let map;
        let lat_longs = new Array();
        let lastpolygon = null;
        let bounds = null;
        let polygons = [];
        let isDrawing = false;
        let drawPoints = [];
        let drawMarkers = [];
        let drawPolyline = null;

        function createDrawButton(controlDiv, map) {
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.marginTop = "8px";
            controlUI.style.marginBottom = "22px";
            controlUI.style.textAlign = "center";
            controlUI.title = "Click to start drawing zone";
            controlDiv.appendChild(controlUI);

            const controlText = document.createElement("div");
            controlText.style.color = "rgb(25,25,25)";
            controlText.style.fontFamily = "Roboto,Arial,sans-serif";
            controlText.style.fontSize = "12px";
            controlText.style.lineHeight = "20px";
            controlText.style.paddingLeft = "8px";
            controlText.style.paddingRight = "8px";
            controlText.innerHTML = "Draw Zone";
            controlUI.appendChild(controlText);

            controlUI.addEventListener("click", () => {
                if (isDrawing) {
                    finishDrawing();
                } else {
                    startDrawing();
                }
            });
        }

        function createResetButton(controlDiv) {
            const controlUI = document.createElement("div");
            controlUI.style.backgroundColor = "#fff";
            controlUI.style.border = "2px solid #fff";
            controlUI.style.borderRadius = "3px";
            controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
            controlUI.style.cursor = "pointer";
            controlUI.style.marginTop = "8px";
            controlUI.style.marginBottom = "22px";
            controlUI.style.textAlign = "center";
            controlUI.title = "Reset zone";
            controlDiv.appendChild(controlUI);

            const controlText = document.createElement("div");
            controlText.style.color = "rgb(25,25,25)";
            controlText.style.fontFamily = "Roboto,Arial,sans-serif";
            controlText.style.fontSize = "12px";
            controlText.style.lineHeight = "20px";
            controlText.style.paddingLeft = "8px";
            controlText.style.paddingRight = "8px";
            controlText.innerHTML = "X Reset";
            controlUI.appendChild(controlText);

            controlUI.addEventListener("click", () => {
                clearDrawing();
            });
        }

        function startDrawing() {
            clearDrawing();
            isDrawing = true;
            map.setOptions({ draggableCursor: 'crosshair' });
            toastr.info('{{ translate("click_on_map_to_add_points._click_Draw_Zone_again_to_finish._minimum_3_points_required") }}');
        }

        function finishDrawing() {
            if (drawPoints.length < 3) {
                toastr.error('{{ translate("minimum_3_points_required_to_create_a_zone") }}');
                return;
            }
            isDrawing = false;
            map.setOptions({ draggableCursor: null });

            if (lastpolygon) {
                lastpolygon.setMap(null);
            }

            lastpolygon = new google.maps.Polygon({
                paths: drawPoints,
                strokeColor: '#0c67a3',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#0c67a3',
                fillOpacity: 0.2,
                editable: true,
                map: map
            });

            const coords = drawPoints.map(p => new google.maps.LatLng(p.lat, p.lng));
            $('#coordinates').val(coords);
            auto_grow();

            drawMarkers.forEach(m => m.setMap(null));
            if (drawPolyline) drawPolyline.setMap(null);
            drawMarkers = [];
            drawPolyline = null;
            drawPoints = [];

            google.maps.event.addListener(lastpolygon.getPath(), 'set_at', updateCoordsFromPolygon);
            google.maps.event.addListener(lastpolygon.getPath(), 'insert_at', updateCoordsFromPolygon);
            google.maps.event.addListener(lastpolygon.getPath(), 'remove_at', updateCoordsFromPolygon);
        }

        function updateCoordsFromPolygon() {
            if (lastpolygon) {
                const path = lastpolygon.getPath();
                const coords = [];
                for (let i = 0; i < path.getLength(); i++) {
                    coords.push(path.getAt(i));
                }
                $('#coordinates').val(coords);
                auto_grow();
            }
        }

        function clearDrawing() {
            isDrawing = false;
            map.setOptions({ draggableCursor: null });
            drawMarkers.forEach(m => m.setMap(null));
            if (drawPolyline) drawPolyline.setMap(null);
            if (lastpolygon) lastpolygon.setMap(null);
            drawMarkers = [];
            drawPolyline = null;
            drawPoints = [];
            lastpolygon = null;
            $('#coordinates').val('');
        }

        function initialize() {
            if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.Map !== 'function') {
                setTimeout(initialize, 100);
                return;
            }
            bounds = new google.maps.LatLngBounds();
            let myLatlng = new google.maps.LatLng({{trim(explode(' ',$zone->center)[1], 'POINT()') }}, {{trim(explode(' ',$zone->center)[0], 'POINT()') }});
            let myOptions = {
                zoom: 13,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);

            const polygonCoords = [
                    @foreach($area['coordinates'] as $coords)
                {
                    lat: {{$coords[1]}}, lng: {{$coords[0]}}
                },
                @endforeach
            ];

            let zonePolygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: "#000000",
                strokeOpacity: 0.2,
                strokeWeight: 2,
                fillColor: "#000000",
                fillOpacity: 0.05,
            });

            zonePolygon.setMap(map);

            zonePolygon.getPaths().forEach(function (path) {
                path.forEach(function (latlng) {
                    bounds.extend(latlng);
                    map.fitBounds(bounds);
                });
            });

            const drawDiv = document.createElement("div");
            createDrawButton(drawDiv, map);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(drawDiv);

            const resetDiv = document.createElement("div");
            createResetButton(resetDiv);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(resetDiv);

            map.addListener('click', (e) => {
                if (!isDrawing) return;
                drawPoints.push({ lat: e.latLng.lat(), lng: e.latLng.lng() });

                const marker = new google.maps.Marker({
                    position: e.latLng,
                    map: map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 6,
                        fillColor: '#0c67a3',
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 2
                    }
                });
                drawMarkers.push(marker);

                if (drawPolyline) drawPolyline.setMap(null);
                if (drawPoints.length >= 2) {
                    let previewPath = [...drawPoints];
                    if (drawPoints.length >= 3) {
                        previewPath.push(drawPoints[0]);
                    }
                    drawPolyline = new google.maps.Polyline({
                        path: previewPath,
                        strokeColor: '#0c67a3',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        map: map
                    });
                }
            });

            // Create the search box and link it to the UI element.
            const input = document.getElementById("pac-input");
            const autocomplete = new google.maps.places.Autocomplete(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            map.addListener("bounds_changed", () => {
                autocomplete.setBounds(map.getBounds());
            });
            let markers = [];
            autocomplete.addListener("place_changed", () => {
                const place = autocomplete.getPlace();
                if (!place || !place.geometry || !place.geometry.location) return;
                markers.forEach((marker) => marker.setMap(null));
                markers = [];
                const bounds = new google.maps.LatLngBounds();
                    markers.push(new google.maps.Marker({
                        map, title: place.name, position: place.geometry.location,
                    }));
                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                map.fitBounds(bounds);
            });

            set_all_zones();
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        function set_all_zones() {
            $.get({
                url: '{{route('admin.zone.get-zones',[$zone->id])}}',
                dataType: 'json',
                success: function (data) {
                    for (let i = 0; i < data.length; i++) {
                        polygons.push(new google.maps.Polygon({
                            paths: data[i],
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.1,
                        }));
                        polygons[i].setMap(map);
                    }
                },
            });
        }

        $(document).on('ready', function () {
            $("#zone_form").on('keydown', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            })
        });

        $('#reset_btn').click(function () {
            $('#name').val(null);
            clearDrawing();
        })

    </script>
@endpush
