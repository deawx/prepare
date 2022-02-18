<?php

namespace elegance;

use Closure;

/** Adiciona o conteúdo de um array em um template do tipo string */
abstract class Prepare
{

    /**
     * Prepara um texto para ser exibido, subistituindo ocorrencias
     * @param string $string Texto que deve ser preparado
     * @param array $prepare Array com valores a serem inseridos no text
     * Indices (int) do array serão inserido no primeiro bloco {{#}}
     * Incides (string) do array serão insedidos em todos os blocos {{indice}}
     * @return string Texto tratado
     */
    public static function prepare(string $string, $prepare = [])
    {
        preg_match_all("#\{\{[^\}]*+\}\}#i", $string, $parts);

        $parts = array_shift($parts);

        if ($parts) {
            $prepare = self::combine($prepare);
            $string = self::resolve($string, $parts, $prepare);
        }

        return $string;
    }


    /** Aplica o prepara na string */
    protected static function resolve($string, $parts, $prepare)
    {
        list($sequence, $reference) = self::organize($prepare);

        foreach ($parts as $search) {
            if (strpos($string, $search) !== false) {

                $searchTag = substr($search, 2, -2);
                $searchTag = trim($searchTag);
                $parameters = [];

                if (strpos($searchTag, ':') !== false) {
                    $parameters = explode(':', $searchTag);
                    $searchTag = array_shift($parameters);
                    $parameters = explode(',', implode(':', $parameters));
                }

                if ($searchTag == '#') {
                    $replace = array_shift($sequence);
                } else {
                    $replace = $reference[$searchTag] ?? null;
                }

                if ($replace instanceof Closure) {
                    $replace = $replace(...$parameters);
                }

                if (!is_null($replace)) {
                    if ($searchTag == '#') {
                        $string = str_replace_first($search, $replace, $string);
                    } else {
                        $string = str_replace($search, $replace, $string);
                    }
                }
            }
        }

        return $string;
    }

    /** Organiza o array de prepare divinido os parametros sequenciais dos referenciados */
    protected static function organize($prepare)
    {
        $sequence = [];
        $reference = [];
        foreach ($prepare as $key => $value) {
            if (is_numeric($key)) {
                $sequence[] = $value;
            } else {
                $reference[$key] = $value;
            }
        }
        return [$sequence, $reference];
    }

    /** Combina arrays em itens separados por . */
    protected static function combine($prepare)
    {
        ensure_array($prepare);
        foreach ($prepare as $key => $value) {
            if (is_array($value)) {
                unset($prepare[$key]);
                foreach (self::combine($value) as $subKey => $subValue) {
                    $newKey = $subKey == '.' ? $key : "$key.$subKey";
                    $prepare[$newKey] = $subValue;
                }
            }
        }
        return $prepare;
    }
}
