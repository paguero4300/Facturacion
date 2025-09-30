<?php

if (!function_exists('hexToRgb')) {
    /**
     * Convierte un color hexadecimal a formato RGB
     *
     * @param string $hex
     * @return string
     */
    function hexToRgb($hex)
    {
        // Eliminar el # si está presente
        $hex = str_replace('#', '', $hex);
        
        // Verificar si es un color hexadecimal válido
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } elseif (strlen($hex) == 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } else {
            // Valor por defecto si el formato es inválido
            return '255, 153, 0';
        }
        
        return "$r, $g, $b";
    }
}