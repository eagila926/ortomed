@extends('layouts.etiqueta')

@section('title', 'Etiqueta Dra. Julisa')

@push('styles')
<style>
  /* Footer tipo "etiqueta_js" (3 columnas) */
  .footer-etiqueta{
    position: fixed;
    left: 80px;        
    right: 80px;       
    bottom: 0px;      
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 40px;

    font-family: Arial, sans-serif;
    font-size: 23px;
    font-weight: 750;
    color: #111;
  }

  .footer-etiqueta .col{
    flex: 1;
    display: flex;
    align-items: center;
    min-width: 0;
  }
  .footer-etiqueta .col.center{ justify-content: center; }
  .footer-etiqueta .col.right{ justify-content: flex-end; }

  /* Inputs estilo impresión */
  .footer-etiqueta input{
    border: none;
    outline: none;
    background: transparent;

    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    color: inherit;

    padding: 0;
    margin: 0;

    /* evita saltos raros */
    min-width: 0;
  }

  /* QF y SO: input completo */
  .input-full{
    width: 100%;
  }

  /* Centro: "Elab:" + fecha, que se edita solo la fecha */
  .label{
    margin-right: 10px;
    white-space: nowrap;
  }
  .input-date{
    width: 160px; /* ancho típico dd-mm-yyyy */
    text-align: left;
  }

  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')
@php
  // Defaults
  $qf   = $qf   ?? 'Q.F. EVELYN GARCÍA';
  $elab = $elab ?? now()->format('d-m-Y');
  $so   = $so   ?? 'SO';
@endphp

<div class="footer-etiqueta">
  {{-- Izquierda: QF --}}
  <div class="col left">
    <input class="input-full" type="text" value="{{ $qf }}">
  </div>

  {{-- Centro: Elab: FECHA --}}
  <div class="col center">
    <span class="label">Elab:</span>
    <input class="input-date" type="text" value="{{ $elab }}">
  </div>

  {{-- Derecha: SO --}}
  <div class="col right">
    <input class="input-full" type="text" value="{{ $so }}">
  </div>
</div>
@endsection
