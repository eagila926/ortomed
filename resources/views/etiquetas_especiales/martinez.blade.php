@extends('layouts.etiqueta')

@section('title', 'Etiqueta Martínez')

@push('styles')
<style>
  .bloque-etiqueta{
    position: fixed;
    left: 80px;
    bottom: 70px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 24px;
    line-height: 1.15;
    font-weight: 700;
    color: #111;
  }

  /* Inputs estilo impresión */
  .bloque-etiqueta input{
    border: none;
    outline: none;
    background: transparent;
    font-family: inherit;
    font-size: inherit;
    font-weight: inherit;
    color: inherit;
    padding: 0;
    margin: 0;
    width: 100%;
  }

  .linea{
    display: block;
  }

  /* En impresión no mostrar cursores */
  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')
@php
  $qf   = $qf   ?? 'Q.F. EVELYN GARCÍA';
  $elab = $elab ?? now()->format('d-m-Y');
  $so   = $so   ?? 'SO';
@endphp

<div class="bloque-etiqueta">

  <span class="linea">
    <input type="text" value="{{ $qf }}">
  </span>

  <span class="linea">
    <input type="text" value="Elab:{{ $elab }}">
  </span>

  <span class="linea">
    <input type="text" value="{{ $so }}">
  </span>

</div>
@endsection
