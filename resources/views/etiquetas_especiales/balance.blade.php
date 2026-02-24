{{-- resources/views/etiquetas_especiales/viteri.blade.php --}}
@extends('layouts.etiqueta')

@section('title', 'Etiqueta Viteri')

@push('styles')
<style>
  /* Bloque abajo-derecha como tu etiqueta_balance.php */
  .bloque-balance{
    position: fixed;
    right: 140px;   /* ajusta según tu impresora */
    bottom: 170px;  /* ajusta según tu impresora */
    font-family: Arial, Helvetica, sans-serif;
    font-size: 20px;
    line-height: 1.25;
    font-weight: 800;
    color: #111;
    text-align: left;
    white-space: nowrap;
  }

  /* Inputs estilo impresión (editables sin bordes) */
  .bloque-balance input{
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

  /* Para que QF y SO se editen completo */
  .in-full{
    width: 320px; /* ajusta si el texto se te corta */
  }

  /* Para fechas */
  .in-date{
    width: 150px; /* dd-mm-YYYY */
  }

  .row-line{
    display: flex;
    align-items: baseline;
    gap: 10px;
  }
  .lbl{
    display: inline-block;
    min-width: 52px; /* alinea Elab/Exp */
  }

  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')
@php
  // Defaults: Elab hoy, Exp +2 meses
  $qf   = $qf   ?? 'Q.F. EVELYN GARCÍA';
  $elab = $elab ?? now()->format('d-m-Y');
  $exp  = $exp  ?? now()->addMonthsNoOverflow(2)->format('d-m-Y');
  $so   = $so   ?? 'SO';
@endphp

<div class="bloque-balance">
  <div class="row-line">
    <input class="in-full" type="text" value="{{ $qf }}">
  </div>

  <div class="row-line">
    <span class="lbl">Elab:</span>
    <input class="in-date" type="text" value="{{ $elab }}">
  </div>

  <div class="row-line">
    <span class="lbl">Exp:</span>
    <input class="in-date" type="text" value="{{ $exp }}">
  </div>

  <div class="row-line">
    <input class="in-full" type="text" value="{{ $so }}">
  </div>
</div>
@endsection
