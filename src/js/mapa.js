if(document.querySelector('#mapa')){
    
    const lat = -12.062235
    const lng= -77.044609
    const zoom = 16

    
    
    const map = L.map('mapa').setView([lat, lng],zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup(`<h2 class="mapa_heading">Conferencias CMA</h2>
                    <p class="mapa__texto">Centro de conferencias></p>
        `)
        .openPopup();
}