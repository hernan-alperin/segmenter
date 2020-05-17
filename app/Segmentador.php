<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Config;

class Segmentador extends Model
{
    private $resultado=null;
    
    public function segmentar_a_lado_completo($aglo,$prov,$dpto,$frac,$radio,$vivs_deseada,$vivs_max,$vivs_min,$mza_indivisible)
	{
        // Set the limit to 500 MB.
        // Varios intentos para aumentar la memoria.
        /*
        $fiveMBs = 500 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fwrite($fp, 'test');
        rewind($fp);
        ini_set('memory_limit','512M');
        */
        $processLog = Process::fromShellCommandline('echo "Se va a segmentar: $info_segmenta"  >> segmentaciones.log');
        $processLog->run(null, ['info_segmenta' => " Aglomerado: ".$aglo ." Radio ".$radio]);

        $esquema = 'e'.$aglo;

        // Ejemplo: python3 app/developer_docs/segmentacion-core/lados_completos/lados_completos.py e0777.arc 50 084 1 4 20 30 10 1 
	$process = Process::fromShellCommandline('/usr/bin/python3 ../app/developer_docs/segmentacion-core/lados_completos/lados_completos.py $tabla $prov $dpto $frac $rad $min $max $deseada $indivisible',null,['PYTHONIOENCODING' => 'utf8',
		'MANDARINA_DATABASE' => Config::get('database.connections.pgsql.database'),
		'MANDARINA_USER' => Config::get('database.connections.pgsql.username'),
		'MANDARINA_PASS' => Config::get('database.connections.pgsql.password'),
		'MANDARINA_HOST' => Config::get('database.connections.pgsql.host'),
		'MANDARINA_PORT' => Config::get('database.connections.pgsql.port')
	]);
        $process->setTimeout(5 * 60 * 60);
       
        $process->run(null, ['tabla' => $esquema.".arc",'prov'=>$prov,'dpto'=>$dpto,'frac'=>$frac,'rad'=>$radio,
                             'deseada'=>$vivs_deseada,'max'=>$vivs_max,'min'=>$vivs_min,'indivisible'=>$mza_indivisible]);
                        // executes after the command finishes
                        if (!$process->isSuccessful()) {
                                dd($process->getErrorOutput());
                        }else{  
                            return $this->resultado=$process->getOutput();
                        }
            // e0777.arc 50 084 1 4 20 30 10 1');
     }

    public function ver_segmentacion()
    {
       if (isset($this->resultado)){
            return $this->resultado;
        }else{ return "No hay segmentacion realizada.";}
    }
}
