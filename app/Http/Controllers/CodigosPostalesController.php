<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CodigoPostalConverter;
use App\Util\Constans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CodigosPostalesController extends Controller
{

    const SEP0MEX_INPUT_FILE = 'sepomex_txt';

    public function getCodigoPostal(Request $request, string $codigoPostal = '') {

        if(!empty($codigoPostal)) {
            return CodigoPostalConverter::where('d_codigo', $codigoPostal)->get();
        }
        return $codigoPostal;
    }


    public function updagradeCodigosPostalesTxtFile()
    {
        // Verifica si el archivo exist que contiene los CP
        if(Storage::disk('local')->exists('CPdescarga.txt')) {
            Storage::disk()->delete('CPdescarga.txt');
        }

        // Se conecta el service de correos de mexico, para descargar el zip que contiene lo codigos postales
        $document = Http::withHeaders([
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Language'           => 'en-US,en;q=0.9,es-MX;q=0.8,es;q=0.7',
            'Cache-Control'             => 'max-age=0',
            'Connection'                => 'keep-alive',
            'Content-Type'              => 'application/x-www-form-urlencoded',
            'Sec-Fetch-Dest'            => 'document',
            'Sec-Fetch-Mode'            => 'navigate',
            'Sec-Fetch-Site'            => 'same-origin',
            'Sec-Fetch-User'            => '?1',
            'Upgrade-Insecure-Requests' => '1'
        ])->asForm()->post('https://www.correosdemexico.gob.mx/SSLServicios/ConsultaCP/CodigoPostal_Exportar.aspx', [
            '__EVENTTARGET'        => '',
            '__EVENTARGUMENT'      => '',
            '__LASTFOCUS'          => '',
            '__VIEWSTATE'          => '/wEPDwUINzcwOTQyOTgPZBYCAgEPZBYCAgEPZBYGAgMPDxYCHgRUZXh0BTfDmmx0aW1hIEFjdHVhbGl6YWNpw7NuIGRlIEluZm9ybWFjacOzbjogQWJyaWwgMyBkZSAyMDIzZGQCBw8QDxYGHg1EYXRhVGV4dEZpZWxkBQNFZG8eDkRhdGFWYWx1ZUZpZWxkBQVJZEVkbx4LXyFEYXRhQm91bmRnZBAVISMtLS0tLS0tLS0tIFQgIG8gIGQgIG8gIHMgLS0tLS0tLS0tLQ5BZ3Vhc2NhbGllbnRlcw9CYWphIENhbGlmb3JuaWETQmFqYSBDYWxpZm9ybmlhIFN1cghDYW1wZWNoZRRDb2FodWlsYSBkZSBaYXJhZ296YQZDb2xpbWEHQ2hpYXBhcwlDaGlodWFodWERQ2l1ZGFkIGRlIE3DqXhpY28HRHVyYW5nbwpHdWFuYWp1YXRvCEd1ZXJyZXJvB0hpZGFsZ28HSmFsaXNjbwdNw6l4aWNvFE1pY2hvYWPDoW4gZGUgT2NhbXBvB01vcmVsb3MHTmF5YXJpdAtOdWV2byBMZcOzbgZPYXhhY2EGUHVlYmxhClF1ZXLDqXRhcm8MUXVpbnRhbmEgUm9vEFNhbiBMdWlzIFBvdG9zw60HU2luYWxvYQZTb25vcmEHVGFiYXNjbwpUYW1hdWxpcGFzCFRsYXhjYWxhH1ZlcmFjcnV6IGRlIElnbmFjaW8gZGUgbGEgTGxhdmUIWXVjYXTDoW4JWmFjYXRlY2FzFSECMDACMDECMDICMDMCMDQCMDUCMDYCMDcCMDgCMDkCMTACMTECMTICMTMCMTQCMTUCMTYCMTcCMTgCMTkCMjACMjECMjICMjMCMjQCMjUCMjYCMjcCMjgCMjkCMzACMzECMzIUKwMhZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZGQCHQ88KwALAGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgEFC2J0bkRlc2NhcmdhECVy01G7PF2Dj0L6Rb29d7m53oc=',
            '__VIEWSTATEGENERATOR' => 'BE1A6D2E',
            '__EVENTVALIDATION'    => '/wEWKALe/9K6BwLG/OLvBgLWk4iCCgLWk4SCCgLWk4CCCgLWk7yCCgLWk7iCCgLWk7SCCgLWk7CCCgLWk6yCCgLWk+iBCgLWk+SBCgLJk4iCCgLJk4SCCgLJk4CCCgLJk7yCCgLJk7iCCgLJk7SCCgLJk7CCCgLJk6yCCgLJk+iBCgLJk+SBCgLIk4iCCgLIk4SCCgLIk4CCCgLIk7yCCgLIk7iCCgLIk7SCCgLIk7CCCgLIk6yCCgLIk+iBCgLIk+SBCgLLk4iCCgLLk4SCCgLLk4CCCgLL+uTWBALa4Za4AgK+qOyRAQLI56b6CwL1/KjtBb/J+uVF4o+PCuWvMdAnNmu7yUmw',
            'cboEdo'               => '00',
            'rblTipo'              => 'txt',
            'btnDescarga.x'        => '55',
            'btnDescarga.y'        => '8'
        ]);
        // Si la peticion no fue exitosa el proceso termina qui
        if (!$document->ok()) {
            throw new \Exception("Error al descargar el archivo desde SEPOMEX");
        }

        // Guardamos el archivo generado de la peticion
        $fileName = uniqid('sepomex_txt') . '.zip';
        Storage::disk('local')->put($fileName, $document->body());
        $fileAbsulutePath = Storage::disk('local')->path($fileName);

        // Descomprimimos el archivo, en dado caso que marque error lo elimina y termina el proceso
        $zip = new \ZipArchive();
        if(!$zip->open($fileAbsulutePath)) {
            Storage::disk()->delete($fileName);
            throw new \Exception("Error al descargar el archivo desde SEPOMEX");
        }
        $zip->extractTo(Storage::disk('local')->path(''));
        $zip->close();
        $codigosPostalesConverter = [];

        //Leemos el archivo TXT por cada linea obtenemso los datos que estan separados por |
        $fileCodigosPostalesContent = fopen(Storage::disk('local')->path('CPdescarga.txt'), 'r');
        $lines = 0;
        while (($line = fgets($fileCodigosPostalesContent)) !== false) {
            $lines++;
            if ($lines < 3){
                continue;
            }
            $fields = explode('|', $line);
            // Gurdamos los datos en la tabla codigos_postales_converter
            CodigoPostalConverter::create([
                'd_codigo' => $fields[0],
                'd_asenta' => utf8_encode($fields[1]),
                'd_tipo_asenta' => utf8_encode($fields[2]),
                'd_mnpio' => utf8_encode($fields[3]),
                'd_estado' => utf8_encode($fields[4]),
                'd_ciudad' => utf8_encode($fields[5]),
                'd_CP' => utf8_encode($fields[6]),
                'c_estado' => $fields[7],
                'c_oficina' => $fields[8],
                'c_CP' => $fields[9],
                'c_tipo_asenta' => $fields[10],
                'c_mnpio' => $fields[11],
                'id_asenta_cpcons' => $fields[12],
                'd_zona' => utf8_encode($fields[13]),
                'c_cve_ciudad' => $fields[14],
            ]);
        }
        return [
            'file' => basename($fileAbsulutePath),
            'rowsTotal' => CodigoPostalConverter::count(),
        ];

    }

}
