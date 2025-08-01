<?php
    
    function enviarCorreoLC($id_laboratorio,$id_usuario,$id_muestra,$fecha_actual){

        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);

        date_default_timezone_set("America/Bogota");		
        ini_set('max_execution_time', 300);		
        require_once("../passwordRecovery/php/PHPMailer/PHPMailerAutoload.php");
           
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = false;

        $mail->Port = 587;
        $mail->Host = "stmp.office365.com";
        
        $mail->SMTPAuth = true;                  
        $mail->SMTPSecure = "ssl"; // or tls
        
        $mail->Username = "no-reply@quik.com.co";
        $mail->Password = "Quik2017";
        
        $mail->setFrom('no-reply@quik.com.co', 'QAP Online | Quik S.A.S.');

        $mail->AddAddress("qap@quik.com.co");
        /*
        $qryCoords = "SELECT email_usuario FROM usuario WHERE tipo_usuario = 100 and estado = 1";
        $qryArrayCoords = mysql_query($qryCoords);
        mysqlException(mysql_error(),"_01correo");
        while ($qryDataCoords = mysql_fetch_array($qryArrayCoords)) {
            $mail->addBCC($qryDataCoords["email_usuario"]);
        }
        */
        
        $mail->addBCC("viviana.sanchez@quik.com.co");

        $mail->CharSet =  "utf-8";

        $mail->Subject = "Reporte de resultados QAP LC";
        $mail->IsHTML(true);

        // Obtener nombre de usuario
        $qry = "SELECT nombre_usuario from usuario where id_usuario = '$id_usuario'";
        $qryArray = mysql_query($qry);
        mysqlException(mysql_error(),"_02correo");
        $qryData = mysql_fetch_array($qryArray);
        $nombre_usuario = $qryData["nombre_usuario"];
    
        // Obtener nombre del programa, ronda y muestra
        $qry = "SELECT 
            programa.nombre_programa,
            ronda.no_ronda,
            contador_muestra.no_contador,
            muestra.codigo_muestra
        from 
            programa
            join ronda on programa.id_programa = ronda.id_programa
            join contador_muestra on ronda.id_ronda = contador_muestra.id_ronda
            join muestra on muestra.id_muestra = contador_muestra.id_muestra
        where muestra.id_muestra = $id_muestra";

        $qryArray = mysql_query($qry);
        mysqlException(mysql_error(),"_03");
        $qryData = mysql_fetch_array($qryArray);
        $nombre_programa = $qryData["nombre_programa"];
        $no_ronda = $qryData["no_ronda"];
        $no_contador = $qryData["no_contador"];
        $codigo_muestra = $qryData["codigo_muestra"];
        
        // Obtener nombre de laboratorio
        $qry = "SELECT no_laboratorio, nombre_laboratorio from laboratorio where id_laboratorio = '$id_laboratorio'";
        $qryArray = mysql_query($qry);
        mysqlException(mysql_error(),"_04correo");
        $qryData = mysql_fetch_array($qryArray);
        $nombre_laboratorio = $qryData["no_laboratorio"] . " - ". $qryData["nombre_laboratorio"];

        $mail->Body = 
                "<p>Estimado Coordinador de QAP Laboratorio Clínico</p>".
                "<br/>".
                "<br/>".
                "<p>QAP Online informa que el usuario <strong>$nombre_usuario</strong> acaba de reportar una muestra, a continuación la información detallada</p>".
                "<br/>".
                "<br/>".
                "<strong>Laboratorio:</strong> $nombre_laboratorio</br>".
                "<strong>Hora:</strong> $fecha_actual</br>".
                "<strong>Programa:</strong> $nombre_programa</br>".
                "<strong>Ronda:</strong> $no_ronda</br>".
                "<strong>Número de muestra:</strong> $no_contador</br>".
                "<strong>Código de muestra:</strong> $codigo_muestra</br>".
                "<br/>".
                "<br/>".
                "<br/>".
                "<p>*** NO RESPONDER - Mensaje Generado Automáticamente ***</p>".
                "<p>Este correo es únicamente informativo y es de uso exclusivo del destinatario(a), puede contener información privilegiada y/o confidencial. 
                    Si no es usted el destinatario(a) deberá borrarlo inmediatamente. Queda notificado que el mal uso, divulgación no autorizada, alteración y/o  
                    modificación malintencionada sobre este mensaje y sus anexos quedan estrictamente prohibidos y pueden ser legalmente sancionados. - 
                    Quik S.A.S.  no asume ninguna responsabilidad por estas circunstancias-</p>";	

        $mail->send();

    }
?>