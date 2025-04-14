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
                    <img src="${imageSrc}" alt="${sneaker.name}" onerror="this.src='placeholder.jpg'">
                    <p>Precio: $${price} (Talla 7 US)</p>
                    <a href="detallezapatilla.html?id=${sneaker.id}">Ver Detalles</a>
                    <button class="btn-comprar" data-id="${sneaker.id}" data-price="${price}">Comprar</button>
                `;

                container.appendChild(sneakerDiv);
            });

            // Escuchar los clicks en los botones "Comprar"
            document.querySelectorAll('.btn-comprar').forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const price = this.getAttribute('data-price');

                    // Llamar al backend que inicia Webpay
                    fetch('webpay_init.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_producto: id, 
                            precio: price 
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.url && data.token) {
                                window.location.href = `${data.url}?token_ws=${data.token}`;
                            } else {
                                alert('Error al iniciar pago');
                            }
                        })
                        .catch(error => {
                            console.error('Error en Webpay:', error);
                            alert('No se pudo procesar el pago.');
                        });
                });
            });

        })
        .catch(error => {
            console.error("Error cargando zapatillas:", error);
            document.getElementById("sneaker-list").innerHTML = `<p>No se pudieron cargar las zapatillas.</p>`;
        });
});
