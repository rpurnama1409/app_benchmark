<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
       
        // Fungsi yang akan diuji
        function exampleFunction()
        {
            // Gantikan dengan kode yang ingin diukur
            for ($i = 0; $i < 1000; $i++) {
                $result = $i * $i;
            }
        }

        $iterations = 10000;

        $this->benchmark->mark('start');

        for ($i = 0; $i < $iterations; $i++) {
            exampleFunction();
        }

        $this->benchmark->mark('end');

        echo "Jumlah iterasi: $iterations<br>";
        echo "Waktu eksekusi rata-rata: " . $this->benchmark->elapsed_time('start', 'end') * 1000 . " ms";
    }
   
}