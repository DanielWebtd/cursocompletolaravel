<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QueryBuilderController extends Controller
{
    public function pruebas(Request $request)
    {
        $extension = $request->extension;
        $gananciaGenerada = $request->gananciaGenerada;
        $fecha1 = $request->fecha1;
        $fecha2 = $request->fecha2;

        

        $data = DB::table('videos') // Empezar Query Builder y a su vez "equivale" a clausula FROM.
            ->select('videos.id AS idVideo', 'titulo', 'descripcion', 'extension', 'ganancia_generada', 'comentario', 'comentarios.publicado AS comentarioPublicado', 'cantidad_visitas', 'fecha_publicacion')
            ->leftJoin('detalles_video', 'videos.id', '=', 'detalles_video.video_id')
            ->leftJoin('comentarios', 'comentarios.video_id', '=', 'videos.id')
            ->where('videos.publicado', 1)

            /* la funcion callback "when" se aplica cuando el valor-variable (primer parametro) se evalue a TRUE,
            que sea un valor TRUTHY.*/
            ->when($extension, function($query, $extension) {
                
                return $query->where('extension', $extension);
            })
            ->when($gananciaGenerada, function($query, $gananciaGenerada) {
                
                return $query->where('ganancia_generada', (float)$gananciaGenerada);
            })
            ->when($fecha1, function($query) use($request) {
                
                return $query->whereBetween('fecha_publicacion', [$request->fecha1, $request->fecha2]);
            })
            ->when($request->ordenacion, function($query) use($request) {
                
                switch ($request->ordenacion) {

                    case 'ganancia_generada':
                        
                        return $query->orderBy('ganancia_generada');
                    break;
                    case 'titulo':
                        return $query->orderBy('titulo');
                    break;
                }
            }, function($query) {

                /* si entro aqui es porque el primer parametro no es un valor truthy sino falsy */
                return $query->orderBy('videos.id');
            })
            ->get(); // a partir de aqui ya es una coleccion.
    
        return view('query-builder.videos')
            ->with('data', $data);
    }

    public function filtros() 
    {
        return view('query-builder.form-filtros');
    }

    public function insert() 
    {
        DB::table('users')->insert([

            'name' => 'WEBTD',
            'email' => 'soportewebtd@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'remember_token' => null,
            'created_at' => now(), // 2022-03-22 11:13:45,
            'updated_at' => now()
        ]);

        return 'ok';
    }
}
