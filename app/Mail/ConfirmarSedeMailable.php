<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmarSedeMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre_voluntario;
    public $id_detalle_jornada;
    public $fecha;
    public $lugar;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nombre_voluntario, $id_detalle_jornada, $fecha, $lugar)
    {
        $this->nombre_voluntario = $nombre_voluntario;
        $this->id_detalle_jornada = $id_detalle_jornada;
        $this->fecha = $fecha;
        $this->lugar = $lugar;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.confirmar')->subject('Porfavor confirma tu asistencia');

    }
}
