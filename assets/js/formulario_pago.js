document.getElementById('form-compra').addEventListener('submit', function(e) {
    e.preventDefault();  // Evitar que se recargue la página

    const formData = new FormData(this);

    fetch('webpay_init.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.url && data.token) {
            // Redirigir a la URL de Webpay con el token recibido
            window.location.href = `${data.url}?token_ws=${data.token}`;
        } else {
            alert('Error al iniciar el pago.');
        }
    })
    .catch(error => {
        console.error('Error al procesar el pago:', error);
        alert('Ocurrió un problema al iniciar el pago.');
    });
});
