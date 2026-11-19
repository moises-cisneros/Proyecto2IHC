# Diff Coverage
## Diff: 731fb8d...HEAD, staged and unstaged changes

- app/dtos/comprobantescanresult&#46;php (45.5%): Missing lines 23,39,50,52-60
- app/http/controllers/autentificacioncontroller&#46;php (78.1%): Missing lines 149,152-154,159-160,162
- app/http/controllers/colaboracioncontroller&#46;php (99.4%): Missing lines 40
- app/http/controllers/cuentacontroller&#46;php (100%)
- app/http/controllers/eventoscontroller&#46;php (100%)
- app/http/controllers/gastoscontroller&#46;php (91.6%): Missing lines 46,57-64,102,168,205
- app/http/controllers/historialcontroller&#46;php (100%)
- app/http/controllers/perfilcobrocontroller&#46;php (93.8%): Missing lines 32
- app/http/controllers/planescontroller&#46;php (84.9%): Missing lines 86-90,92-98,103-108,195-198,202
- app/http/requests/confirmarplanrequest&#46;php (73.3%): Missing lines 31,57,61,67,71,77-83
- app/http/requests/rechazarpagorequest&#46;php (100%)
- app/http/requests/retirarseplanrequest&#46;php (94.7%): Missing lines 40
- app/http/requests/storeeventorequest&#46;php (100%)
- app/http/requests/storegastorequest&#46;php (70.9%): Missing lines 47,51,69-70,76-77,79-82,84,86-90
- app/http/requests/storepagorequest&#46;php (92.0%): Missing lines 42,46,53,66
- app/http/requests/storeplanrequest&#46;php (100%)
- app/http/requests/updatedatoscobrorequest&#46;php (100%)
- app/http/requests/updateplanrequest&#46;php (100%)
- app/mail/transport/cloudflaremailtransport&#46;php (0.0%): Missing lines 12,16,19,21,23-26,29-30,33-41,43-45,49,51
- app/models/evento&#46;php (100%)
- app/models/gasto&#46;php (60.0%): Missing lines 56,58-60
- app/models/gastodivision&#46;php (50.0%): Missing lines 28,30
- app/models/invitacion&#46;php (100%)
- app/models/opcion&#46;php (100%)
- app/models/pago&#46;php (81.8%): Missing lines 66,68,122,124
- app/models/plan&#46;php (98.3%): Missing lines 165,204,209-210
- app/models/planactivity&#46;php (100%)
- app/models/user&#46;php (93.9%): Missing lines 72,74,105,107
- app/models/voto&#46;php (100%)
- app/policies/gastopolicy&#46;php (70.0%): Missing lines 14,16,25
- app/policies/pagopolicy&#46;php (93.8%): Missing lines 17
- app/policies/planpolicy&#46;php (98.2%): Missing lines 36
- app/providers/appserviceprovider&#46;php (64.3%): Missing lines 37,45-48
- app/services/comprobante/geminicomprobantescanner&#46;php (91.5%): Missing lines 114-115,117-121
- app/services/userdataexportservice&#46;php (79.9%): Missing lines 162-168,196-203,206-213,216-223,226-233

## Summary

- **Total**: 1836 lines
- **Missing**: 187 lines
- **Coverage**: 89%



## app/dtos/comprobantescanresult&#46;php

```
  19      */
  20     public function coincideConMonto(float $montoEsperado, float $tolerancia = 0.05): bool
  21     {
  22         if (! $this->esComprobante || $this->monto === null) {
! 23             return false;
  24         }
  25 
  26         return abs($this->monto - $montoEsperado) <= $tolerancia;
  27     }
```


---


```
  35             return 'invalido';
  36         }
  37 
  38         if ($this->monto === null) {
! 39             return 'invalido';
  40         }
  41 
  42         return $this->coincideConMonto($montoEsperado) ? 'verificado' : 'discrepante';
  43     }
```


---


```
  46      * Convierte el DTO a un array serializable.
  47      *
  48      * @return array<string, mixed>
  49      */
! 50     public function toArray(): array
  51     {
! 52         return [
! 53             'es_comprobante' => $this->esComprobante,
! 54             'monto' => $this->monto,
! 55             'banco' => $this->banco,
! 56             'referencia' => $this->referencia,
! 57             'confianza' => $this->confianza,
! 58             'mensaje_error' => $this->mensajeError,
! 59             'raw_payload' => $this->rawPayload,
! 60         ];
  61     }
  62 }
```


---



## app/http/controllers/autentificacioncontroller&#46;php

```
  145 
  146     public function restablecer(Request $request)
  147     {
  148         $request->validate([
! 149             'correo' => 'required|email',
  150             'contrasena' => 'required|min:8|confirmed',
  151         ], [
! 152             'correo.required' => 'Escribe tu correo.',
! 153             'contrasena.required' => 'Escribe la nueva contraseña.',
! 154             'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
  155             'contrasena.confirmed' => 'Las contraseñas no coinciden.',
  156         ]);
  157 
  158         $datos = [
! 159             'email' => $request->correo,
! 160             'password' => $request->contrasena,
  161             'password_confirmation' => $request->contrasena_confirmation,
! 162             'token' => $request->token,
  163         ];
  164 
  165         // Laravel revisa que el token sea válido y luego ejecuta esta función
  166         $estado = Password::reset($datos, function ($usuario, $contrasena) {
```


---



## app/http/controllers/colaboracioncontroller&#46;php

```
  36         ]);
  37 
  38         $pestanaActiva = $request->input('tab', 'fechas');
  39         if (! in_array($pestanaActiva, ['fechas', 'lugares', 'gastos'])) {
! 40             $pestanaActiva = 'fechas';
  41         }
  42 
  43         $fechas = $plan->opciones->where('tipo', 'fecha')->values();
  44         $lugares = $plan->opciones->where('tipo', 'lugar')->values();
```


---



## app/http/controllers/gastoscontroller&#46;php

```
  42             foreach ($participantesIds as $index => $uid) {
  43                 // Asignar centavos restantes al primer participante para cuadrar la suma exacta
  44                 $montoAsignado = $cuotaBase;
  45                 if ($index === 0 && $diferencia > 0) {
! 46                     $montoAsignado = round($montoAsignado + $diferencia, 2);
  47                 }
  48 
  49                 GastoDivision::create([
  50                     'gasto_id' => $gasto->id,
```


---


```
  53                 ]);
  54             }
  55         } else {
  56             // Personalizada
! 57             $divisiones = $request->input('divisiones', []);
! 58             foreach ($participantesIds as $uid) {
! 59                 $monto = isset($divisiones[$uid]) ? round((float) $divisiones[$uid], 2) : 0.0;
! 60                 GastoDivision::create([
! 61                     'gasto_id' => $gasto->id,
! 62                     'user_id' => $uid,
! 63                     'monto_asignado' => $monto,
! 64                 ]);
  65             }
  66         }
  67 
  68         // Trazabilidad en historial de actividades
```


---


```
   98             return (int) $d['deudor_id'] === (int) auth()->id() && (int) $d['acreedor_id'] === (int) $request->receptor_id;
   99         });
  100 
  101         if ($deuda && $montoFloat > ((float) $deuda['monto'] + 0.009)) {
! 102             return back()->withErrors(['monto' => "El monto ingresado (Bs {$montoFloat}) excede la deuda exigible de Bs {$deuda['monto']}."]);
  103         }
  104 
  105         $datosPago = [
  106             'deudor_id' => auth()->id(),
```


---


```
  164     {
  165         Gate::authorize('confirmar', $pago);
  166 
  167         if ($pago->estado === 'confirmado') {
! 168             return back()->with('error', 'El pago ya se encuentra confirmado.');
  169         }
  170 
  171         $pago->update([
  172             'estado' => 'confirmado',
```


---


```
  201     {
  202         Gate::authorize('rechazar', $pago);
  203 
  204         if ($pago->estado !== 'pendiente') {
! 205             return back()->with('error', 'Solo se pueden rechazar pagos que se encuentren en estado pendiente.');
  206         }
  207 
  208         $pago->update([
  209             'estado' => 'rechazado',
```


---



## app/http/controllers/perfilcobrocontroller&#46;php

```
  28             }
  29             $datos['qr_pago_path'] = null;
  30         } elseif ($request->hasFile('qr_pago')) {
  31             if ($usuario->qr_pago_path && Storage::disk('public')->exists($usuario->qr_pago_path)) {
! 32                 Storage::disk('public')->delete($usuario->qr_pago_path);
  33             }
  34             $datos['qr_pago_path'] = $request->file('qr_pago')->store('qrs', 'public');
  35         }
```


---



## app/http/controllers/planescontroller&#46;php

```
   82         $totalHoy = $hoy->count();
   83         $totalProximos = $proximos->count();
   84 
   85         if ($buscar !== '') {
!  86             Evento::registrar('busqueda_realizada', [
!  87                 'termino' => $buscar,
!  88                 'seccion' => $seccion,
!  89                 'total_resultados' => $planes->count(),
!  90             ]);
   91 
!  92             if ($planes->isEmpty()) {
!  93                 Evento::registrar('busqueda_sin_resultados', [
!  94                     'termino' => $buscar,
!  95                     'seccion' => $seccion,
!  96                     'estado' => $estado,
!  97                     'fecha' => $fecha,
!  98                 ]);
   99             }
  100         }
  101 
  102         if ($estado !== '' || $fecha !== '') {
! 103             Evento::registrar('filtro_aplicado', [
! 104                 'estado' => $estado,
! 105                 'fecha' => $fecha,
! 106                 'seccion' => $seccion,
! 107                 'total_resultados' => $planes->count(),
! 108             ]);
  109         }
  110 
  111         return view('planes.listar', compact(
  112             'planes',
```


---


```
  191                 'motivos' => array_keys($bloqueos),
  192             ]);
  193 
  194             if ($request->expectsJson()) {
! 195                 return response()->json([
! 196                     'message' => 'No se puede eliminar el plan debido a impedimentos activos.',
! 197                     'bloqueos' => $bloqueos,
! 198                 ], 422);
  199             }
  200 
  201             $mensaje = isset($bloqueos['plan_confirmado'])
! 202                 ? 'Un plan confirmado no puede eliminarse. Primero cancélalo.'
  203                 : 'El plan tiene gastos con deudas pendientes entre participantes que deben liquidarse antes de eliminarlo.';
  204 
  205             return redirect()->back()
  206                 ->with('error', $mensaje)
```


---



## app/http/requests/confirmarplanrequest&#46;php

```
  27     {
  28         /** @var Plan|null $plan */
  29         $plan = $this->route('plan');
  30         if (is_numeric($plan)) {
! 31             $plan = Plan::find($plan);
  32         }
  33 
  34         $tieneEmpate = $plan && ($plan->tieneEmpateFechas() || $plan->tieneEmpateLugares());
  35         // Sin propuestas de fecha en la sala, el plan conserva su fecha límite (aviso del modal de confirmación).
```


---


```
  53         $validator->after(function ($validator) {
  54             /** @var Plan|null $plan */
  55             $plan = $this->route('plan');
  56             if (is_numeric($plan)) {
! 57                 $plan = Plan::find($plan);
  58             }
  59 
  60             if (! $plan) {
! 61                 return;
  62             }
  63 
  64             if ($this->filled('propuesta_fecha_id')) {
  65                 $opcionFecha = Opcion::find($this->propuesta_fecha_id);
```


---


```
  63 
  64             if ($this->filled('propuesta_fecha_id')) {
  65                 $opcionFecha = Opcion::find($this->propuesta_fecha_id);
  66                 if (! $opcionFecha || ! $plan->validarPropuestaPertenece($opcionFecha, 'fecha')) {
! 67                     $validator->errors()->add('propuesta_fecha_id', 'La fecha seleccionada no pertenece a este plan.');
  68                 } elseif ($plan->tieneEmpateFechas()) {
  69                     $empatadas = $plan->obtenerPropuestasEmpatadas('fecha')->pluck('id');
  70                     if (! $empatadas->contains($opcionFecha->id)) {
! 71                         $validator->errors()->add('propuesta_fecha_id', 'Debes seleccionar una de las fechas empatadas con mayor votación.');
  72                     }
  73                 }
  74             }
```


---


```
  73                 }
  74             }
  75 
  76             if ($this->filled('propuesta_lugar_id')) {
! 77                 $opcionLugar = Opcion::find($this->propuesta_lugar_id);
! 78                 if (! $opcionLugar || ! $plan->validarPropuestaPertenece($opcionLugar, 'lugar')) {
! 79                     $validator->errors()->add('propuesta_lugar_id', 'El lugar seleccionado no pertenece a este plan.');
! 80                 } elseif ($plan->tieneEmpateLugares()) {
! 81                     $empatadas = $plan->obtenerPropuestasEmpatadas('lugar')->pluck('id');
! 82                     if (! $empatadas->contains($opcionLugar->id)) {
! 83                         $validator->errors()->add('propuesta_lugar_id', 'Debes seleccionar uno de los lugares empatados con mayor votación.');
  84                     }
  85                 }
  86             }
  87         });
```


---



## app/http/requests/retirarseplanrequest&#46;php

```
  36         $validator->after(function ($validator) {
  37             /** @var Plan|null $plan */
  38             $plan = $this->route('plan');
  39             if (is_numeric($plan)) {
! 40                 $plan = Plan::find($plan);
  41             }
  42 
  43             if ($plan && $plan->estado !== 'pendiente') {
  44                 $validator->errors()->add('plan', 'Solo puedes retirarte de un plan que se encuentre en estado pendiente.');
```


---



## app/http/requests/storegastorequest&#46;php

```
  43         $validator->after(function ($validator) {
  44             /** @var Plan|null $plan */
  45             $plan = $this->route('plan');
  46             if (is_numeric($plan)) {
! 47                 $plan = Plan::find($plan);
  48             }
  49 
  50             if (! $plan) {
! 51                 return;
  52             }
  53 
  54             // Obtener IDs de usuarios válidos del plan (creador + participantes aceptados)
  55             $participantesValidos = collect([$plan->user_id])
```


---


```
  65 
  66             // Validar anti-IDOR: cada participante debe pertenecer al plan
  67             foreach ($participantesSeleccionados as $uid) {
  68                 if (! $participantesValidos->contains($uid)) {
! 69                     $validator->errors()->add('participantes', 'Uno o más participantes seleccionados no pertenecen a este plan.');
! 70                     break;
  71                 }
  72             }
  73 
  74             // Validación de división personalizada
```


---


```
  72             }
  73 
  74             // Validación de división personalizada
  75             if ($this->input('tipo_division') === 'personalizada') {
! 76                 $montoTotal = round((float) $this->input('monto_total', 0), 2);
! 77                 $divisiones = $this->input('divisiones', []);
  78 
! 79                 $sumaDivisiones = 0.0;
! 80                 foreach ($participantesSeleccionados as $uid) {
! 81                     $montoUser = isset($divisiones[$uid]) ? round((float) $divisiones[$uid], 2) : 0.0;
! 82                     $sumaDivisiones += $montoUser;
  83                 }
! 84                 $sumaDivisiones = round($sumaDivisiones, 2);
  85 
! 86                 if (abs($sumaDivisiones - $montoTotal) > 0.01) {
! 87                     $validator->errors()->add(
! 88                         'divisiones',
! 89                         "La suma de los montos asignados (Bs {$sumaDivisiones}) debe coincidir exactamente con el monto total (Bs {$montoTotal})."
! 90                     );
  91                 }
  92             }
  93         });
  94     }
```


---



## app/http/requests/storepagorequest&#46;php

```
  38         $validator->after(function ($validator) {
  39             /** @var Plan|null $plan */
  40             $plan = $this->route('plan');
  41             if (is_numeric($plan)) {
! 42                 $plan = Plan::find($plan);
  43             }
  44 
  45             if (! $plan) {
! 46                 return;
  47             }
  48 
  49             $receptorId = (int) $this->input('receptor_id');
  50             $authId = (int) auth()->id();
```


---


```
  49             $receptorId = (int) $this->input('receptor_id');
  50             $authId = (int) auth()->id();
  51 
  52             if ($receptorId === $authId) {
! 53                 $validator->errors()->add('receptor_id', 'No puedes registrar un pago hacia ti mismo.');
  54             }
  55 
  56             $participantesValidos = collect([$plan->user_id])
  57                 ->merge($plan->participantes()->pluck('users.id'))
```


---


```
  62                 ->unique()
  63                 ->values();
  64 
  65             if (! $participantesValidos->contains($receptorId)) {
! 66                 $validator->errors()->add('receptor_id', 'El destinatario del pago no pertenece a este plan.');
  67             }
  68 
  69             // Validar que el monto no exceda la deuda neta calculada
  70             $monto = round((float) $this->input('monto', 0), 2);
```


---



## app/mail/transport/cloudflaremailtransport&#46;php

```
   8 use Symfony\Component\Mime\MessageConverter;
   9 
  10 class CloudflareMailTransport extends AbstractTransport
  11 {
! 12     public function __construct(
  13         protected string $endpoint,
  14         protected string $secret
  15     ) {
! 16         parent::__construct();
  17     }
  18 
! 19     protected function doSend(SentMessage $message): void
  20     {
! 21         $email = MessageConverter::toEmail($message->getOriginalMessage());
  22 
! 23         $recipients = collect($email->getTo())->map(fn ($addr) => $addr->getAddress())->implode(',');
! 24         $subject = $email->getSubject() ?? 'Notificación de Planazo';
! 25         $html = $email->getHtmlBody();
! 26         $text = $email->getTextBody();
  27 
  28         // Si no hay texto plano pero hay HTML, generar fallback
! 29         if (empty($text) && ! empty($html)) {
! 30             $text = strip_tags((string) $html);
  31         }
  32 
! 33         $response = Http::withToken($this->secret)
! 34             ->timeout(15)
! 35             ->post($this->endpoint, [
! 36                 'to' => $recipients,
! 37                 'subject' => $subject,
! 38                 'html' => $html,
! 39                 'text' => $text,
! 40                 'from_name' => config('mail.from.name', 'Planazo'),
! 41             ]);
  42 
! 43         if (! $response->successful() || ! ($response->json('success') ?? false)) {
! 44             $err = $response->json('error') ?? $response->body();
! 45             throw new \RuntimeException("Cloudflare Mail Bridge error: {$err}");
  46         }
  47     }
  48 
! 49     public function __toString(): string
  50     {
! 51         return 'cloudflare_bridge';
  52     }
  53 }
```


---



## app/models/gasto&#46;php

```
  52 
  53     /**
  54      * Participantes entre quienes se dividió el gasto.
  55      */
! 56     public function participantes(): BelongsToMany
  57     {
! 58         return $this->belongsToMany(User::class, 'gasto_divisiones', 'gasto_id', 'user_id')
! 59             ->withPivot('monto_asignado')
! 60             ->withTimestamps();
  61     }
  62 }
```


---



## app/models/gastodivision&#46;php

```
  24 
  25     /**
  26      * Gasto al que corresponde esta división.
  27      */
! 28     public function gasto(): BelongsTo
  29     {
! 30         return $this->belongsTo(Gasto::class, 'gasto_id');
  31     }
  32 
  33     /**
  34      * Usuario asignado a esta porción del gasto.
```


---



## app/models/pago&#46;php

```
  62 
  63     /**
  64      * Evalúa si el pago está en estado pendiente de confirmación.
  65      */
! 66     public function esPendiente(): bool
  67     {
! 68         return $this->estado === 'pendiente';
  69     }
  70 
  71     /**
  72      * Evalúa si el pago fue confirmado.
```


---


```
  118 
  119     /**
  120      * Evalúa si el escaneo aún está pendiente de procesamiento.
  121      */
! 122     public function esEscaneoPendiente(): bool
  123     {
! 124         return $this->estado_escaneo === 'pendiente';
  125     }
  126 }
```


---



## app/models/plan&#46;php

```
  161     public function obtenerGanadorIndiscutido(string $tipo): ?Opcion
  162     {
  163         $opciones = $this->opciones()->where('tipo', $tipo)->withCount('votos')->get();
  164         if ($opciones->isEmpty()) {
! 165             return null;
  166         }
  167 
  168         $maxVotos = $opciones->max('votos_count');
  169         $candidatas = $opciones->where('votos_count', $maxVotos);
```


---


```
  200             throw new \InvalidArgumentException("La propuesta #{$opcion->id} no pertenece al plan #{$this->id}.");
  201         }
  202 
  203         if ($this->esInmutable()) {
! 204             throw new DomainException("No se pueden asignar opciones a un plan en estado {$this->estado}.");
  205         }
  206 
  207         if ($opcion->tipo === 'fecha') {
  208             $this->propuesta_fecha_id = $opcion->id;
! 209         } elseif ($opcion->tipo === 'lugar') {
! 210             $this->propuesta_lugar_id = $opcion->id;
  211         }
  212 
  213         if ($motivo !== null) {
  214             $this->motivo_desempate = $motivo;
```


---



## app/models/user&#46;php

```
  68 
  69     /**
  70      * Actividades donde el usuario es destinatario o contraparte bilateral.
  71      */
! 72     public function actividadesRecibidas(): HasMany
  73     {
! 74         return $this->hasMany(PlanActivity::class, 'destinatario_id');
  75     }
  76 
  77     /**
  78      * Planes creados por el usuario.
```


---


```
  101 
  102     /**
  103      * Opciones de fecha o lugar propuestas por el usuario.
  104      */
! 105     public function opciones(): HasMany
  106     {
! 107         return $this->hasMany(Opcion::class);
  108     }
  109 
  110     /**
  111      * Votos emitidos por el usuario.
```


---



## app/policies/gastopolicy&#46;php

```
  10 {
  11     /**
  12      * Determina si el usuario puede ver los gastos del plan.
  13      */
! 14     public function view(User $user, Gasto $gasto): bool
  15     {
! 16         return $this->esParticipanteDelPlan($user, $gasto->plan);
  17     }
  18 
  19     /**
  20      * Determina si el usuario puede registrar un nuevo gasto en el plan.
```


---


```
  21      */
  22     public function create(User $user, Plan $plan): bool
  23     {
  24         if ($plan->estado === 'cancelado') {
! 25             return false;
  26         }
  27 
  28         return $this->esParticipanteDelPlan($user, $plan);
  29     }
```


---



## app/policies/pagopolicy&#46;php

```
  13      */
  14     public function create(User $user, Plan $plan): bool
  15     {
  16         if ($plan->estado === 'cancelado') {
! 17             return false;
  18         }
  19 
  20         return $this->esParticipanteDelPlan($user, $plan);
  21     }
```


---



## app/policies/planpolicy&#46;php

```
  32      */
  33     public function rechazar(User $user, Plan $plan): bool
  34     {
  35         if ($plan->estado === 'cancelado') {
! 36             return false;
  37         }
  38 
  39         return $this->isInvitado($user, $plan);
  40     }
```


---



## app/providers/appserviceprovider&#46;php

```
  33      */
  34     public function boot(): void
  35     {
  36         if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
! 37             URL::forceScheme('https');
  38         }
  39 
  40         Gate::policy(Plan::class, PlanPolicy::class);
  41         Gate::policy(Gasto::class, GastoPolicy::class);
```


---


```
  41         Gate::policy(Gasto::class, GastoPolicy::class);
  42         Gate::policy(Pago::class, PagoPolicy::class);
  43 
  44         Mail::extend('cloudflare', function (array $config = []) {
! 45             return new CloudflareMailTransport(
! 46                 $config['endpoint'] ?? env('MAIL_BRIDGE_ENDPOINT', 'https://planazo-mail-bridge.moisescisnerosdl.workers.dev'),
! 47                 $config['secret'] ?? env('MAIL_BRIDGE_SECRET', 'planazo-secure-key-2026')
! 48             );
  49         });
  50     }
  51 }
```


---



## app/services/comprobante/geminicomprobantescanner&#46;php

```
  110                 referencia: $referencia,
  111                 confianza: $confianza,
  112                 rawPayload: $parsed
  113             );
! 114         } catch (Throwable $e) {
! 115             Log::error('[GeminiComprobanteScanner] Excepción al procesar imagen: '.$e->getMessage());
  116 
! 117             return new ComprobanteScanResult(
! 118                 esComprobante: false,
! 119                 confianza: 0.0,
! 120                 mensajeError: 'Excepción al escanear comprobante: '.$e->getMessage()
! 121             );
  122         }
  123     }
  124 }
```


---



## app/services/userdataexportservice&#46;php

```
  158         // 3. Planes participados
  159         fputcsv($fp, ['=== PLANES EN LOS QUE PARTICIPA ===']);
  160         fputcsv($fp, ['ID Plan', 'Nombre', 'Estado', 'Organizador', 'Fecha Limite']);
  161         foreach ($data['planes_participados'] as $p) {
! 162             fputcsv($fp, [
! 163                 $p['id'],
! 164                 $p['nombre'],
! 165                 $p['estado'],
! 166                 $p['organizador'],
! 167                 $p['fecha_limite'] ?? 'Sin limite',
! 168             ]);
  169         }
  170         if (empty($data['planes_participados'])) {
  171             fputcsv($fp, ['(Sin participaciones en otros planes)']);
  172         }
```


---


```
  192         // 5. Liquidación de gastos
  193         fputcsv($fp, ['=== GASTOS Y LIQUIDACION ===']);
  194         fputcsv($fp, ['Tipo Registro', 'Plan', 'Concepto / Contraparte', 'Monto (Bs)', 'Estado / Detalle', 'Fecha']);
  195         foreach ($data['liquidacion_gastos']['gastos_pagados'] as $g) {
! 196             fputcsv($fp, [
! 197                 'Gasto Pagado',
! 198                 $g['plan_nombre'],
! 199                 $g['concepto'],
! 200                 number_format($g['monto_total'], 2),
! 201                 "Division: {$g['tipo_division']}",
! 202                 $g['creado_en'],
! 203             ]);
  204         }
  205         foreach ($data['liquidacion_gastos']['divisiones_asignadas'] as $d) {
! 206             fputcsv($fp, [
! 207                 'Consumo Asignado',
! 208                 $d['plan_nombre'],
! 209                 $d['concepto'],
! 210                 number_format($d['monto_asignado'], 2),
! 211                 'Porcion de gasto grupal',
! 212                 '-',
! 213             ]);
  214         }
  215         foreach ($data['liquidacion_gastos']['pagos_realizados'] as $pr) {
! 216             fputcsv($fp, [
! 217                 'Pago Enviado',
! 218                 $pr['plan_nombre'],
! 219                 "Acreedor: {$pr['receptor']}",
! 220                 number_format($pr['monto'], 2),
! 221                 "Estado: {$pr['estado']}",
! 222                 $pr['creado_en'],
! 223             ]);
  224         }
  225         foreach ($data['liquidacion_gastos']['pagos_recibidos'] as $prec) {
! 226             fputcsv($fp, [
! 227                 'Pago Recibido',
! 228                 $prec['plan_nombre'],
! 229                 "Deudor: {$prec['deudor']}",
! 230                 number_format($prec['monto'], 2),
! 231                 "Estado: {$prec['estado']}",
! 232                 $prec['creado_en'],
! 233             ]);
  234         }
  235 
  236         rewind($fp);
  237         $csvContent = stream_get_contents($fp);
```


---


