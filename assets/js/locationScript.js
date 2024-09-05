function setCoordinate(){
    const city = document.getElementById('sortie_lieuNew_ville');
    const streetName= document.getElementById('sortie_lieuNew_rue');

    city.addEventListener('change',async  (e) => getCoordinate(e));
    streetName.addEventListener('change',async  (e) => getCoordinate(e));
}
const getCoordinate= async (e) => {
    const city = document.getElementById('sortie_lieuNew_ville');
    const streetName= document.getElementById('sortie_lieuNew_rue');
    const lat = document.getElementById('sortie_lieuNew_latitude');
    const lon = document.getElementById('sortie_lieuNew_longitude');

    const query = (streetName.value + ' ' + city.value).replace(/ /g, '+');

    try {
        const response = await fetch(`/lieu/getCoordinate?adresse=${query}`);

        if (response.status !== 200) {
            console.log(response.message);
            return;
        }
        const data = await response.json();

        lon.value = data[0];
        lat.value = data[1];
        await getNewMap();

    } catch(e) {
        console.log(e.getMessages);
    }
}
function updateMap() {
    const lat = document.getElementById('sortie_lieuNew_latitude');
    const lon = document.getElementById('sortie_lieuNew_longitude');

    lat.addEventListener('change', getNewMap);
    lon.addEventListener('change', getNewMap)
}

async function getNewMap() {
    const lat = document.getElementById('sortie_lieuNew_latitude');
    const lon = document.getElementById('sortie_lieuNew_longitude');
    const map =  document.getElementById('mapContainer')

    try {
       const response = await fetch(`/lieu/getMap?latitude=${lat.value}&longitude=${lon.value}`);
       const newMap = await response.json();
       map.innerHTML = newMap;
    }catch (e) {
        e.getMessages();
    }

}

if (typeof document !== 'undefined') {
    document.addEventListener('turbo:load', () => {
            setCoordinate();
            updateMap();
    });
}
if (typeof document !== 'undefined') {
    document.addEventListener('turbo:render', () => {
        setCoordinate();
        updateMap();
    });
}