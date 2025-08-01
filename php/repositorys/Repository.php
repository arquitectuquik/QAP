<?php
include_once __DIR__ . '/../sql_connection.php';
class Repository
{
    /**
     * Se encarga de ejcuta el query en la db y convertir el resultado en un array
     *
     * @param string $query
     * @return array
     */
    protected function ejecutarQuery($query)
    {
        // Log de la consulta para debugging
        error_log("QUERY_DEBUG: " . $query);
        
        $resultadoQuery = mysql_query($query);
        
        // Verificar si hay errores en la consulta
        if (!$resultadoQuery) {
            $error = mysql_error();
            error_log("MYSQL_ERROR: " . $error);
            error_log("QUERY_FAILED: " . $query);
            // En PHP 5.6 no podemos usar Exception de esta manera
            $dataArray = array('error' => 'Error en la consulta SQL: ' . $error);
            return $dataArray;
        }

        $dataArray = array();
        //Se convierte el valor de mysql en valor array
        while ($data = mysql_fetch_assoc($resultadoQuery)) {
            array_push(
                $dataArray,
                $data
            );
        }
        
        error_log("QUERY_RESULT_COUNT: " . count($dataArray));
        return $dataArray;
    }
}