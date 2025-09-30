<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetallesController extends Controller
{
    /**
     * Muestra la página principal de Detalles
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('index');
    }
    
    /**
     * Procesa el formulario de contacto
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);
        
        // Aquí se procesaría el formulario (enviar email, guardar en BD, etc.)
        // Por ahora, solo redirigimos con un mensaje de éxito
        
        return redirect()->route('contacto')->with('success', '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
    }
}
