import {
    LngLatBounds,
    Map as MapLibreMap,
    Marker,
    NavigationControl,
    Popup,
    ScaleControl,
    setWorkerUrl,
} from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import workerUrl from 'maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url';

setWorkerUrl(workerUrl);

const osmRasterStyle = {
    version: 8,
    sources: {
        osm: {
            type: 'raster',
            tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
            tileSize: 256,
            maxzoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        },
    },
    layers: [
        {
            id: 'osm-basemap',
            type: 'raster',
            source: 'osm',
        },
    ],
};

const toLngLat = ([latitude, longitude]) => [Number(longitude), Number(latitude)];

const validLatLngs = (points = []) => points
    .map((point) => [Number(point[0]), Number(point[1])])
    .filter(([latitude, longitude]) => Number.isFinite(latitude) && Number.isFinite(longitude));

const createBounds = (points) => {
    const bounds = new LngLatBounds();

    validLatLngs(points).forEach((point) => bounds.extend(toLngLat(point)));

    return bounds;
};

const createMap = ({ container, center, zoom = 13, maxZoom = 19 }) => {
    const map = new MapLibreMap({
        container,
        style: osmRasterStyle,
        center: toLngLat(center),
        zoom,
        maxZoom,
        attributionControl: true,
        cooperativeGestures: true,
    });

    map.addControl(new NavigationControl({ showCompass: true }), 'top-right');
    map.addControl(new ScaleControl({ maxWidth: 120, unit: 'metric' }), 'bottom-left');

    return map;
};

const createMarker = (map, {
    position,
    color = '#047857',
    size = 16,
    popupHtml = null,
    ariaLabel = 'Titik peta',
}) => {
    const element = document.createElement('button');
    element.type = 'button';
    element.className = 'madiun-map-marker';
    element.setAttribute('aria-label', ariaLabel);
    element.style.setProperty('--marker-color', color);
    element.style.setProperty('--marker-size', `${size}px`);

    const marker = new Marker({ element, anchor: 'center' })
        .setLngLat(toLngLat(position))
        .addTo(map);

    if (popupHtml) {
        marker.setPopup(new Popup({ offset: Math.max(12, size), maxWidth: '320px' }).setHTML(popupHtml));
    }

    return marker;
};

const lineFeature = (points) => ({
    type: 'Feature',
    properties: {},
    geometry: {
        type: 'LineString',
        coordinates: validLatLngs(points).map(toLngLat),
    },
});

const upsertLine = (map, {
    sourceId,
    layerId,
    points,
    color = '#059669',
    width = 5,
    opacity = 0.9,
    dasharray = null,
}) => {
    const data = lineFeature(points);
    const source = map.getSource(sourceId);

    if (source) {
        source.setData(data);
        return;
    }

    map.addSource(sourceId, {
        type: 'geojson',
        data,
    });

    const paint = {
        'line-color': color,
        'line-width': width,
        'line-opacity': opacity,
    };

    if (dasharray) {
        paint['line-dasharray'] = dasharray;
    }

    map.addLayer({
        id: layerId,
        type: 'line',
        source: sourceId,
        layout: {
            'line-cap': 'round',
            'line-join': 'round',
        },
        paint,
    });
};

const removeLine = (map, sourceId, layerId) => {
    if (map.getLayer(layerId)) {
        map.removeLayer(layerId);
    }

    if (map.getSource(sourceId)) {
        map.removeSource(sourceId);
    }
};

const fitToPoints = (map, points, { padding = 48, maxZoom = 16, duration = 0 } = {}) => {
    const normalizedPoints = validLatLngs(points);

    if (normalizedPoints.length === 0) {
        return;
    }

    if (normalizedPoints.length === 1) {
        map.easeTo({ center: toLngLat(normalizedPoints[0]), zoom: maxZoom, duration });
        return;
    }

    map.fitBounds(createBounds(normalizedPoints), {
        padding,
        maxZoom,
        duration,
    });
};

window.MadiunMapLibre = Object.freeze({
    Popup,
    createBounds,
    createMap,
    createMarker,
    fitToPoints,
    removeLine,
    toLngLat,
    upsertLine,
    validLatLngs,
});

window.dispatchEvent(new CustomEvent('madiun:maplibre-ready'));
