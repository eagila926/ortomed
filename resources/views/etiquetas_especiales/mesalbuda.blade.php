{{-- resources/views/etiquetas_especiales/mesalbuda.blade.php --}}
@extends('layouts.etiqueta')

@section('title', 'Etiqueta Mesalbuda')

@push('styles')
<style>
  .etq{
    position: relative;
    width: 100%;
    height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
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

  /* Título */
  .titulo{
    position: fixed;
    top: 40px;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 54px;
    font-weight: 900;
    font-style: italic;
    letter-spacing: 1px;
    color: #333;
  }
  .titulo input{
    text-align: center;
    width: 520px;
    font-size: 54px;
    font-weight: 900;
    font-style: italic;
    letter-spacing: 1px;
    color: #333;
  }

  /* Zona central */
  .cuerpo{
    position: fixed;
    left: 80px;
    right: 80px;
    top: 200px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }

  .izq{
    font-size: 30px;
    font-weight: 900;
    line-height: 1.9;
  }

  .izq .linea{
    display: flex;
    gap: 18px;
    align-items: baseline;
  }
  .izq .nom{ width: 260px; }
  .izq .val{ width: 140px; }

  .der{
    text-align: right;
    font-size: 30px;
    font-weight: 500;
    line-height: 1.45;
  }
  .der .fuerte{
    font-weight: 900;
    margin-top: 18px;
  }

  /* Footer */
  .footer{
    position: fixed;
    left: 80px;
    right: 80px;
    bottom: 80px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 30px;
  }

  .footer-izq{
    font-size: 22px;
    font-weight: 900;
    line-height: 1.35;
  }
  .footer-izq input{ width: 420px; }

  .footer-centro{
    font-size: 22px;
    font-weight: 900;
    text-align: center;
    white-space: nowrap;
  }
  .footer-centro .lbl{ margin-right: 10px; }
  .footer-centro input{ width: 150px; }

  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')
@php
  // Defaults según tu captura
  $titulo = $titulo ?? 'MESALBUDA';

  $a1 = $a1 ?? 'BUDESONIDA';
  $a1v = $a1v ?? '1';
  $a1u = $a1u ?? 'mg';

  $a2 = $a2 ?? 'MESALAZINA';
  $a2v = $a2v ?? '2';
  $a2u = $a2u ?? 'g';

  $ind1 = $ind1 ?? 'Enfermedad de Crohn';
  $ind2 = $ind2 ?? 'Recto colitis ulcerativa inespecífica';
  $ind3 = $ind3 ?? 'Enema rectal por 133 mL';

  $qf   = $qf   ?? 'Q.F. EVELYN GARCÍA';
  $dr   = $dr   ?? 'DR. ESTEBAN GONZÁLEZ';
  $elab = $elab ?? now()->format('d-m-Y');
@endphp

<div class="etq">
  <div class="titulo">
    <input type="text" value="{{ $titulo }}">
  </div>

  <div class="cuerpo">
    {{-- Izquierda: activos --}}
    <div class="izq">
      <div class="linea">
        <input class="nom" type="text" value="{{ $a1 }}">
        <div style="display:flex; gap:10px; align-items:baseline;">
          <input class="val" type="text" value="{{ $a1v }}">
          <input style="width:80px;" type="text" value="{{ $a1u }}">
        </div>
      </div>

      <div class="linea">
        <input class="nom" type="text" value="{{ $a2 }}">
        <div style="display:flex; gap:10px; align-items:baseline;">
          <input class="val" type="text" value="{{ $a2v }}">
          <input style="width:80px;" type="text" value="{{ $a2u }}">
        </div>
      </div>
    </div>

    {{-- Derecha: indicaciones --}}
    <div class="der">
      <div><input style="text-align:right; width:520px;" type="text" value="{{ $ind1 }}"></div>
      <div><input style="text-align:right; width:520px;" type="text" value="{{ $ind2 }}"></div>
      <div class="fuerte">
        <input style="text-align:right; width:520px; font-weight:900;" type="text" value="{{ $ind3 }}">
      </div>
    </div>
  </div>

  <div class="footer">
    {{-- Footer izquierda --}}
    <div class="footer-izq">
      <div><input type="text" value="{{ $qf }}"></div>
      <div><input type="text" value="{{ $dr }}"></div>
    </div>

    {{-- Footer centro --}}
    <div class="footer-centro">
      <span class="lbl">Elab:</span>
      <input type="text" value="{{ $elab }}">
    </div>

    {{-- derecha vacío (para que el centro quede centrado real) --}}
    <div style="width:420px;"></div>
  </div>
</div>
@endsection
