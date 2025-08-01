<?php

    use \setasign\Fpdi\Fpdi;

    require_once("generalInformePAT.php");
    require_once('fpdi/src/autoload.php');

    $pdf = new generalInformePAT('P','mm','letter'); // Página vertical, tamaño carta, medición en Milímetros
    $pdf->AliasNbPages();
    
    $pdf->laboratorio_pat = $laboratorio_pat; 
    $pdf->fecha_envio = $fecha_envio;
    $pdf->estado_reporte = $estado_reporte;
    $pdf->intento_pat = $intento_pat;
    $qry = "SELECT envio FROM $tbl_reto_laboratorio WHERE laboratorio_id_laboratorio = '$laboratorio_pat' and reto_id_reto = '$reto_pat'";
	$qryData = mysql_fetch_array(mysql_query($qry));
    $envio_pat = $pdf->obtenerNomEnvio($qryData['envio']);
    $pdf->envio_pat = $envio_pat;

    $qryProgramaPat = "SELECT 
                programa_pat.nombre,
                programa_pat.sigla
            FROM 
                programa_pat
            WHERE programa_pat.id_programa = '$programa_pat'
    ";

    $qryRetoPat = "SELECT 
        reto.nombre
        FROM 
        reto
        WHERE reto.id_reto = '$reto_pat'
        ";

    $qryArrayRetoPat = mysql_query($qryRetoPat);
    mysqlException(mysql_error(),"_01");
    $array_qry_reto_pat = mysql_fetch_array($qryArrayRetoPat);
    $pdf->nombre_reto = $array_qry_reto_pat["nombre"]; // Nombre del reto de patologia

    $qryArrayProgramaPat = mysql_query($qryProgramaPat);
    mysqlException(mysql_error(),"_01");
    $array_qry_programa_pat = mysql_fetch_array($qryArrayProgramaPat);

    $pdf->tituloprograma = $array_qry_programa_pat["nombre"]; // Programa QAP PAT
    $pdf->siglaprograma = $array_qry_programa_pat["sigla"]; // Programa QAP PAT

    $pdf->AddPage(); // Portada
    $pdf->SetAutoPageBreak(true,10);
    $pdf->AddPage(); // Contraportada
    $pdf->SetAutoPageBreak(true,10);
    
    // Consulta para obtener los casos clinicos de el reto diligenciado
    $qry = "SELECT id_caso_clinico, codigo, nombre, revision FROM caso_clinico where reto_id_reto = $reto_pat and estado = 1";
    $qryArray = mysql_query($qry);
    mysqlException(mysql_error(),"_01");
    while($qryData = mysql_fetch_array($qryArray)) {
        
        $id_caso_clinico = $qryData["id_caso_clinico"];
        $codigo = $qryData["codigo"];
        $nombre = $qryData["nombre"];
        $revision = $qryData["revision"];

        $pdf->AddPage(); // Una hoja por cada caso clínico
        $pdf->SetAutoPageBreak(true,10);
        $pdf->SetFont('Arial','B',10);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Bookmark($nombre. " " . $codigo, false);
        $pdf->SetX(8);
        $pdf->MultiCell(200,4,"Evaluación diagnóstica " . $nombre. "\n" . $codigo,0,'C',0);
        
        
        // Impresion de valores del diagnostico
        // Traer la pregunta del diagnostico
        $qryPregunta = "SELECT
            pregunta.id_pregunta,
            pregunta.nombre
        from grupo join pregunta on grupo.id_grupo = pregunta.grupo_id_grupo
            where grupo.caso_clinico_id_caso_clinico = $id_caso_clinico and grupo.nombre = 'Diagnóstico'";

        $qryArrayPregunta = mysql_query($qryPregunta);
        mysqlException(mysql_error(),"_01");
        while($qryDataPregunta = mysql_fetch_array($qryArrayPregunta)) {
            $id_pregunta = $qryDataPregunta["id_pregunta"];
            $nombre = $qryDataPregunta["nombre"];


            /** **************************  */
            /* TABLE DE DIAGNOSTICO */
            /** **************************  */

            $qryDistractor = "SELECT 
                    id_distractor,
                    nombre
                from distractor
                where pregunta_id_pregunta = $id_pregunta and valor > 0";
            $qryArrayDistractor = mysql_query($qryDistractor);
            mysqlException(mysql_error(),"_03");
            $respuestasCorrectas = array();
            while($qryDataDistractor = mysql_fetch_array($qryArrayDistractor)) {
                array_push($respuestasCorrectas,[
                    "id_distractor" => $qryDataDistractor["id_distractor"],
                    "nombre" => $qryDataDistractor["nombre"] 
                ]);
            }

            $qryDistractorIntento = "SELECT 
                    distractor.id_distractor,
                    distractor.nombre
                from distractor join respuesta_lab on distractor.id_distractor = respuesta_lab.distractor_id_distractor
                where respuesta_lab.pregunta_id_pregunta = '$id_pregunta' and intento_id_intento = $intento_pat limit 1";
            $qryArrayDistractorIntento = mysql_query($qryDistractorIntento);
            mysqlException(mysql_error(),"_04");
            while($qryDataDistractorIntento = mysql_fetch_array($qryArrayDistractorIntento)) {
                $id_distractor = $qryDataDistractorIntento["id_distractor"];
                $nombre_distractor = $qryDataDistractorIntento["nombre"];
            }

            $pdf->SetFont('Arial','B',10);
            $pdf->SetDrawColor(50,50,50);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFillColor(255,255,255);
            $pdf->Ln(9);
            $pdf->SetX(28);
            $pdf->Cell(160,4,"Diagnóstico","TBLR",0,'C',1);
            $pdf->SetWidths(
                array(
                    80,
                    80
                ));
            $pdf->SetAligns(
                array(
                    "C",
                    "C"
                    )
                );
            $pdf->Ln();
            $pdf->SetX(28);
            $respuestasCorrectasTxt = "";
            for($ixd=0; $ixd<sizeof($respuestasCorrectas); $ixd++){
                if($ixd == 1){
                    $respuestasCorrectasTxt = $respuestasCorrectasTxt . "\n"; // Salto de linea apartir del segundo    
                }
                $respuestasCorrectasTxt = $respuestasCorrectasTxt . "  ". $respuestasCorrectas[$ixd]["nombre"];
            }
            $pdf->SetX(28);
            $pdf->Cell(80,5,"Su respuesta ","LR",0,'C',0);
            $pdf->Cell(80,5,"Respuesta referenciada ","R",1,'C',0);
            $pdf->SetX(28);
            $pdf->SetFont('Arial','',8);
            $pdf->Row(array(
                $nombre_distractor,
                $respuestasCorrectasTxt
            ));
            $pdf->SetX(28);
            if($pdf->validarSiRespuestaCorrecta($id_distractor,array_column($respuestasCorrectas, "id_distractor"))){
                $pdf->SetFillColor(124, 221, 5);
                $pdf->Cell(160,5,"Concordante",1,0,'C',1);
            } else {
                $pdf->SetFillColor(236, 112, 99);
                $pdf->Cell(160,5,"No Concordante",1,0,'C',1);
            }

            /* 
                Consulta SQL para contar el numero de distractores por caso clinico             
            */

            $qryDistractoresCount = "SELECT COUNT(distractor.nombre) as cantidadDistractores FROM distractor                

                                INNER JOIN pregunta
                                ON distractor.pregunta_id_pregunta = pregunta.id_pregunta
                                INNER JOIN grupo
                                ON pregunta.grupo_id_grupo = grupo.id_grupo
                                INNER JOIN caso_clinico
                                ON grupo.caso_clinico_id_caso_clinico = caso_clinico.id_caso_clinico
                                
                                WHERE caso_clinico.reto_id_reto = $reto_pat && caso_clinico.codigo = '$codigo' && grupo.nombre = 'Diagnóstico'";

            $qryArrayDistractoresCount = mysql_query($qryDistractoresCount);
            $qryDataDistractoresCount = mysql_fetch_array($qryArrayDistractoresCount);

            /* 
                Consulta SQL para traer todos los distractores por caso clinico             
            */

            $qryDistractores = "SELECT distractor.nombre FROM distractor                

                                INNER JOIN pregunta
                                ON distractor.pregunta_id_pregunta = pregunta.id_pregunta
                                INNER JOIN grupo
                                ON pregunta.grupo_id_grupo = grupo.id_grupo
                                INNER JOIN caso_clinico
                                ON grupo.caso_clinico_id_caso_clinico = caso_clinico.id_caso_clinico
                                
                                WHERE caso_clinico.reto_id_reto = $reto_pat && caso_clinico.codigo = '$codigo' && grupo.nombre = 'Diagnóstico'";

            $qryArrayDistractores = mysql_query($qryDistractores);

            /* 
                Consulta SQL para obtener todas las respuestas reportadas por caso clinico
            */

            $queryPrueba = "SELECT distractor.id_distractor, distractor.nombre

                            from distractor 
            
                            join respuesta_lab 
                            on distractor.id_distractor = respuesta_lab.distractor_id_distractor
                            join intento
                            on intento.id_intento = respuesta_lab.intento_id_intento
                                                
                            where respuesta_lab.pregunta_id_pregunta = '$id_pregunta' && intento.usuario_id_usuario != '297'";

            $qryArrayPrueba = mysql_query($queryPrueba);


            $arrayRespuestas = array();

            while($qryDataPrueba = mysql_fetch_array($qryArrayPrueba)) {

                array_push($arrayRespuestas, $qryDataPrueba['nombre']);
        
            }



            // ----------------------------------------------------------------------------------------------------------------
            // ----------------------------------------------------------------------------------------------------------------
            // ----------------------------------------------------------------------------------------------------------------



            // Panel de consenso de valoreaciones
            $pdf->SetFont('Arial','B',10);
            $pdf->SetDrawColor(50,50,50);
            $pdf->SetLineWidth(0.1);
            $pdf->SetFillColor(255,255,255);
            $pdf->Ln(15);
            $pdf->SetX(28);
            $pdf->SetWidths_dos( array(
                160
                ));
            $pdf->SetAligns_dos( array(
                "C"
                ));
            $pdf->Row_dos(array(
                "Consenso de valoraciones"
            ));
            
            /*  
                Se guardan todos los distractores por caso clinico en un array
            */

            $nombreDistractoresArray = array();

            while($qryDataDistractores = mysql_fetch_array($qryArrayDistractores)) {

                array_push($nombreDistractoresArray, $qryDataDistractores['nombre']);
        
            }

            /*  
                Se define el ancho de las celdas según la cantidad de distractores por caso clinico
            */

            $tamanoCeldaArray = array();

            for ($i=0; $i < $qryDataDistractoresCount['cantidadDistractores']; $i++) { 

                $tamanoCeldaArray[] = (160 / $qryDataDistractoresCount['cantidadDistractores']);

            }

            /*
                Se itera sobre el arreglo de distractores y dentro se itera sobre el arreglo de respuestas.

                Si el distractor es igual a la respuesta se suma 1.

                Se obtiene el porcentaje al final de laiteracion sobre las respuestas
            */

            $arrayAuxiliarRespuestas = array();
            $arrayNumerosLimpios = array();

            for ($i=0; $i < sizeof($nombreDistractoresArray); $i++) { 
                $nombre = $nombreDistractoresArray[$i];
                $numero = 0;
                $total = sizeof($arrayRespuestas);
                for ($j=0; $j < $total; $j++) { 
                    $resp = $arrayRespuestas[$j];
                    if ($resp == $nombre) {
                        $numero++;
                    }
                }

                $numeroLimpio = $numero;
                $dato = $numero . " (" . round($numero/$total * 100,2) . "%)";
                
                array_push($arrayAuxiliarRespuestas, $dato);
                array_push($arrayNumerosLimpios, $numeroLimpio);
            }

            /*  
                Se almacena en un array los valores "C" la cantidad de veces equivalentes a la cantidad de distractores
            */

            $arrayValoresCentrado = array();

            for ($i=0; $i < $qryDataDistractoresCount['cantidadDistractores']; $i++) { 

                array_push($arrayValoresCentrado, "C");

            }

            /*  
                Se imprimen los datos requeridos
            */

            $pdf->SetFont('Arial','',8);
            $pdf->SetWidths_dos($tamanoCeldaArray);
            $pdf->SetAligns_dos($arrayValoresCentrado);
            $pdf->SetX(28);
            $pdf->Row_dos($nombreDistractoresArray);
            $pdf->SetX(28);
            
            for ($i=0; $i < $qryDataDistractoresCount['cantidadDistractores']; $i++) { 

                // Si el valor que se está evaluando es diferente al elemento mas alto del array, lo imprime normal
                if ($arrayAuxiliarRespuestas[$i] != max($arrayNumerosLimpios)) {
                    
                    $pdf->Cell($tamanoCeldaArray[$i],5,$arrayAuxiliarRespuestas[$i],1,0,'C',1);
                    
                } else { // De lo contrario lo imprime de color verde (Sombrea de verde el elemento mayor)

                    $pdf->SetFillColor(124, 221, 5); // Color verde
                    $pdf->Cell($tamanoCeldaArray[$i],5,$arrayAuxiliarRespuestas[$i],1,0,'C',1);
                    $pdf->SetFillColor(255, 255, 255); // Se restablece a color blanco

                }

            }
 
            /* 
                Panel de convenciones de diagnosticos            
            */

            // $qryDistractores = "SELECT distractor.nombre FROM distractor                

            //                     INNER JOIN pregunta
            //                     ON distractor.pregunta_id_pregunta = pregunta.id_pregunta
            //                     INNER JOIN grupo
            //                     ON pregunta.grupo_id_grupo = grupo.id_grupo
            //                     INNER JOIN caso_clinico
            //                     ON grupo.caso_clinico_id_caso_clinico = caso_clinico.id_caso_clinico
                                
            //                     WHERE caso_clinico.reto_id_reto = $reto_pat && caso_clinico.codigo = '$codigo'";

            // $qryArrayDistractores = mysql_query($qryDistractores);

            // $pdf->Ln(9);
            // $pdf->SetX(18);
            // $pdf->SetFont("Arial","B",7.5);
            // $pdf->Cell(199,5,"Convenciones para diagnósticos",0,1,'L',0);
            
            // $indice = 0;

            // while($qryDataDistractores = mysql_fetch_array($qryArrayDistractores)) {

            //     $pdf->SetLeftMargin(18);
            //     $pdf->WriteHTML("<span><b>[".($indice+1)."]</b> " .$qryDataDistractores['nombre']. " </span> ");

            //     $indice++;
        
            // }
        }

        


        /* ********************************** */
        /* Observaciones del caso clinico     */
        /* ********************************** */
        
        $pdf->SetTextColor(40, 40, 40);

        $pdf->Ln(18);
        $pdf->SetX(18);
        $pdf->SetFont('Arial','B',10);
        $pdf->MultiCell(178,4,"Observaciones",0,'L',0);
        
        if($revision == ""){
            $pdf->Ln(4);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(178,4,"No hay revisión para el presente caso clínico",0,'J',0);
        } else {
            $pdf->Ln(4);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(178,4,$revision,0,'J',0);
        }



        $pdf->Ln(9);
        $pdf->SetX(18);
        $pdf->SetFont('Arial','B',10);
        $pdf->MultiCell(178,4,"Referencias",0,'L',0);
        
        $qryReferencias = "SELECT 
            referencia.id_referencia,
            referencia.descripcion
        from referencia
        where caso_clinico_id_caso_clinico = '$id_caso_clinico' and estado = 1";
        $qryArrayReferencias = mysql_query($qryReferencias);
        $checkrows = mysql_num_rows(mysql_query($qryReferencias));
				
        if ($checkrows > 0){ // Si hay referencias
            mysqlException(mysql_error(),"_04");
            while($qryDataReferencias = mysql_fetch_array($qryArrayReferencias)) {
                $nombre_referencia = $qryDataReferencias["descripcion"];
                $pdf->Ln(2);
                $pdf->SetX(18);
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(178,4,$nombre_referencia,0,'J',0);
            }
        } else {
            $pdf->Ln(2);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','',10);
            $pdf->MultiCell(178,4,"No hay referencias para el presente caso clínico",0,'J',0);
        }




        /* ********************************** */
        /* Imagenes del caso clinico     */
        /* ********************************** */
        
        $qryImagenes = "SELECT 
            ruta,
            nombre
        from imagen_adjunta
        where caso_clinico_id_caso_clinico = '$id_caso_clinico' and estado = 1 and tipo = 2";
        $qryArrayImagenes = mysql_query($qryImagenes);
        mysqlException(mysql_error(),"_04");

        $checkrows = mysql_num_rows(mysql_query($qryImagenes));
				
        if ($checkrows > 0){ // Si hay imagenes adjuntas
            
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true,10);

            $pdf->Ln(6);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','B',9);
            $pdf->MultiCell(178,4,"Imagenes relacionadas",0,'L',0);
            
            while($qryDataImagenes = mysql_fetch_array($qryArrayImagenes)) {
                $ruta_imagen = $qryDataImagenes["ruta"];
                $nombre_imagen = $qryDataImagenes["nombre"];
                $pdf->Ln(5);
                $pdf->SetX(45);
                $pdf->Image($ruta_imagen,null,null,130);
                $pdf->Ln(2);
                $pdf->SetX(45);
                $pdf->SetTextColor(50,50,50);
                $pdf->SetFont("Arial","",8);
                $pdf->Cell(130,4,$nombre_imagen,0,0,'C',0);
                
            }
            
            $pdf->SetTextColor(0,0,0);
        }
        
        
    }


    if($see_observaciones == 1){ // Si esta habilitada la opcion de mostrar observaciones del cliente
        $qryObs = "SELECT * FROM intento where id_intento = $intento_pat";
        $qryArrayObs = mysql_query($qryObs);
        mysqlException(mysql_error(),"_01");
        $comentario = "";
        while($qryDataObs = mysql_fetch_array($qryArrayObs)) {
            $comentario = $qryDataObs["comentario"];
        }

        $pdf->Ln(14);
        $pdf->SetX(18);
        $pdf->SetFont('Arial','B',10);
        $pdf->MultiCell(178,4,"Comentarios del intento",0,'L',0);

        if($comentario != ""){ // Si hay observaciones del cliente
            $pdf->Ln(2);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(178,4,$comentario,0,'J',0);
        } else { // No hay observaciones por mostrar
            $pdf->Ln(2);
            $pdf->SetX(18);
            $pdf->SetFont('Arial','',9);
            $pdf->MultiCell(178,4,"No hay observaciones para este intento.",0,'J',0);
        }
    
    }

    $pdf->Ln(8);
    $pdf->SetX(18);
    $pdf->SetFont('Arial','B',9);

    if($pdf->getY() > 215){
        $pdf->addPage(); // Ya que no cabe, agreguelo a una nueva hoja todo
        $pdf->SetAutoPageBreak(true,10);
    }

    $pdf->SetX(10);
    $pdf->MultiCell(196,4,"- Final del reporte -\n   Página ". ($pdf->PageNo() + 1). " de {nb}\n",0,'C',0);
    $pdf->MultiCell(196,4,"\n\n\n\n\n\n\n\nAprobado por:\nAída Porras. Magister en Biología. Doctor in management.\nCoordinadora Programas QAP PAT",0,'C',0);

    $pdf->CreateIndex();
    
    // Cerrar PDF
    $pdf->Close();

    $nomArchivo = "temp-pdf/".uniqid().".pdf";
    $pdf->Output("F",$nomArchivo,true);

    $pdf = new Fpdi('P','mm','letter');
    $pdf->AliasNbPages();
    $pageCount = $pdf->setSourceFile($nomArchivo);
    for($pageNumber=1; $pageNumber<$pageCount; $pageNumber++){
        
        if($pageNumber == 3){
            $templateID = $pdf->importPage($pageCount);
            $pdf->getTemplateSize($templateID);
            $pdf->addPage();
            $pdf->SetAutoPageBreak(true,10);

            $pdf->useTemplate($templateID);

            // Contador de paginas
            $pdf->SetXY(102,10);
            $pdf->SetFont('Arial','',7);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(200,4,$pdf->PageNo() . " de {nb}",0,0,'C',0);
        }

        $templateID = $pdf->importPage($pageNumber);
        $pdf->getTemplateSize($templateID);
        $pdf->addPage();
        $pdf->SetAutoPageBreak(true,10);

        $pdf->useTemplate($templateID);
        
        if($pageNumber > 1){
            // Contador de paginas
            $pdf->SetXY(102,10);
            $pdf->SetFont('Arial','',7);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(200,4,$pdf->PageNo() . " de {nb}",0,0,'C',0);
        }
    }
    

    // Consulta para obtener el nombre de usuario
    $qrySFR = "SELECT cod_usuario FROM intento join usuario on usuario.id_usuario = intento.usuario_id_usuario WHERE intento.id_intento = $intento_pat limit 1";
    $qryArraySRF = mysql_query($qrySFR);
    global $cod_usuario_srf;
    mysqlException(mysql_error(),"_01");
    while ($qryDataSRF = mysql_fetch_array($qryArraySRF)) {
        $cod_usuario_srf = $qryDataSRF["cod_usuario"];
    }

    $pdf->Output("I","$cod_usuario_srf.pdf");
    unlink($nomArchivo);

?>