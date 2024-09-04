const city = document.getElementById('sortie_lieuNew_ville');
const streetName= document.getElementById('sortie_lieuNew_rue');
const lat = document.getElementById('sortie_lieuNew_latitude');
const lon = document.getElementById('sortie_lieuNew_longitude');

async function getCoordinate() {
    $query = streetName.textContent + city.textContent;
    fetch('')
}