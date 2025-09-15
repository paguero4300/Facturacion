<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array crearEmpresa(string $ruc, string $tipoPlan = '01')
 * @method static string obtenerToken()
 * @method static array firmarXml(string $nombreArchivo, string $xmlContent)
 * @method static array enviarXmlFirmado(string $nombreXmlFirmado, string $xmlFirmadoContent)
 * @method static array consultarTicket(string $nombreArchivo)
 * @method static array procesarDocumento(string $nombreArchivo, string $xmlContent)
 * @method static void setCredenciales(string $username, string $password)
 * @method static bool tieneCredenciales()
 */
class Qpse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'qpse';
    }
}