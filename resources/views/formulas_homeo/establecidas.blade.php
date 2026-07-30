@extends('layouts.app')

@section('title', 'Fórmulas establecidas homeopáticas | Ortomed')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h2 class="h4 mb-1">Fórmulas establecidas homeopáticas</h2>
    <p class="text-muted mb-0">Consulta, edita y genera recetas de las fórmulas guardadas.</p>
  </div>
  <a href="{{ route('formulas-homeo.nueva') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> Nueva fórmula
  </a>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
  </div>
@endif

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('formulas-homeo.establecidas') }}" class="row g-2">
      <div class="col-md-9">
        <input type="search" class="form-control" name="q" value="{{ $q }}"
               placeholder="Buscar por código o nombre de la fórmula">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary flex-grow-1">Buscar</button>
        <a href="{{ route('formulas-homeo.establecidas') }}" class="btn btn-outline-secondary">Limpiar</a>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Código / Fórmula</th>
          <th>Composición</th>
          <th>Presentación</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($formulas as $formula)
          <tr>
            <td>
              <div class="fw-semibold">{{ $formula->codigo }}</div>
              <small class="text-muted">{{ $formula->nombre_etiqueta }} · {{ $formula->categoria }}</small>
            </td>
            <td>
              @foreach($formula->items as $item)
                <div>
                  {{ $item->activo }}
                  @if($item->dilusion !== '')<span class="text-muted">{{ $item->dilusion }}</span>@endif
                </div>
              @endforeach
            </td>
            <td><span class="badge text-bg-info">{{ $formula->presentacion }}</span></td>
            <td class="text-end text-nowrap">
              <a href="{{ route('formulas-homeo.editar', $formula) }}"
                 class="btn btn-sm btn-primary" title="Editar fórmula">
                <i class="bi bi-pencil-square"></i> Editar
              </a>
              <button type="button" class="btn btn-sm btn-outline-danger btn-receta-homeo"
                      data-id="{{ $formula->id }}"
                      data-codigo="{{ $formula->codigo }}"
                      data-nombre="{{ $formula->nombre_etiqueta }}"
                      title="Generar receta">
                <i class="bi bi-file-earmark-pdf"></i> Receta
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-5">No hay fórmulas homeopáticas guardadas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($formulas->hasPages())
    <div class="card-body border-top">{{ $formulas->links() }}</div>
  @endif
</div>

<div class="modal fade" id="modalRecetaHomeo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formRecetaHomeo" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Generar receta homeopática</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Fórmula</label>
            <input type="text" id="recetaFormula" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label for="buscarMedicoReceta" class="form-label">Seleccionar médico</label>
            <div class="position-relative">
              <input type="search" id="buscarMedicoReceta" class="form-control"
                     placeholder="Buscar por nombre o cédula" autocomplete="off">
              <input type="hidden" id="cedulaMedicoReceta" name="cedula_medico" required>
              <div id="resultadosMedicosReceta"
                   class="list-group position-absolute w-100 shadow d-none"
                   style="z-index:1060; max-height:230px; overflow-y:auto;"></div>
            </div>
            <div class="form-text" id="estadoMedicoReceta">
              Selecciona un médico con firma registrada.
            </div>
          </div>
          <div class="mb-3">
            <label for="recetaSo" class="form-label">SO</label>
            <input type="text" id="recetaSo" name="so" class="form-control"
                   inputmode="numeric" pattern="\d+" required>
          </div>
          <div class="mb-3">
            <label for="recetaPaciente" class="form-label">Paciente (opcional)</label>
            <input type="text" id="recetaPaciente" name="paciente" class="form-control" maxlength="255">
          </div>
          <div class="mb-3">
            <label for="recetaCantidad" class="form-label">Cantidad de frascos</label>
            <input type="number" id="recetaCantidad" name="cantidad" class="form-control"
                   min="1" max="500" value="1" required>
            <div class="form-text" id="resumenDistribucion">1 receta de 1 frasco.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger" id="generarRecetaHomeo" disabled>
            <i class="bi bi-file-earmark-pdf me-1"></i> Generar PDF
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = new bootstrap.Modal(document.getElementById('modalRecetaHomeo'));
  const form = document.getElementById('formRecetaHomeo');
  const cantidad = document.getElementById('recetaCantidad');
  const resumen = document.getElementById('resumenDistribucion');
  const baseUrl = @json(url('/formulas-homeopaticas'));
  const medicosUrl = @json(route('medicos.buscar'));
  const buscarMedico = document.getElementById('buscarMedicoReceta');
  const cedulaMedico = document.getElementById('cedulaMedicoReceta');
  const resultadosMedicos = document.getElementById('resultadosMedicosReceta');
  const estadoMedico = document.getElementById('estadoMedicoReceta');
  const generar = document.getElementById('generarRecetaHomeo');
  let temporizadorMedico;

  function escapar(valor) {
    const div = document.createElement('div');
    div.textContent = valor ?? '';
    return div.innerHTML;
  }

  function actualizarResumen() {
    let restantes = Math.max(0, parseInt(cantidad.value, 10) || 0);
    const grupos = [];
    while (restantes > 0) {
      grupos.push(Math.min(6, restantes));
      restantes -= 6;
    }
    resumen.textContent = grupos.length
      ? `${grupos.length} receta(s): ${grupos.join(' + ')} frasco(s).`
      : '';
  }

  document.querySelectorAll('.btn-receta-homeo').forEach(boton => {
    boton.addEventListener('click', () => {
      form.reset();
      form.action = `${baseUrl}/${boton.dataset.id}/receta`;
      document.getElementById('recetaFormula').value =
        `${boton.dataset.codigo} · ${boton.dataset.nombre}`;
      buscarMedico.value = '';
      cedulaMedico.value = '';
      resultadosMedicos.classList.add('d-none');
      estadoMedico.className = 'form-text';
      estadoMedico.textContent = 'Selecciona un médico con firma registrada.';
      generar.disabled = true;
      cantidad.value = 1;
      actualizarResumen();
      modal.show();
    });
  });

  cantidad.addEventListener('input', actualizarResumen);

  form.addEventListener('submit', event => {
    event.preventDefault();
    if (!form.reportValidity() || !cedulaMedico.value) return;

    generar.disabled = true;
    modal.hide();

    window.submitPdfFormWithPublicLinks(form, {
      defaultFilename: 'recetas-homeopaticas.pdf',
      errorMessage: 'No se pudieron generar las recetas.',
    }).then(() => {
      form.reset();
      cedulaMedico.value = '';
      generar.disabled = true;
    }).catch(error => {
      alert(error.message || 'No se pudieron generar las recetas.');
      generar.disabled = false;
      modal.show();
    });
  });

  buscarMedico.addEventListener('input', () => {
    cedulaMedico.value = '';
    generar.disabled = true;
    estadoMedico.className = 'form-text';
    estadoMedico.textContent = 'Selecciona un médico con firma registrada.';
    clearTimeout(temporizadorMedico);
    const q = buscarMedico.value.trim();
    if (!q) {
      resultadosMedicos.classList.add('d-none');
      return;
    }

    temporizadorMedico = setTimeout(() => {
      fetch(`${medicosUrl}?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json' },
      })
        .then(response => response.json())
        .then(medicos => {
          resultadosMedicos.innerHTML = medicos.length
            ? medicos.map(medico => `
                <button type="button" class="list-group-item list-group-item-action"
                        data-cedula="${escapar(medico.cedula)}"
                        data-nombre="${escapar(medico.label)}"
                        data-firma="${medico.firma ? '1' : '0'}">
                  <div class="d-flex justify-content-between gap-2">
                    <span>
                      <strong>${escapar(medico.label)}</strong>
                      <small class="d-block text-muted">Cédula: ${escapar(medico.cedula)}</small>
                    </span>
                    <span class="badge ${medico.firma ? 'text-bg-success' : 'text-bg-danger'} align-self-center">
                      ${medico.firma ? 'Con firma' : 'Sin firma'}
                    </span>
                  </div>
                </button>`).join('')
            : '<div class="list-group-item text-muted">No se encontraron médicos.</div>';
          resultadosMedicos.classList.remove('d-none');
        });
    }, 250);
  });

  resultadosMedicos.addEventListener('click', event => {
    const opcion = event.target.closest('[data-cedula]');
    if (!opcion) return;
    buscarMedico.value = opcion.dataset.nombre;
    resultadosMedicos.classList.add('d-none');

    if (opcion.dataset.firma !== '1') {
      cedulaMedico.value = '';
      generar.disabled = true;
      estadoMedico.className = 'form-text text-danger';
      estadoMedico.textContent = 'Este médico no tiene firma registrada. Selecciona otro médico.';
      return;
    }

    cedulaMedico.value = opcion.dataset.cedula;
    generar.disabled = false;
    estadoMedico.className = 'form-text text-success';
    estadoMedico.textContent = `Médico válido · Cédula: ${opcion.dataset.cedula}`;
  });

  document.addEventListener('click', event => {
    if (!event.target.closest('#buscarMedicoReceta') &&
        !event.target.closest('#resultadosMedicosReceta')) {
      resultadosMedicos.classList.add('d-none');
    }
  });

  actualizarResumen();
});
</script>
@endpush
