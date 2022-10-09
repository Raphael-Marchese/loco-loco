console.log('hola')
async function getAddress() {
  try {
    let data = await fetch('http://127.0.0.1:8080/store/address_list', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    let res = await data.json()
    console.log(res)

    return res
  } catch (error) {
    console.error('ERROR', error)
  }
}

async function initMap() {
  const position = await getAddress()

  if (position) {
    map = await new google.maps.Map(document.getElementById('map'), {
      mapId: '981b93be4c70d164',
      center: { lat: parseFloat(coords.lat), lng: parseFloat(coords.lng) },
      zoom: 16,
    })

    position.forEach(async (coords) => {
      await new google.maps.Marker({
        position:{ lat: parseFloat(coords.lat), lng: parseFloat(coords.lng) },
        map,
        title: 'Hello World!',
      })
    })
  } else {
    console.log('Erreur');
    map = await new google.maps.Map(document.getElementById('map'), {
      mapId: '981b93be4c70d164',
      center: { lat: 50.6364791975973, lng: 3.069550812929656 },
      zoom: 9,
    })
  }
}

window.initMap = initMap

