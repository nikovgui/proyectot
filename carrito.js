document.addEventListener("DOMContentLoaded", function () {
    mostrarCarrito();
});

function mostrarCarrito() {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    const contenedor = document.getElementById("carrito-list");
    contenedor.innerHTML = "";

    if (carrito.length === 0) {
        contenedor.innerHTML = "<p>El carrito está vacío.</p>";
    } else {
        carrito.forEach((producto, index) => {
            const item = document.createElement("div");
            item.innerHTML = `
                <img src="${producto.image}" alt="${producto.name}" style="width:100px; height:100px; border-radius: 5px;">
                <p>${producto.name} (US ${producto.size}) - $${producto.price}</p>
                <button onclick="eliminarProducto(${index})">Eliminar</button>
            `;
            contenedor.appendChild(item);
        });
    }
}



function procesarPago() {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

    if (carrito.length === 0) {
        alert("El carrito está vacío. Agrega productos antes de pagar.");
        return;
    }

    let params = new URLSearchParams();
    carrito.forEach((producto, index) => {
        params.append(`productoId_${index}`, producto.id);
        params.append(`precio_${index}`, producto.price);
    });

    console.log("Enviando a formulario de pago:", params.toString());

    window.location.href = `http://localhost/sneaker_store/formulario_pago.html?${params.toString()}`;
}




function eliminarProducto(index) {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    carrito.splice(index, 1);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    mostrarCarrito();
}

document.getElementById("comprar-btn").addEventListener("click", function () {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

    if (carrito.length === 0) {
        alert("El carrito está vacío. Agrega productos antes de pagar.");
        return;
    }

    fetch("http://localhost/sneaker_store/guardar_carrito.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ carrito: carrito })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = "http://localhost/sneaker_store/formulario_pago.html";
        } else {
            alert("Error al procesar el pago.");
        }
    })
    .catch(error => console.error("Error:", error));
});
