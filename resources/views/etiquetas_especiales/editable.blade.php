{{-- resources/views/etiquetas_especiales/editable.blade.php --}}
@extends('layouts.etiqueta')

@section('title', 'Etiqueta Editable')

@push('styles')
<style>
  /* Tipografía general similar a tu etiqueta antigua */
  .etq{
    position: relative;
    width: 100%;
    height: 100vh;
    font-family: Arial, sans-serif;
    color: #111;
  }

  /* Inputs estilo impresión */
  .etq input{
    border: none;
    outline: none;
    background: transparent;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
    padding: 0;
    margin: 0;
  }

  /* Título centrado */
  .etq .titulo{
    position: fixed;
    top: 38px;            /* ajusta */
    left: 0;
    right: 0;
    text-align: center;
    font-style: italic;
    font-weight: 800;
    font-size: 34px;      /* ajusta si necesitas */
    letter-spacing: .5px;
  }
  .etq .titulo input{
    text-align: center;
    width: 520px;
    font-style: italic;
    font-weight: 800;
    font-size: 34px;
  }

  /* Bloque izquierdo */
  .etq .bloque-izq{
    position: fixed;
    left: 120px;         /* ajusta para imprimir */
    bottom: 150px;       /* ajusta para imprimir */
    font-weight: 800;
    font-size: 22px;
    line-height: 1.35;
    white-space: nowrap;
  }

  /* Bloque derecho */
  .etq .bloque-der{
    position: fixed;
    right: 220px;        /* ajusta para imprimir */
    bottom: 150px;       /* ajusta para imprimir */
    font-weight: 800;
    font-size: 22px;
    line-height: 1.35;
    white-space: nowrap;
  }

  .linea{
    display: flex;
    align-items: baseline;
    gap: 10px;
  }
  .lbl{
    min-width: 110px; /* alinea DR/Contiene/Posologia */
  }

  /* Anchos de inputs */
  .in-med{ width: 420px; }
  .in-contiene{ width: 250px; }
  .in-poso{ width: 520px; }

  .in-qf{ width: 340px; }
  .in-elab{ width: 200px; }
  .in-so{ width: 200px; }

  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')
@php
  // Defaults (puedes cambiar a lo que uses normalmente)
  $titulo    = $titulo    ?? 'FORMULA MAGISTRAL';

  $dra       = $dra       ?? '';                 // DR.(A):
  $contiene  = $contiene  ?? 'CÁPSULAS';         // Contiene:
  $posologia = $posologia ?? 'TOMAR 0 CÁPSULAS DIARIAS';

  $qf        = $qf        ?? 'Q.F. EVELYN GARCÍA';
  $elab      = $elab      ?? '';                 // en tu captura sale vacío
  $so        = $so        ?? 'SO.';
@endphp

<div class="etq">

  {{-- TÍTULO --}}
  <div class="titulo">
    <input type="text" value="{{ $titulo }}">
  </div>

  {{-- BLOQUE IZQUIERDO --}}
  <div class="bloque-izq">
    <div class="linea">
      <span class="lbl">DR.(A):</span>
      <input class="in-med" type="text" value="{{ $dra }}">
    </div>

    <div class="linea">
      <span class="lbl">Contiene:</span>
      <input class="in-contiene" type="text" value="{{ $contiene }}">
    </div>

    <div class="linea">
      <span class="lbl">Posología:</span>
      <input class="in-poso" type="text" value="{{ $posologia }}">
    </div>
  </div>

  {{-- BLOQUE DERECHO --}}
  <div class="bloque-der">
    <div class="linea">
      <input class="in-qf" type="text" value="{{ $qf }}">
    </div>

    <div class="linea">
      <span class="lbl" style="min-width:70px;">Elab:</span>
      <input class="in-elab" type="text" value="{{ $elab }}">
    </div>

    <div class="linea">
      <input class="in-so" type="text" value="{{ $so }}">
    </div>
  </div>

</div>
@endsection
