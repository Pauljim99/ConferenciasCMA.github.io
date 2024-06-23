<main class="auth" data-bs-theme="ligth">
    <h2 class="auth__heading"><?php echo $titulo; ?></h2>
    <p class="auth__texto">Inicia sesión en Conferencias CMA</p>
   
   <?php
        require_once __DIR__ . '/../templates/alertas.php';
    ?>

    <form method='POST' action="/login" class="formulario">
        <div class="formulario__campo">
            <label for="email" class="formulario__label">Email</label>
            <input
             type="email"
             class="formulario__input" 
             placeholder="Tu Email" 
             id="email" 
             name="email">

        </div>

        <div class="formulario__campo">
            <label for="password" class="formulario__label">Password</label>
            <input 
            type="password" 
            class="formulario__input" 
            placeholder="Tu Password"
            id="password" 
            name="password"
            >
        
        </div class="formulario">
            <div class="g-recaptcha" data-sitekey="6LcK8fIpAAAAAD4v8F5wDqK_6K96BP4hLiP6lTIi"></div>
        <div>
        
        <br>

        </div>
       
        <input type="submit" class="formulario__submit" value="Iniciar Sesión">
    </form>

    <div class="acciones">
        <a href="/registro" class="acciones__enlace">¿Aún no tienes una cuenta? Obtener una</a>
        <a href="/olvide" class="acciones__enlace">¿Olvidaste tu password?</a>
    </div>
</main>
