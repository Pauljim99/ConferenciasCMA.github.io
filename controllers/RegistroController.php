<?php

namespace Controllers;

use Endroid\QrCode\QrCode;
use Mpdf\Mpdf;
use Model\Registro;
use Model\Usuario;
use Model\Paquete;
use Model\Categoria;
use Model\Dia;
use Model\hora;
use Model\Evento;
use Model\Ponente;
use Model\Regalo;
use Model\EventosRegistros;
use MVC\Router;



class RegistroController
{

    public static function crear(Router $router)
    {

        if(!is_auth()){
            header('Location: /');
            return;
        }

        // Verificar si el usuario ya esta registrado 
        $registro = Registro::where('usuario_id', $_SESSION['id']);

        if(isset($registro) && $registro->paquete_id ==="3" || $registro->paquete_id ==="2" ){
            header('Location: /boleto?id=' . urlencode($registro->token));
            return; 
        }

        if(isset($registro) && $registro->paquete_id ==="1"){
            header('Location: /finalizar-registro/conferencias');
            return; 
        }


        $router->render('registro/crear', [
            'titulo' => 'Finalizar Registro'
        ]);
    }
    public static function gratis(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
                return; 
            }

            // Verificar si el usuario ya esta registrado 
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if (isset($registro) && $registro->paquete_id === "3") {
                header('Location: /boleto?id=' . urlencode($registro->token));
                return; 
            }

            $token = substr(md5(uniqid(rand(), true)), 0, 8);

            //crear registro
            $datos =[
                'paquete_id' => 3,
                'pago_id' => '',
                'token' => $token,
                'usuario_id' => $_SESSION['id']
            ];

            $registro = new Registro($datos);
            $resultado = $registro->guardar();

            if ($resultado) {
                header('Location: /boleto?id=' . urlencode($registro->token));
                return; 
            }
        }
    }

    public static function boleto(Router $router) {
 
        // Validar la URL
        $id = $_GET['id'];
     
        if(!$id || !strlen($id) === 8 ) {
            header('Location: /');
            return;
        }
     
        // buscarlo en la BD
        $registro = Registro::where('token', $id);
        if(!$registro) {
            header('Location: /');
            return;
        }
        // Llenar las tablas de referencia
        $registro->usuario = Usuario::find($registro->usuario_id);
        $registro->paquete = Paquete::find($registro->paquete_id);
     
        $router->render('registro/boleto', [
            'titulo' => 'Asistencia a Conferencias CMA',
            'registro' => $registro
        ]);
    }
    

    public static function pagar(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!is_auth()) {
                header('Location: /login');
                return; 
            }
            
            if(empty($_POST)){
                echo json_encode([]);
                return;
            }

            //crear registro
            $datos = $_POST;
            $datos ['token']=substr(md5(uniqid(rand(),true)),0,8);
            $datos ['usuario_id']=$_SESSION['id'];

            try {
                $registro = new Registro($datos);
                $resultado = $registro->guardar();
                echo json_encode($resultado);

            } catch (\Throwable $th) {
                echo json_encode([
                    'resultado' => 'error'
                ]);
            }
           
        }
    }


    public static function boletopdf(Router $router)
    {
        // Obtener el token del boleto desde la URL
        $id = $_GET['id'] ?? null;
    
        if (!$id || strlen($id) !== 8) {
            header('Location: /'); // Redirigir si el token no es válido
            return;
        }
    
        // Buscar el registro en la base de datos
        $registro = Registro::where('token', $id);
        if (!$registro) {
            header('Location: /'); // Redirigir si no se encuentra el registro
            return;
        }
    
        // Llenar los datos adicionales del registro (usuario, paquete, etc.)
        $registro->usuario = Usuario::find($registro->usuario_id);
        $registro->paquete = Paquete::find($registro->paquete_id);
    
        // Crear el contenido HTML del boleto basado en el diseño de la página web
        $html = "
        <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: rgb(0,22,101);
            background: linear-gradient(360deg, rgba(0,22,101,1) 0%, rgba(255,255,255,1) 0%, rgba(182,210,222,1) 100%);
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        .header {
            text-align: center;
            padding: 20px;
            border-radius: 30px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 32px;
        }
        .header p {
            margin: 5px 0;
            font-size: 20px;
        }
        .ticket {
            text-align: center;
            padding: 50px;
            border-radius: 30px;
            font-size: 24px;
            background: rgb(0,22,101);
            background: linear-gradient(360deg, rgba(0,22,101,1) 0%, rgba(29,97,252,1) 33%, rgba(23,196,215,1) 100%);
            color: #fff;
            margin-bottom: 20px; /* Espacio entre el ticket y la marca */
        }
        .ticket h4 {
            margin: 0;
            font-size: 30px;
            color: #fff;
        }
        .ticket p {
            margin: 18px 0;
            color: #fff;
            font-size: 24px;
        }
        .ticket .code {
            font-size: 20px;
            color: #333;
            background: #e0e0e0;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
            background: rgb(41,31,226);
            background: linear-gradient(360deg, rgba(41,31,226,1) 8%, rgba(0,170,255,1) 74%, rgba(5,210,254,1) 100%);
        }
        .footer {
            position: absolute;
            bottom: 30px;
            right: 20px;
            font-size: 18px;
            color: #333;
            text-align: right;
        }
    </style>
    
        <div class='container'>
            <div class='header'>
                <h2>Tu Boleto</h2>
                <p>Te recomendamos traer impreso o mostrar el boleto el dia del evento</p>
            </div>
            <div class='ticket'>
                <h4>Conferencias CMA</h4>
                <p>Plan: " . $registro->paquete->nombre . "</p>
                <p>Nombre: " . $registro->usuario->nombre . " " . $registro->usuario->apellido . "</p>
                <p class='code'>Código: #" . $registro->token . "</p>
            </div>

           
        </div>
        <footer class='footer'>
                <h2>Conferencias CMA</h2>
        </footer>
        ";
    
        // Generar el PDF usando mPDF
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output("boleto_{$registro->token}.pdf", "I");
    }
    
    

    public static function conferencias(Router $router)
    {
        if (!is_auth()) {
            header('Location: /login');
            return; 
        }

        // Validar que el usuario tenga el plan presencial
        $usuario_id = $_SESSION['id'];
        $registro = Registro::where('usuario_id', $usuario_id);
    
        // Aqui validas si el registro se ha completado o no
        $registroFinalizado = EventosRegistros::where('registro_id', $registro->id);
    
        if(isset($registro) && $registro->paquete_id === "2") {
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        } 
    
        // Aqui validas si el registro se ha completado o no
        if(isset($registroFinalizado)) {
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }
        if($registro->paquete_id !=="1"){
            header('Location: /');
            return; 
        }

        $eventos= Evento::ordenar ('hora_id', 'ASC');
        $eventos_formateados = [];

        foreach ($eventos as $evento) {
            $evento ->categoria= Categoria::find ($evento->categoria_id);
            $evento->dia= Dia::find($evento->dia_id);
            $evento ->hora =Hora::find($evento->hora_id);
            $evento->ponente= Ponente::find ($evento->ponente_id);

            if($evento->dia_id === "1" && $evento-> categoria_id === "1") { 
                $eventos_formateados ['conferencias_v'][] = $evento;
            }
            if($evento->dia_id === "2" && $evento ->categoria_id === "1") { 
                $eventos_formateados ['conferencias_s'][] = $evento;
            }
            if($evento->dia_id === "1" && $evento-> categoria_id === "2") {
                $eventos_formateados ['workshops_v'][] = $evento;
            }
            if($evento->dia_id === "2" && $evento-> categoria_id === "2") {
                $eventos_formateados [ 'workshops_s'][] = $evento;
            }
        }

        $regalos =Regalo::all('ASC');


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Revisar que el usuario este autenticado 

            if (!is_auth()) {

                header('Location: /login');
                return; 
            }

            $eventos = explode(',', $_POST['eventos']);
            if (empty($eventos)) {
                echo json_encode(['resultado' => false]);
                return;
            }

            // Obtener el registro de usuario
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if (!isset($registro) || $registro->paquete_id !=="1") {
                echo json_encode(['resultado' => false]);
                return;
            }

            $eventos_array = [];
            // Validar la disponibilidad de los eventos seleccionados 
            foreach ($eventos as $evento_id) {
                $evento = Evento::find($evento_id); 
                // Comprobar que el evento exista
                if (!isset($evento) || $evento->disponibles ==="0") {
                    echo json_encode(['resultado' => false]);

                    return;
                }

                $eventos_array[] = $evento;
            }
            foreach ($eventos_array as $evento) {
                $evento->disponibles -= 1;
                $evento->guardar();
                // Almacenar el registro
                $datos = [
                    'evento_id' => (int) $evento->id,
                    'registro_id' => (int) $registro->id
                ];
                $registro_usuario = new EventosRegistros($datos);
                $registro_usuario->guardar();
            }


            // Almacenar el regalo
            $registro->sincronizar(['regalo_id' => $_POST['regalo_id']]);
            $resultado = $registro->guardar();

            if ($resultado) {
                echo json_encode([
                    'resultado' => $resultado,
                    'token' => $registro->token
                ]);
            } else {
                echo json_encode(['resultado' => false]);
            }

            return;
            
        }
            
        


        $router->render('registro/conferencias', [

            'titulo' => 'Elige Workshops y Conferencias',
            'eventos' => $eventos_formateados,
            'regalos' =>$regalos
            
        ]);
    }
}