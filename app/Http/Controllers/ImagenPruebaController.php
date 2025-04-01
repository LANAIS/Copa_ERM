<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImagenPruebaController extends Controller
{
    public function test()
    {
        // Crear carpeta si no existe
        $storagePath = storage_path('app/public/robots/fotos');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }
        
        // Crear una imagen de ejemplo
        $width = 300;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);
        
        // Colores
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $blue = imagecolorallocate($image, 0, 0, 255);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fondo blanco
        imagefill($image, 0, 0, $white);
        
        // Dibujar un robot simple
        imagefilledrectangle($image, 100, 100, 200, 200, $blue); // Cuerpo
        imagefilledrectangle($image, 100, 50, 200, 100, $red); // Cabeza
        imagefilledrectangle($image, 70, 120, 100, 180, $green); // Brazo izquierdo
        imagefilledrectangle($image, 200, 120, 230, 180, $green); // Brazo derecho
        imagefilledrectangle($image, 120, 200, 140, 250, $black); // Pierna izquierda
        imagefilledrectangle($image, 160, 200, 180, 250, $black); // Pierna derecha
        
        // Guardar la imagen
        $fileName = 'robot_test_' . time() . '.jpg';
        $path = $storagePath . '/' . $fileName;
        imagejpeg($image, $path, 90);
        imagedestroy($image);
        
        // Verificar que la imagen existe
        if (File::exists($path)) {
            // Asegurarnos que la URL generada incluya el puerto correcto
            $url = Storage::url('robots/fotos/' . $fileName);
            $appUrl = config('app.url');
            
            return response()->json([
                'success' => true,
                'message' => 'Imagen creada exitosamente',
                'path' => 'robots/fotos/' . $fileName,
                'url' => $url,
                'app_url' => $appUrl,
                'full_url' => $appUrl . '/storage/robots/fotos/' . $fileName
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la imagen',
                'storage_path' => $storagePath,
                'file_path' => $path,
                'app_url' => config('app.url')
            ]);
        }
    }
} 