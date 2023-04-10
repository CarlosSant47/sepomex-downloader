<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;


/**
 * @method static create(array $array)
 */
class CodigoPostalConverter extends Model implements \Serializable {


    protected $fillable = [
        'd_codigo',
        'd_asenta',
        'd_tipo_asenta',
        'd_mnpio',
        'd_estado',
        'd_ciudad',
        'd_CP'
    ];
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $table = 'codigos_postales_converter';

    public function serialize()
    {
        return [
            'codigoPostal' => $this->d_codigo,
        ];
    }

    public function unserialize($data)
    {

    }
}
