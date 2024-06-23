<main class="pagina">
    <h2 class="pagina__heading"><?php echo $titulo; ?></h2>
    <p class="pagina__descripcion">Tu Boleto - Te recomendamos almacenarlo, puedes compartirlo en redes sociales.</p>

    <div class="boleto-virtual">
        <div class="boleto boleto--<?php echo strtolower($registro->paquete->nombre); ?> boleto--acceso">
            <div class="boleto__contenido">
                <h4 class="boleto__logo">Conferencias CMA</h4>
                <p class="boleto__plan"><?php echo $registro->paquete->nombre; ?></p>
                <p class="boleto__nombre"><?php echo $registro->usuario->nombre . " " . $registro->usuario->apellido; ?></p>
            </div>
            <p class="boleto__codigo"><?php echo '#' . $registro->token; ?></p>
        </div>
    </div>

    <!-- Botón para imprimir el boleto -->
    <button class="boleto__imprimir" onclick="imprimirBoleto()">Imprimir Boleto</button>
    <script>
        function imprimirBoleto() {
            // Redireccionar al script PHP que genera el PDF
            window.open('boletopdf?id=<?php echo urlencode($registro->token); ?>', '_blank');
        }
    </script>

</main>