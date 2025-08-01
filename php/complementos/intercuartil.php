<?php



class Intercuartil
{



    private $resultados;

    private $new_array_resultados;

    private $limite_inf;

    private $limite_sup;

    private $media_general;

    private $desvest_general;

    private $iqr;

    private $q1;

    private $q2;

    private $q3;



    function test_intercuartil($array_resultados, $columna, $estado = false)
    {

        $this->new_array_resultados = array();

        $this->resultados = array_column($array_resultados, $columna);



        // Encuentra el primer cuartil

        $n = count($this->resultados);

        //$this->q1 = floor(($n+1)/4)-1;

        //$this->q1 = $this->resultados[$this->q1];

        $position = ($n - 1) * 0.25;



        $low = floor($position);

        $high = ceil($position);



        $lowValue = $this->resultados[$low];

        $highValue = $this->resultados[$high];



        // Interpolación lineal

        $this->q1 = $lowValue + ($position - $low) * ($highValue - $lowValue);



        //$this->q1 = $this->quartiles(0.25);

        // Encuentra el segundo cuartil (mediana)

        $this->q2 = (($n + 1) * 2 / 4) - 1;

        $this->q2 = $this->resultados[$this->q2];



        // Encuentra el tercer cuartil

        $position = ($n - 1) * 0.75;



        $low = floor($position);

        $high = ceil($position);



        $lowValue = $this->resultados[$low];

        $highValue = $this->resultados[$high];



        // Interpolación lineal

        $this->q3 = $lowValue + ($position - $low) * ($highValue - $lowValue);



        //$this->q3 = (($n+1)*3/4)-1;

        //$this->q3 = $this->resultados[$this->q3];



        // Calcula el rango intercuartílico

        $this->iqr = $this->q3 - $this->q1;



        // Calcula el límite inferior

        $this->limite_inf = $this->q1 - (1.5 * $this->iqr);



        // Calcula el límite superior

        $this->limite_sup = $this->q3 + (1.5 * $this->iqr);



        // Calculos generales

        $this->media_general = $this->stats_average($this->resultados);

        $this->desvest_general = $this->stats_standard_deviation($this->resultados, true);

        $n = sizeof($this->resultados);



        // Busca valores atípicos

        // if ($estado == true) {



        //     foreach ($array_resultados as $row_result) {

        //         if ($row_result[$columna] <= $this->limite_inf || $row_result[$columna] >= $this->limite_sup) {

        //             array_push($this->new_array_resultados, $row_result);

        //         }

        //     }



        // } else {





        foreach ($array_resultados as $row_result) {

            // if ($row_result[$columna] > $this->limite_inf && $row_result[$columna] < $this->limite_sup) {

            array_push($this->new_array_resultados, $row_result);

            //     }

            // }

        }

        return $this->new_array_resultados;



    }

    function getPromediosNormales($columna)
    {



        $media = $this->stats_average(array_column($this->new_array_resultados, $columna));

        $de = $this->stats_standard_deviation(array_column($this->new_array_resultados, $columna), true);



        if ($media == 0) {

            $cv = 0;

        } else {

            $cv = (($de / $media) * 100);

        }



        $n = sizeof(array_column($this->new_array_resultados, $columna));



        return [

            "media" => $media,

            "de" => $de,

            "cv" => $cv,

            "n" => $n

        ];

    }

    function stats_standard_deviation(array $a, $sample = true)
    {

        $n = count($a);

        if ($n === 0) {

            // trigger_error("The array has zero elements", E_USER_WARNING);

            return 0;

        }

        if ($sample && $n === 1) {

            // trigger_error("The array has only 1 element", E_USER_WARNING);

            return 0;

        }

        $mean = array_sum($a) / $n;

        $carry = 0.0;

        foreach ($a as $val) {

            $d = ((double) $val) - $mean;

            $carry += $d * $d;

        }
        ;

        if ($sample) {

            --$n;

        }

        return sqrt($carry / $n);

    }

    function stats_average($array)
    {

        if (count($array) == 0) {

            return 0;

        } else {

            return array_sum($array) / count($array);

        }

    }

    public function get_q1()
    {

        if (isset($this->q1)) {

            return round($this->q1, 5);

        }

        return " - - ";

    }

    public function get_q3()
    {

        if (isset($this->q3)) {

            return round($this->q3, 5);

        }

        return " - - ";

    }

    public function get_iqr()
    {

        if (isset($this->iqr)) {

            return round($this->iqr, 5);

        }

        return " - - ";

    }

    public function get_limite_inf()
    {

        if (isset($this->limite_inf)) {

            return round($this->limite_inf, 5);

        }

        return " - - ";

    }



    public function get_limite_sup()
    {

        if (isset($this->limite_sup)) {

            return round($this->limite_sup, 5);

        }

        return " - - ";

    }





    public function get_media_general()
    {

        if (isset($this->media_general)) {

            return round($this->media_general, 3);

        }

        return " - - ";

    }

    public function get_desvest_general()
    {

        if (isset($this->desvest_general)) {

            return round($this->desvest_general, 3);

        }

        return " - - ";

    }

}

