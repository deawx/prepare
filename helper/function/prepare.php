<?php

if (!function_exists('prepare')) {

    /**
     * Prepara um texto para ser exibido, subistituindo ocorrencias
     * @param string $string Texto que deve ser preparado
     * @param array $prepare Array com valores a serem inseridos no text
     * @return string Texto tratado
     */
    function prepare($string = '', $prepare = [])
    {
        return \elegance\Prepare::prepare($string, $prepare);
    }
}
