@php
  $prefill = session('etiqueta_preview', []);
  $soPrefill = $prefill['so'] ?? null;
  $medicoPrefill = $prefill['medico'] ?? null;
  $pacientePrefill = $prefill['paciente'] ?? null;

  function nombreCorto($full) {
      $full = trim((string)$full);
      if ($full === '') return null;
      $p = preg_split('/\s+/', $full);
      $first = $p[0] ?? '';
      $last  = $p[count($p)-1] ?? '';
      return trim($first.' '.$last);
  }
  $medicoCorto = nombreCorto($medicoPrefill);

  function abreviarNombreActivoView($nombre) {
      $nombre = preg_replace('/\s*\(.*?\)\s*/', '', (string)$nombre);
      if (stripos($nombre, 'BIFIDUMBACTERIUM') === 0) {
          return 'BIFIDUM' . substr($nombre, strlen('BIFIDUMBACTERIUM'));
      } elseif (stripos($nombre, 'LACTOBACILUS') === 0) {
          return 'LACTO' . substr($nombre, strlen('LACTOBACILUS'));
      }
      return $nombre;
  }

  // Excluir auxiliares (ajusta a sobres si tu lista difiere)
  $excluir = [70274,70272,70275,70273,1101,1078,1077,1219,70276,70271,71497];
  $items  = $items ?? $formula->items->filter(fn($it) => !in_array((int)$it->cod_odoo, $excluir))->values();

  $totalActivos = $items->count();

  // Columnas (en tu etiqueta vieja eran 2 columnas fijas)
  $columnas = 2;
  if ($totalActivos >= 16 && $totalActivos <= 30) $columnas = 3;

  $porCol = max(1, (int) ceil($totalActivos / $columnas));
  $chunks = $items->chunk($porCol);

  // Para SOBRES: usa tomas_diarias si ya lo tienes igual que cápsulas
  $tomas = (int) ($formula->tomas_diarias ?? 1); // sobres suele ser 1, ajusta si aplica
  $dias  = 30;
  $contiene = $tomas * $dias; // o si tienes dias real en formula, úsalo

  $nombreEtiqueta = (string) ($formula->nombre_etiqueta ?? '');
  $qf = $qf ?? 'Q.F. EVELYN GARCÍA';
  $fechaElaboracion = $fechaElaboracion ?? now()->format('d-m-Y');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Etiqueta – {{ $formula->codigo }}</title>

  <style>
    body { font-family: Arial, sans-serif; margin: 1px; }

    /* === Estilos del primer código (SOBRES) === */
    .container {
      width: 227px;
      height: 1100px;
      display: flex;
      justify-content: center;
      align-items: center;
      transform: rotate(0deg);
      transform-origin: center;
      white-space: nowrap;
      margin-left: 150px;
      margin-top: 100px;
    }

    .etiqueta {
      width: 300px;
      height: 950px;
      writing-mode: vertical-rl;
      text-align: left;
      font-size: 20px;
    }

    .row { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .col { flex: 1; padding: 1px; font-size: 22px; }
    .col-half { width: 50%; }

    .header { text-align: center; margin-bottom: 1px; font-size: 20px; font-weight: bold; }
    .footer { margin-top: 10px; font-size: 22px; }

    .editable { border: 1px; padding: 5px; display: inline-block; }

    /* Opcional: evitar Enter en contenteditable en impresión */
    @media print {
      .editable { border: none !important; }
    }
  </style>
  @php
  $canRecetas = auth()->check() && auth()->user()->hasRole(['Admin','Laboratorio']);
@endphp

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
            <label>Código de fórmula</label>
            <input type="text" name="codigo_formula"
                   class="dlg-recetas__input"
                   value="{{ $formula->codigo }}" readonly>
          </div>

          <div class="form-row">
            <label for="so">SO</label>
            <input type="text" name="so" id="so"
                   class="dlg-recetas__input"
                   inputmode="numeric" pattern="\d+" maxlength="50"
                   placeholder="Solo números" required>
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
                     autocomplete="off">
              <div id="medico_sugs"></div>
            </div>
            <input type="hidden" name="cedula_medico" id="cedula_medico" required>
            <input type="hidden" name="medico_nombre" id="medico_nombre">
            <small id="medico_sel" style="display:block;margin-top:4px;"></small>
          </div>

          <div id="grp_paciente" class="form-row" style="grid-column:1 / -1; max-width:420px;">
            <label for="paciente">Paciente (opcional si solo 1 etiqueta)</label>
            <input type="text" name="paciente" id="paciente"
                   class="dlg-recetas__input"
                   maxlength="50"
                   placeholder="Dejar vacío para nombre aleatorio">
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

  <script>
    // ---------- Apertura del modal ----------
    const mustOpen = {{ session()->has('recetas_guardadas') ? 'false' : 'true' }};
    const dlg = document.getElementById('dlg-recetas');

    if (mustOpen && dlg && !dlg.open) dlg.showModal();

    document.getElementById('btn-cancel')?.addEventListener('click', () => dlg.close());

    // ---------- Referencias ----------
    const form   = document.getElementById('frm-recetas');
    const btnSave= document.getElementById('btn-save');
    const so     = document.getElementById('so');
    const numEt  = document.getElementById('num_etiquetas');
    const grpPac = document.getElementById('grp_paciente');
    const inpPac = document.getElementById('paciente');
    const inpMed = document.getElementById('medico_search');
    const cedulaHidden = document.getElementById('cedula_medico');
    const selText= document.getElementById('medico_sel');
    const sugBox = document.getElementById('medico_sugs');

    // ---------- SO: solo dígitos ----------
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

    // ---------- Mostrar/ocultar paciente ----------
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
    togglePaciente();

    // ---------- Autocompletar de médicos ----------
    let sugPanel = null;
    function clearSugs(){ if (sugPanel){ sugPanel.remove(); sugPanel=null; } }

    async function buscarMedicos(q){
      if (!q || q.length < 2) { clearSugs(); return; }
      const url = "{{ route('medicos.buscar') }}" + "?q=" + encodeURIComponent(q);

      let list = [];
      try {
        const resp = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!resp.ok) { clearSugs(); return; }

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

    inpMed?.addEventListener('input', (e)=> {
      cedulaHidden.value = '';
      selText.textContent = '';
      buscarMedicos(e.target.value.trim());
      updateSaveEnabled();
    });

    document.addEventListener('click', (e)=> {
      if (!sugBox.contains(e.target) && e.target !== inpMed) clearSugs();
    });

    // ---------- Habilitar/Deshabilitar botón Guardar ----------
    function updateSaveEnabled(){
      const soOk = !!so.value && /^\d+$/.test(so.value);
      const medOk = !!cedulaHidden.value;
      const n = parseInt(numEt.value || '1', 10);
      btnSave.disabled = !(soOk && medOk && n >= 1);
    }
    updateSaveEnabled();

    // ---------- Validación final ----------
    form.addEventListener('submit', (e) => {
      if (!cedulaHidden.value) {
        e.preventDefault();
        alert('Selecciona un médico de la lista.');
        inpMed.focus();
      }
    });
  </script>
@endif
<!-- ===== FIN MODAL ===== -->
</head>

<body>
  <div class="container">
    <div class="etiqueta">

      <!-- Título -->
      <div class="header">
        <h2 class="editable" contenteditable="true">
          {{ $nombreEtiqueta }}
        </h2>
      </div>

      <!-- Paciente y código -->
      <div class="row">
        <div class="col">
          <strong class="editable" contenteditable="true">
            PTE: {{ $pacientePrefill ?? ($formula->paciente ?? '-') }}
          </strong>
        </div>
        <div class="col" style="text-align:right;">
          <strong>{{ $formula->codigo }}</strong>
        </div>
      </div>

      <!-- Composición (columnas) -->
      <div class="row">
        @foreach($chunks as $colChunk)
          <div class="col col-half">
            @foreach($colChunk as $it)
              <div class="row">
                <div class="col">
                  <strong>{{ abreviarNombreActivoView($it->activo) }}</strong>
                </div>
                <div class="col" style="text-align:right;">
                  <strong>{{ number_format((float)$it->cantidad, 2) }} {{ $it->unidad ?? 'g' }}</strong>
                </div>
              </div>
            @endforeach
          </div>
        @endforeach
      </div>

      <!-- Footer -->
      <div class="footer">
        <div class="row">
          <div class="col">
            <strong class="editable" contenteditable="true">
              DR.(A): {{ $medicoCorto ?? ($formula->medico ?? '-') }}
            </strong><br>

            <strong>CONTIENE: {{ $contiene }} SOBRES</strong><br>

            <strong>POSOLOGÍA: TOMAR</strong>
            <strong class="editable" contenteditable="true">{{ $tomas }}</strong>
            <strong>SOBRE DIARIO</strong>
          </div>

          <div class="col" style="text-align:left;">
            <strong class="editable" contenteditable="true">{{ $qf }}</strong><br>

            <strong class="editable" contenteditable="true">
              ELAB: {{ $fechaElaboracion }}
            </strong><br>

            <strong>SO.</strong>
            <strong class="editable" contenteditable="true">
              {{ $soPrefill ?? '' }}
            </strong>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    // Evitar saltos de línea en contenteditable
    document.querySelectorAll('.editable').forEach(el => {
      el.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); el.blur(); }
      });
    });

    // Solo números en campos marcados
    document.querySelectorAll('.js-only-numbers').forEach(el => {
      el.addEventListener('input', () => {
        el.textContent = (el.textContent || '').replace(/[^\d]/g, '');
      });
      el.addEventListener('paste', (e) => {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const clean = (text || '').replace(/[^\d]/g, '');
        document.execCommand('insertText', false, clean);
      });
    });
  </script>
</body>
</html>
