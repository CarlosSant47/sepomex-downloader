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

    public function getCodigoPostal(Request $request, string $codigoPostal = '')
    {


        $codigosPostaes = [];
        if (!empty($codigoPostal)) {
            $codigosPostaes = CodigoPostalConverter::where('d_codigo', $codigoPostal);
            #->where('d_asenta', 'LIKE', "%%")
        }
        if (!empty($request->get('colonia'))) {
            $codigosPostaes->where('d_asenta', 'LIKE', '%' . $request->get('colonia') . '%');
        }
        return $codigosPostaes->get();
    }


    public function updagradeCodigosPostalesTxtFile()
    {
        // Verifica si el archivo exist que contiene los CP
        if (Storage::disk('local')->exists('CPdescarga.txt')) {
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
        ])->asForm()->post(Constans::SEPOMEX_URL_SERVICE_EXPORTAR, [
            '__EVENTTARGET'        => '',
            '__EVENTARGUMENT'      => '',
            '__LASTFOCUS'          => '',
            '__VIEWSTATE'          => Constans::TOKEN_STATE_VALUE,
            '__VIEWSTATEGENERATOR' => 'BE1A6D2E',
            '__EVENTVALIDATION'    => Constans::TOKEN_EVENT_VALIDATION,
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
        if (!$zip->open($fileAbsulutePath)) {
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
            if ($lines < 3) {
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
