document.addEventListener("DOMContentLoaded", function () {
    console.log("Cargando zapatillas...");

    fetch("http://localhost/get_sneakers.php")
        .then(response => response.json())
        .then(data => {
            console.log("Datos recibidos:", data); 
            const container = document.getElementById("sneaker-list");

            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = `<p>Error: No se recibieron datos correctos.</p>`;
                return;
            }

            data.forEach(sneaker => {
                const sneakerDiv = document.createElement("div");
                sneakerDiv.classList.add("sneaker");

                const imageSrc = sneaker.images && sneaker.images.length > 0 ? sneaker.images[0] : "placeholder.jpg";

                
                const price = sneaker.prices && sneaker.prices["7"] ? sneaker.prices["7"] : "N/A";

                sneakerDiv.innerHTML = `
                    <h2>${sneaker.name}</h2>
                    <img src="${imageSrc}" alt="${sneaker.name}">
                    <p>Precio: $${price} (Talla 7 US)</p>
                    <a href="detallezapatilla.html?id=${sneaker.id}">Ver Detalles</a>
                `;

                container.appendChild(sneakerDiv);
            });
        })
        .catch(error => {
            console.error("Error cargando zapatillas:", error);
            document.getElementById("sneaker-list").innerHTML = `<p>No se pudieron cargar las zapatillas.</p>`;
        });
});
