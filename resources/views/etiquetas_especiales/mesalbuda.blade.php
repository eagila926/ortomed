{{-- resources/views/etiquetas_especiales/mesalbuda.blade.php --}}
@extends('layouts.etiqueta')

@section('title', 'Etiqueta Mesalbuda')

@php
    $prefill = session('etiqueta_preview', []);
    $soPrefill = $prefill['so'] ?? null;
    $medicoPrefill = $prefill['medico'] ?? null;
    $pacientePrefill = $prefill['paciente'] ?? null;

    function nombreCorto($full) {
        $full = trim((string) $full);
        if ($full === '') return null;

        $p = preg_split('/\s+/', $full);
        $first = $p[0] ?? '';
        $last  = $p[count($p)-1] ?? '';

        return trim($first . ' ' . $last);
    }

    $medicoCorto = nombreCorto($medicoPrefill);

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
    $dr   = $dr   ?? ($medicoCorto ?: 'DR. ESTEBAN GONZÁLEZ');
    $elab = $elab ?? now()->format('d-m-Y');

    $canRecetas = auth()->check() && auth()->user()->hasRole(['Admin','Laboratorio']);
@endphp

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

  /* ===== Modal de recetas ===== */
  #dlg-recetas::backdrop {
    background: rgba(15,23,42,.45);
  }

  #dlg-recetas {
    border: none;
    border-radius: 18px;
    padding: 0;
    max-width: 780px;
    width: 96%;
    box-shadow: 0 18px 45px rgba(15,23,42,0.38);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  }

  .dlg-recetas__header {
    padding: 16px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg,#0d6efd,#2563eb);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
  }

  .dlg-recetas__title {
    margin: 0;
    font-size: 20px;
    font-weight: 650;
  }

  .dlg-recetas__subtitle {
    margin: 2px 0 0;
    font-size: 13px;
    opacity: .9;
  }

  .dlg-recetas__badge {
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(15,23,42,.18);
    font-size: 12px;
    font-weight: 500;
  }

  .dlg-recetas__body {
    padding: 18px 24px 16px;
    background: #f9fafb;
  }

  .dlg-recetas__grid {
    display: grid;
    gap: 14px 18px;
  }

  @media (min-width: 640px) {
    .dlg-recetas__grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  .form-row {
    margin: 0;
    font-size: 14px;
  }

  .form-row label {
    display: block;
    font-weight: 600;
    margin-bottom: 4px;
    color: #111827;
  }

  .form-row small {
    font-size: 12px;
  }

  .dlg-recetas__input,
  .dlg-recetas__input-date {
    width: 100%;
    padding: 7px 9px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 14px;
    transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    background: #ffffff;
  }

  .dlg-recetas__input:focus,
  .dlg-recetas__input-date:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 1px rgba(37,99,235,.25);
  }

  .dlg-recetas__footer {
    padding: 10px 24px 16px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f9fafb;
    border-bottom-left-radius: 18px;
    border-bottom-right-radius: 18px;
  }

  .btn-primary {
    padding: 8px 16px;
    background:#0d6efd;
    border:none;
    color:#fff;
    border-radius:999px;
    font-size: 14px;
    font-weight: 600;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:6px;
    box-shadow:0 6px 14px rgba(37,99,235,.35);
    transition: background .15s ease, transform .08s ease, box-shadow .15s ease, opacity .15s ease;
  }

  .btn-primary:hover:not(:disabled) {
    background:#0b5ed7;
    transform: translateY(-1px);
    box-shadow:0 10px 24px rgba(37,99,235,.40);
  }

  .btn-primary:disabled {
    opacity:.55;
    cursor:not-allowed;
    box-shadow:none;
  }

  .btn-outline {
    padding: 8px 16px;
    border-radius:999px;
    border:1px solid #d1d5db;
    background:#ffffff;
    font-size:14px;
    font-weight:500;
    cursor:pointer;
    transition: background .15s ease, border-color .15s ease, color .15s ease;
  }

  .btn-outline:hover {
    background:#f3f4f6;
    border-color:#9ca3af;
  }

  .sugs-panel {
    position:absolute;
    left:0;
    top:calc(100% + 4px);
    z-index:1000;
    background:#fff;
    border:1px solid #d1d5db;
    width:100%;
    max-height:230px;
    overflow-y:auto;
    box-shadow:0 14px 30px rgba(15,23,42,.25);
    border-radius:10px;
  }

  .sugs-item {
    padding:8px 11px;
    cursor:pointer;
    font-size:13px;
  }

  .sugs-item:hover {
    background:#eff6ff;
  }

  .sugs-item.empty {
    color:#9ca3af;
    cursor:default;
  }

  #medico_sel {
    font-size:12px;
    color:#4b5563;
  }

  @media print{
    input{ caret-color: transparent; }
  }
</style>
@endpush

@section('content')

@if($canRecetas)
  <dialog id="dlg-recetas">
    <form method="POST" action="{{ route('recetas.store') }}" id="frm-recetas" novalidate>
      @csrf

      <div class="dlg-recetas__header">
        <div>
          <h3 class="dlg-recetas__title">Datos para guardar recetas</h3>
          <p class="dlg-recetas__subtitle">
            Completa la información para generar las etiquetas de esta fórmula.
          </p>
        </div>
        <span class="dlg-recetas__badge">
          {{ $formula->codigo ?? 'FÓRMULA' }}
        </span>
      </div>

      <div class="dlg-recetas__body">
        <div class="dlg-recetas__grid">

          <div class="form-row">
            <label for="num_etiquetas">Número de etiquetas a imprimir</label>
            <input type="number" name="num_etiquetas" id="num_etiquetas"
                   class="dlg-recetas__input"
                   value="1" min="1" max="200" required>
            <small>Si eliges más de 1, el paciente se llenará de forma automática.</small>
          </div>

          <div class="form-row">
            <label for="codigo_formula">Código de fórmula</label>
            <input type="text" name="codigo_formula" id="codigo_formula"
                   class="dlg-recetas__input"
                   value="{{ $formula->codigo ?? '' }}"
                   required>
            <small>Este código es editable.</small>
          </div>

          <div class="form-row">
            <label for="so_modal">SO</label>
            <input type="text" name="so" id="so_modal"
                   class="dlg-recetas__input"
                   inputmode="numeric" pattern="\d+" maxlength="50"
                   placeholder="Solo números" required
                   value="{{ $soPrefill }}">
          </div>

          <div class="form-row">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha"
                   class="dlg-recetas__input-date"
                   value="{{ now()->toDateString() }}" required>
          </div>

          <div class="form-row" style="grid-column:1 / -1;">
            <label>Médico (buscar por nombre/apellido)</label>
            <div style="position:relative; max-width:420px;">
              <input type="text" id="medico_search"
                     class="dlg-recetas__input"
                     placeholder="Ej. 'María López'"
                     autocomplete="off"
                     value="{{ $medicoPrefill }}">
              <div id="medico_sugs"></div>
            </div>
            <input type="hidden" name="cedula_medico" id="cedula_medico" required>
            <input type="hidden" name="medico_nombre" id="medico_nombre" value="{{ $medicoPrefill }}">
            <small id="medico_sel" style="display:block;margin-top:4px;"></small>
          </div>

          <div id="grp_paciente" class="form-row" style="grid-column:1 / -1; max-width:420px;">
            <label for="paciente">Paciente (opcional si solo 1 etiqueta)</label>
            <input type="text" name="paciente" id="paciente"
                   class="dlg-recetas__input"
                   maxlength="50"
                   placeholder="Dejar vacío para nombre aleatorio"
                   value="{{ $pacientePrefill }}">
          </div>

        </div>

        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
      </div>

      <div class="dlg-recetas__footer">
        <button type="button" id="btn-cancel" class="btn-outline">Cancelar</button>
        <button type="submit" id="btn-save" class="btn-primary" disabled>
          Guardar recetas y continuar
        </button>
      </div>
    </form>
  </dialog>
@endif

<div class="etq">
  <div class="titulo">
    <input type="text" value="{{ $titulo }}">
  </div>

  <div class="cuerpo">
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

    <div class="der">
      <div><input style="text-align:right; width:520px;" type="text" value="{{ $ind1 }}"></div>
      <div><input style="text-align:right; width:520px;" type="text" value="{{ $ind2 }}"></div>
      <div class="fuerte">
        <input style="text-align:right; width:520px; font-weight:900;" type="text" value="{{ $ind3 }}">
      </div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-izq">
      <div><input type="text" value="{{ $qf }}"></div>
      <div><input type="text" value="{{ $dr }}"></div>
    </div>

    <div class="footer-centro">
      <span class="lbl">Elab:</span>
      <input type="text" value="{{ $elab }}">
    </div>

    <div style="width:420px;"></div>
  </div>
</div>

@if($canRecetas)
<script>
  const mustOpen = {{ session()->has('recetas_guardadas') ? 'false' : 'true' }};
  const dlg = document.getElementById('dlg-recetas');

  if (mustOpen && dlg && !dlg.open) dlg.showModal();

  document.getElementById('btn-cancel')?.addEventListener('click', () => dlg.close());

  const form          = document.getElementById('frm-recetas');
  const btnSave       = document.getElementById('btn-save');
  const so            = document.getElementById('so_modal');
  const numEt         = document.getElementById('num_etiquetas');
  const grpPac        = document.getElementById('grp_paciente');
  const inpPac        = document.getElementById('paciente');
  const inpMed        = document.getElementById('medico_search');
  const cedulaHidden  = document.getElementById('cedula_medico');
  const selText       = document.getElementById('medico_sel');
  const sugBox        = document.getElementById('medico_sugs');
  const codigoFormula = document.getElementById('codigo_formula');

  so?.addEventListener('input', (e) => {
    e.target.value = (e.target.value || '').replace(/[^0-9]/g, '');
    e.target.setCustomValidity('');
    updateSaveEnabled();
  });

  so?.addEventListener('invalid', function() {
    if (this.validity.patternMismatch) {
      this.setCustomValidity('SO solo acepta dígitos (0-9).');
    }
  });

  function togglePaciente() {
    const n = parseInt(numEt.value || '1', 10);

    if (n > 1) {
      grpPac.style.opacity = '0.5';
      inpPac.value = '';
      inpPac.disabled = true;
      inpPac.placeholder = 'Se generarán nombres aleatorios';
    } else {
      grpPac.style.opacity = '1';
      inpPac.disabled = false;
      inpPac.placeholder = 'Dejar vacío para nombre aleatorio';
    }

    updateSaveEnabled();
  }

  numEt?.addEventListener('input', togglePaciente);
  numEt?.addEventListener('change', togglePaciente);
  togglePaciente();

  let sugPanel = null;

  function clearSugs() {
    if (sugPanel) {
      sugPanel.remove();
      sugPanel = null;
    }
  }

  async function buscarMedicos(q) {
    if (!q || q.length < 2) {
      clearSugs();
      return;
    }

    const url = "{{ route('medicos.buscar') }}" + "?q=" + encodeURIComponent(q);

    let list = [];
    try {
      const resp = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!resp.ok) {
        clearSugs();
        return;
      }

      const data = await resp.json();
      list = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
    } catch (e) {
      clearSugs();
      return;
    }

    clearSugs();

    sugPanel = document.createElement('div');
    sugPanel.className = 'sugs-panel';

    if (!list.length) {
      const empty = document.createElement('div');
      empty.className = 'sugs-item empty';
      empty.textContent = 'Sin resultados';
      sugPanel.appendChild(empty);
      sugBox.appendChild(sugPanel);
      return;
    }

    list.forEach(item => {
      const opt = document.createElement('div');
      opt.className = 'sugs-item';
      opt.textContent = item.label + ' — C.I. ' + item.cedula;

      opt.addEventListener('click', () => {
        inpMed.value = item.label;
        cedulaHidden.value = item.cedula;
        document.getElementById('medico_nombre').value = item.label;
        selText.textContent = 'Seleccionado: ' + item.label + ' (C.I. ' + item.cedula + ')';
        clearSugs();
        updateSaveEnabled();
      });

      sugPanel.appendChild(opt);
    });

    sugBox.appendChild(sugPanel);
  }

  inpMed?.addEventListener('input', (e) => {
    cedulaHidden.value = '';
    selText.textContent = '';
    buscarMedicos(e.target.value.trim());
    updateSaveEnabled();
  });

  document.addEventListener('click', (e) => {
    if (!sugBox.contains(e.target) && e.target !== inpMed) clearSugs();
  });

  function updateSaveEnabled() {
    const soOk  = !!so.value && /^\d+$/.test(so.value);
    const medOk = !!cedulaHidden.value;
    const codOk = !!codigoFormula.value.trim();
    const n     = parseInt(numEt.value || '1', 10);

    btnSave.disabled = !(soOk && medOk && codOk && n >= 1);
  }

  codigoFormula?.addEventListener('input', updateSaveEnabled);
  updateSaveEnabled();

  form?.addEventListener('submit', (e) => {
    const n = parseInt(numEt.value || '1', 10);

    if (!cedulaHidden.value) {
      e.preventDefault();
      alert('Selecciona un médico de la lista.');
      inpMed.focus();
      return;
    }

    if (!codigoFormula.value.trim()) {
      e.preventDefault();
      alert('Ingresa el código de fórmula.');
      codigoFormula.focus();
      return;
    }

    if (n > 1) {
      inpPac.disabled = true;
      inpPac.value = '';
    }
  });
</script>
@endif

@endsection