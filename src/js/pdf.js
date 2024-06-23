document.getElementById('descargarPDF').addEventListener('click', function() {
    // Realizamos una solicitud AJAX al archivo generar_pdf.php
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'generar_pdf.php', true);
    xhr.responseType = 'blob'; // Indicamos que la respuesta será un archivo
    xhr.onload = function() {
        if (this.status === 200) {
            // Creamos un enlace temporal para descargar el archivo
            var blob = new Blob([this.response], { type: 'application/pdf' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'boleto_conferencia.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }
    };
    xhr.send();
});
