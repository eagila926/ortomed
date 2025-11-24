<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB; 
use App\Mail\RecetaCreadaMail;
use App\Models\Receta;
use App\Models\Formula;
use App\Models\FormulaItem;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\RecetasLoteMail;
use Illuminate\Support\Facades\Storage;

class RecetaController extends Controller
{
    public function index(Request $request)
    {
        // filtros opcionales
        $q      = trim((string) $request->query('q', ''));
        $desde  = $request->query('desde');
        $hasta  = $request->query('hasta');

        $recetas = Receta::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('so', 'like', "%{$q}%")
                    ->orWhere('codigo_formula', 'like', "%{$q}%")
                    ->orWhere('cedula_medico', 'like', "%{$q}%")
                    ->orWhere('paciente', 'like', "%{$q}%");
                });
            })
            ->when($desde, fn($qq) => $qq->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($qq) => $qq->whereDate('fecha', '<=', $hasta))
            ->orderByDesc('id_receta')
            ->paginate(20)
            ->withQueryString();

        return view('recetas.index', compact('recetas','q','desde','hasta'));
    }

    public function show(Receta $receta)
    {
        // 1) Traer fórmula por código + sus ítems
        $formula = Formula::with('items')   // relación hasMany('items') en Formula
            ->where('codigo', $receta->codigo_formula)
            ->firstOrFail();

        // 2) Excluir auxiliares (mismo filtro que usas en la etiqueta)
        $excluir = [70274,70272,70275,70273,1101,1078,1077,1219,70276,70271,71497];
        $items = $formula->items->filter(fn($it) => !in_array((int)$it->cod_odoo, $excluir))->values();

        // 3) Médico por cédula
        $medico = Medico::where('cedula', $receta->cedula_medico)->first();

        // 4) Firma (elige una de estas dos estrategias)
        // 4.a) Si tu tabla medicos tiene columna firma_path (p. ej. 'public/firmas/1712.png')
        $firmaUrl = null;
        if ($medico) {
            // si guardas la ruta relativa en la BD, p.ej. 'firmas/123.png'
            if ($medico->firma_path && Storage::disk('public')->exists($medico->firma_path)) {
                $firmaUrl = Storage::url($medico->firma_path); // /storage/firmas/123.png
            } else {
                $path = 'firmas/'.$medico->cedula.'.png';
                if (Storage::disk('public')->exists($path)) {
                    $firmaUrl = Storage::url($path);
                }
            }
        }

        return view('recetas.recetario', [
            'receta'   => $receta,
            'formula'  => $formula,
            'items'    => $items,
            'medico'   => $medico,
            'firmaUrl' => $firmaUrl,
        ]);
    }
    public function storeMultiple(Request $request)
    {
        $data = $request->validate([
            'codigo_formula'   => ['required','string','max:50'],
            'so'               => ['required','regex:/^\d+$/','max:50'],
            'fecha'            => ['nullable','date'],
            'cedula_medico'    => ['required','string','max:50'],
            'medico_nombre'    => ['nullable','string','max:120'],
            'paciente'         => ['nullable','string','max:50'],
            'num_etiquetas'    => ['required','integer','min:1','max:200'],
            'redirect_to'      => ['nullable','string'],
        ],[
            'so.regex' => 'El campo SO solo debe contener números.',
        ]);

        $n       = (int) $data['num_etiquetas'];
        $fecha   = $data['fecha'] ?? now()->toDateString();
        $so      = $data['so'];
        $cod     = $data['codigo_formula'];
        $cedula  = $data['cedula_medico'];
        $medicoNombre = trim((string)($data['medico_nombre'] ?? ''));

        $groupSize   = 6;               // 6 etiquetas = 1 receta
        $numRecetas  = (int) ceil($n / $groupSize);

        $pacienteParaEtiqueta = null;
        $creadasIds = [];

        // 1) Crear recetas y recoger IDs (si algo falla, no enviamos correos)
        DB::transaction(function () use (
            $n,$fecha,$so,$cod,$cedula,$numRecetas,&$creadasIds,$request,&$pacienteParaEtiqueta,$groupSize
        ) {
            // Cantidad total de etiquetas/frascos que hay que repartir
            $restantes = $n;

            if ($n === 1) {
                $pacienteInput = trim((string)$request->input('paciente',''));
                if ($pacienteInput === '') {
                    $nombreParaBD = $this->randomName();
                    $pacienteParaEtiqueta = null;
                } else {
                    $nombreParaBD = $pacienteInput;
                    $pacienteParaEtiqueta = $pacienteInput;
                }

                // Con 1 etiqueta → 1 frasco
                $r = Receta::create([
                    'so'              => $so,
                    'codigo_formula'  => $cod,
                    'fecha'           => $fecha,
                    'cedula_medico'   => $cedula,
                    'paciente'        => $nombreParaBD,
                    'num_frascos'     => 1,
                ]);
                $creadasIds[] = $r->getKey();

            } else {

                for ($i = 0; $i < $numRecetas; $i++) {

                    // ¿Cuántos frascos/etiquetas va a llevar ESTA receta?
                    // Ej.: n=17, groupSize=6
                    // i=0 → min(6,17)=6
                    // i=1 → min(6,11)=6
                    // i=2 → min(6,5)=5
                    $frascos = min($groupSize, $restantes);

                    $r = Receta::create([
                        'so'              => $so,
                        'codigo_formula'  => $cod,
                        'fecha'           => $fecha,
                        'cedula_medico'   => $cedula,
                        'paciente'        => $this->randomName(),
                        'num_frascos'     => $frascos,
                    ]);
                    $creadasIds[] = $r->getKey();

                    // Restar lo que ya se asignó
                    $restantes -= $frascos;
                }

                $pacienteParaEtiqueta = null;
            }
        });


        // 2) Enviar correos (fuera de la transacción)
        // 2) Enviar correos (una vez por lote)
        $internos = array_filter(array_map('trim', explode(',', env('INTERNAL_RECETAS_EMAIL',''))));

        // tomar el médico de la primera receta (todas comparten el mismo en este flujo)
        $primera = \App\Models\Receta::find($creadasIds[0]);
        $medico = \App\Models\Medico::where('cedula', $primera->cedula_medico)->first();
        $doctorEmail = trim((string)($medico->email ?? $medico->correo ?? ''));

        $mailable = new RecetasLoteMail($creadasIds);

        if ($doctorEmail !== '') {
            $mail = \Mail::to($doctorEmail);
            if ($internos) $mail->cc($internos);
            $mail->send($mailable);
        } elseif ($internos) {
            \Mail::to($internos[0])->cc(array_slice($internos,1))->send($mailable);
        }

        // 3) Redirigir (AHORA sí)
        $back = $data['redirect_to'] ?? url()->previous();
        return redirect($back)->with([
            'recetas_guardadas' => $numRecetas,
            'etiqueta_preview'  => [
                'so'      => $so,
                'medico'  => $medicoNombre,
                'paciente'=> $pacienteParaEtiqueta
            ],
        ]);
    }

    private function enviarCorreoReceta(int $recetaId, array $internos): void
    {
        $receta = Receta::findOrFail($recetaId);
        $medico = Medico::where('cedula', $receta->cedula_medico)->first();
        $doctorEmail = trim((string)($medico->email ?? $medico->correo ?? ''));

        $mailable = new RecetaCreadaMail($recetaId);

        if ($doctorEmail !== '') {
            $mail = Mail::to($doctorEmail);
            if ($internos) $mail->cc($internos);
            $mail->send($mailable);  // en local (sandbox) síncrono
        } elseif ($internos) {
            Mail::to($internos[0])
                ->cc(array_slice($internos,1))
                ->send($mailable);
        }
    }

    private function randomName(): string
    {
        $nombres = [
            'Juan','María','Pedro','Luisa','Carlos','Ana','Jorge','Sofía','Diego','Daniela',
            'Andrés','Valeria','Miguel','Camila','Felipe','Fernanda','Pablo','Paola','Ricardo','Andrea','Elvis',
            'José','Laura','Marco','Patricia','Héctor','Alejandra','Iván','Verónica','Francisco','Natalia',
            'Roberto','Lorena','Sebastián','Carolina','Tomás','Rocío','Ángel','Isabel','Mauricio','Renata',
            'Mario','Bianca','Raúl','Julieta','Oscar','Elena','Samuel','Noelia','Adrián','Fabiola',
            'Hugo','Cecilia','Abel','Esperanza','Leandro','Marisol','Cristian','Dayana','Gustavo','Mayra',
            'Emanuel','Gloria','Sergio','Gabriela','Kevin','Mónica','Luis','Silvia','Saúl','Miranda',
            'Emilio','Claudia','Benjamín','Miriam','Alex','Marina','Tadeo','Inés','Bruno','Leticia',
            'Mateo','Alicia','Rafael','Pilar','Iker','Paloma','Alan','Aurora','Julián','Teresa',
            'Gael','Fátima','Hernán','Amalia','Matías','Agustina','Thiago','Josefina','Damián','Beatriz',
            'Cristóbal','Emma','Ethan','Lucía','Israel','Romina','Mauro','Jacinta','Esteban','Viviana',
            'Félix','Salomé','Bastián','Tamara','Joel','Regina','Erick','Sabrina','Álvaro','Adela',
            'Ramiro','Araceli','Guillermo','Candela','Nelson','Elisa','Fabián','Ivanna','Darío','Clara',
            'Rubén','Daniela','Eduardo','Lourdes','Israel','Yuliana','Ariel','Samanta','Ignacio','Javiera'
        ];
        $apellidos = [
            'García','Rodríguez','Martínez','López','González','Pérez','Sánchez','Ramírez','Torres','Flores',
            'Vargas','Castro','Rojas','Moreno','Guerrero','Mendoza','Ortega','Navarro','Espinoza','Cruz',
            'Reyes','Guzmán','Salazar','Aguilar','Medina','Vera','Romero','Herrera','Muñoz','Correa',
            'Fuentes','Suárez','Peña','Cordero','Bravo','Arroyo','Silva','Mejía','Montenegro','Paredes',
            'Zamora','Ibarra','Padilla','Valencia','Camacho','Acosta','Bustamante','Velásquez','Ojeda','Rivas',
            'Flores','Soto','Cedeño','Moreira','Benítez','Granda','Segura','Merino','Montero','Sandoval',
            'Calderón','Villalba','Rosales','Bustos','Chávez','Riquelme','Bravo','Yáñez','Orozco','Salinas',
            'Carrillo','Zúñiga','Torrealba','Maldonado','Domínguez','Barrios','Luna','Barba','Arellano','Patiño',
            'Altamirano','Jaramillo','Báez','Ontaneda','Vinueza','Armas','Rosero','Proaño','Tapia','Chacón',
            'Cáceres','Núñez','Santana','Pozo','Lozano','Delgado','Peralta','Serrano','Cárdenas','Coronel',
            'Ulloa','Solano','Fierro','Zurita','Viteri','Soria','Vivanco','Toledo','Gálvez','Guajardo',
            'Molina','Mojica','Gallo','Reátegui','Coloma','Pérez','Avilés','Zambrano','Farfán','Arce'
        ];

        return $nombres[random_int(0, count($nombres)-1)] . ' ' .
               $apellidos[random_int(0, count($apellidos)-1)];
    }

}
