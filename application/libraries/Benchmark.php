<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CI_Benchmark {

    protected $marker = array();

    public function __construct()
    {
        $this->mark('total_execution_time_start');
    }

    public function mark($name)
    {
        $this->marker[$name] = microtime(TRUE);
    }

    public function elapsed_time($start = '', $end = '', $decimals = 4)
    {
        if ($start === '') {
            return '{elapsed_time}';
        }

        if (!isset($this->marker[$start])) {
            return '';
        }

        if (!isset($this->marker[$end])) {
            $this->marker[$end] = microtime(TRUE);
        }

        return number_format($this->marker[$end] - $this->marker[$start], $decimals);
    }
}