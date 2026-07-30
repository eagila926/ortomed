<?php

namespace App\Http\Controllers;

use App\Models\ActivoHomeo;
use App\Models\ActivoHomeoTemp;
use App\Models\FormulaHomeo;
use App\Models\FormulaHomeoItem;
use App\Models\Medico;
use App\Models\Receta;
use App\Models\RecetaHomeopatico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormulaHomeoController extends Controller
{
    private const PRESENTACIONES = [
        'Gotero 22 ml',
        'Gotero 50 ml',
        'Spray 22 ml',
        'Spray 50 ml',
        'Glóbulos',
    ];

    public function index()
    {
        $categorias = ActivoHomeo::query()
            ->whereNotNull('categoria')
            ->where('categoria', '<>', '')
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        $formulaEditando = null;
        $formulaEditandoId = session('formula_homeo_editando');
        if ($formulaEditandoId) {
            $formulaEditando = FormulaHomeo::find($formulaEditandoId);
            if (!$formulaEditando) {
                session()->forget('formula_homeo_editando');
            }
        }

        return view('formulas_homeo.nueva', [
            'categorias' => $categorias,
            'presentaciones' => self::PRESENTACIONES,
            'formulaEditando' => $formulaEditando,
        ]);
    }

    public function buscar(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:150'],
        ]);

        return response()->json(
            ActivoHomeo::query()
                ->where('nombre', 'like', '%'.trim($data['q']).'%')
                ->orderBy('nombre')
                ->limit(15)
                ->get(['id', 'nombre', 'categoria'])
        );
    }

    public function listar()
    {
        return response()->json(
            ActivoHomeoTemp::query()
                ->where('user_id', Auth::id())
                ->with('activoHomeo:id,categoria')
                ->orderBy('id')
                ->get()
        );
    }

    public function agregar(Request $request)
    {
        $data = $request->validate([
            'cod_activo' => ['required', 'integer', 'exists:activos_homeo,id'],
            'dilusion' => ['nullable', 'string', 'max:50'],
        ]);

        $userId = (int) Auth::id();
        $activo = ActivoHomeo::findOrFail($data['cod_activo']);

        $item = ActivoHomeoTemp::updateOrCreate(
            ['user_id' => $userId, 'cod_activo' => $activo->id],
            [
                'codigo' => 'TEMP-HOME-'.$userId,
                'activo' => $activo->nombre,
                'dilusion' => trim((string) ($data['dilusion'] ?? '')),
            ]
        );

        return response()->json([
            'message' => $item->wasRecentlyCreated
                ? 'Activo agregado.'
                : 'Activo actualizado.',
        ]);
    }

    public function eliminar(ActivoHomeoTemp $item)
    {
        abort_unless((int) $item->user_id === (int) Auth::id(), 404);
        $item->delete();

        return response()->json(['message' => 'Activo eliminado.']);
    }

    public function limpiar()
    {
        ActivoHomeoTemp::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Selección eliminada.']);
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'nombre_etiqueta' => ['required', 'string', 'max:150'],
            'categoria' => [
                'required',
                'string',
                'max:100',
                Rule::exists('activos_homeo', 'categoria'),
            ],
            'presentacion' => ['required', Rule::in(self::PRESENTACIONES)],
        ]);

        $userId = (int) Auth::id();
        $data['medico'] = '';
        $data['cedula_medico'] = null;
        $temporales = ActivoHomeoTemp::where('user_id', $userId)
            ->orderBy('id')
            ->get();

        if ($temporales->isEmpty()) {
            return back()
                ->withErrors(['activos' => 'Agrega al menos un activo a la fórmula.'])
                ->withInput();
        }

        $formulaEditandoId = session('formula_homeo_editando');

        $formula = DB::transaction(function () use (
            $data,
            $temporales,
            $userId,
            $formulaEditandoId
        ) {
            if ($formulaEditandoId) {
                $formula = FormulaHomeo::lockForUpdate()->findOrFail($formulaEditandoId);
                $formula->update($data);
                FormulaHomeoItem::where('codigo', $formula->codigo)->delete();
            } else {
                $codigo = $this->generarCodigo();
                $formula = FormulaHomeo::create([
                    ...$data,
                    'codigo' => $codigo,
                    'user_id' => $userId,
                    'precio' => 0,
                ]);
            }

            FormulaHomeoItem::insert(
                $temporales->map(fn ($item) => [
                    'codigo' => $formula->codigo,
                    'cod_activo' => $item->cod_activo,
                    'activo' => $item->activo,
                    'dilusion' => $item->dilusion ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all()
            );

            ActivoHomeoTemp::where('user_id', $userId)->delete();

            return $formula;
        });

        session()->forget('formula_homeo_editando');

        return redirect()
            ->route('formulas-homeo.nueva')
            ->with('ok', "Fórmula {$formula->codigo} guardada correctamente.");
    }

    private function generarCodigo(): string
    {
        $ultimoCodigo = FormulaHomeo::query()
            ->where('codigo', 'like', 'F-HOMEO-%')
            ->orderByDesc('codigo')
            ->lockForUpdate()
            ->value('codigo');

        $numero = $ultimoCodigo
            ? ((int) Str::afterLast($ultimoCodigo, '-') + 1)
            : 1;

        return 'F-HOMEO-'.str_pad((string) $numero, 6, '0', STR_PAD_LEFT);
    }

    public function establecidas(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $formulas = FormulaHomeo::with('items')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('codigo', 'like', "%{$q}%")
                        ->orWhere('nombre_etiqueta', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('formulas_homeo.establecidas', compact('formulas', 'q'));
    }

    public function editar(FormulaHomeo $formula)
    {
        $userId = (int) Auth::id();

        DB::transaction(function () use ($formula, $userId) {
            ActivoHomeoTemp::where('user_id', $userId)->delete();

            foreach ($formula->items()->orderBy('id')->get() as $item) {
                ActivoHomeoTemp::create([
                    'codigo' => 'TEMP-HOME-'.$userId,
                    'user_id' => $userId,
                    'cod_activo' => $item->cod_activo,
                    'activo' => $item->activo,
                    'dilusion' => $item->dilusion ?? '',
                ]);
            }
        });

        session(['formula_homeo_editando' => $formula->id]);

        return redirect()->route('formulas-homeo.nueva');
    }

    public function cancelarEdicion()
    {
        session()->forget('formula_homeo_editando');
        ActivoHomeoTemp::where('user_id', Auth::id())->delete();

        return redirect()->route('formulas-homeo.nueva');
    }

    public function receta(Request $request, FormulaHomeo $formula)
    {
        $data = $request->validate([
            'so' => ['required', 'regex:/^\d+$/', 'max:50'],
            'cedula_medico' => ['required', 'string', 'max:50', 'exists:medicos,cedula'],
            'paciente' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:500'],
        ], [
            'so.regex' => 'El campo SO solo debe contener números.',
        ]);

        $medico = Medico::where('cedula', $data['cedula_medico'])->first();
        if (!$medico) {
            return back()->withErrors([
                'medico' => 'Debe seleccionar un médico válido.',
            ]);
        }
        if (!$medico->firma) {
            return back()->withErrors([
                'medico' => 'El médico seleccionado no tiene una firma registrada.',
            ]);
        }

        $composicion = $formula->items()
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => trim(
                $item->activo.($item->dilusion !== '' ? ' '.$item->dilusion : '')
            ))
            ->implode("\n");

        $cantidad = (int) $data['cantidad'];
        $grupos = [];
        for ($restantes = $cantidad; $restantes > 0; $restantes -= 6) {
            $grupos[] = min(6, $restantes);
        }

        $packs = [];
        $recetaIds = [];
        DB::transaction(function () use (
            $data,
            $formula,
            $composicion,
            $cantidad,
            $grupos,
            $medico,
            &$packs,
            &$recetaIds
        ) {
            foreach ($grupos as $index => $numFrascos) {
                $receta = Receta::create([
                    'so' => $data['so'],
                    'codigo_formula' => $formula->codigo.'-'.($index + 1),
                    'fecha' => now()->toDateString(),
                    'cedula_medico' => $medico->cedula,
                    'paciente' => trim((string) ($data['paciente'] ?? '')),
                    'num_frascos' => $numFrascos,
                ]);

                $homeopatico = RecetaHomeopatico::create([
                    'id_receta' => $receta->id_receta,
                    'producto' => $formula->nombre_etiqueta.' - '.$formula->presentacion,
                    'composicion' => $composicion,
                    'cantidad_solicitada' => $cantidad,
                ]);

                $packs[] = compact('receta', 'homeopatico');
                $recetaIds[] = $receta->id_receta;
            }
        });

        $pdf = Pdf::loadView('recetas.homeopatico_pdf', [
            'packs' => $packs,
            'medico' => $medico,
            'firmaBase64' => $this->firmaBase64($medico),
        ])->setPaper('a4');

        return $pdf
            ->download('Recetas-'.$formula->codigo.'.pdf')
            ->withHeaders($this->publicRecipeLinksHeader($recetaIds));
    }

    private function publicRecipeLinksHeader(array $recetaIds): array
    {
        $links = collect($recetaIds)
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($id) => [
                'id' => (int) $id,
                'url' => route('recetas.public.show', ['receta' => $id]),
            ])
            ->all();

        return [
            'X-Receta-Public-Links' => base64_encode(json_encode($links)),
            'Access-Control-Expose-Headers' => 'X-Receta-Public-Links, Content-Disposition',
        ];
    }

    private function firmaBase64(Medico $medico): ?string
    {
        $publica = 'firmas/'.$medico->cedula.'.png';
        if (Storage::disk('public')->exists($publica)) {
            return base64_encode(Storage::disk('public')->get($publica));
        }

        $imagen = public_path('images/firmas/'.$medico->cedula.'.png');
        return file_exists($imagen) ? base64_encode(file_get_contents($imagen)) : null;
    }
}
