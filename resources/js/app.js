const mapContainerSelector = '#adminRouteMap, #adminRouteCreateMap, #adminRouteEditMap';
let mapModulePromise = null;

const loadMapLibreWhenNeeded = () => {
    if (!document.querySelector(mapContainerSelector) || window.MadiunMapLibre) {
        return;
    }

    mapModulePromise ??= import('./maps/maplibre');
};

loadMapLibreWhenNeeded();
document.addEventListener('livewire:navigated', loadMapLibreWhenNeeded);
