<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\PlantillaMembrete;
use App\Models\Cliente;
use App\Models\DocumentoGenerado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PlantillaController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasPermission('plantillas.view')) {
            abort(403, 'Acceso restringido: No tienes autorización para ver las plantillas de documentos.');
        }

        $search = $request->query('search');
        $categoria = $request->query('categoria');

        $query = Plantilla::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre_plantilla', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhere('tipo_documento', 'like', "%{$search}%");
            });
        }

        if ($categoria && $categoria !== 'todos') {
            $query->where('categoria', $categoria);
        }

        $plantillas = $query->orderBy('ultima_modificacion', 'desc')->paginate(12);

        $categorias = Plantilla::select('categoria')
            ->whereNotNull('categoria')
            ->distinct()
            ->pluck('categoria');

        $clientes = Cliente::orderBy('c_apellido', 'asc')->get(['id_cliente', 'c_nombre', 'c_apellido', 'c_identificacion']);

        return view('plantillas.index', compact('plantillas', 'categorias', 'clientes', 'search', 'categoria'));
    }

    /**
     * Upload an image or signature asynchronously and return URL.
     */
    public function uploadImage(Request $request)
    {
        if (!Auth::user()->hasPermission('plantillas.create') && !Auth::user()->hasPermission('plantillas.edit')) {
            return response()->json(['error' => 'No tienes permiso para subir imágenes a plantillas.'], 403);
        }

        $destinationPath = public_path('uploads/plantillas_imagenes');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $fileName = 'img_' . uniqid() . '_' . time() . '.' . $extension;
            $file->move($destinationPath, $fileName);
            $url = asset('uploads/plantillas_imagenes/' . $fileName);
            return response()->json(['success' => true, 'url' => $url]);
        }

        if ($request->filled('base64')) {
            $base64Data = $request->input('base64');
            $ext = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
            }
            $decoded = base64_decode($base64Data);
            if ($decoded !== false && strlen($decoded) > 0) {
                $fileName = 'img_' . uniqid() . '_' . time() . '.' . $ext;
                file_put_contents($destinationPath . '/' . $fileName, $decoded);
                $url = asset('uploads/plantillas_imagenes/' . $fileName);
                return response()->json(['success' => true, 'url' => $url]);
            }
        }

        return response()->json(['error' => 'No se pudo procesar la imagen.'], 422);
    }

    /**
     * Helper to convert any base64 images into physical files on disk.
     */
    private function processBase64Images(?string $html): string
    {
        if (empty($html) || !str_contains($html, 'data:image/')) {
            return $html ?? '';
        }

        $destinationPath = public_path('uploads/plantillas_imagenes');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        return preg_replace_callback('/<img([^>]+)src=["\'](data:image\/([a-zA-Z0-9]+);base64,([^"\']+))["\']([^>]*)>/i', function ($matches) use ($destinationPath) {
            $prefix = $matches[1];
            $ext = strtolower($matches[3]) === 'jpeg' ? 'jpg' : strtolower($matches[3]);
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])) {
                $ext = 'png';
            }
            $base64Data = $matches[4];
            $suffix = $matches[5];

            $decoded = base64_decode($base64Data);
            if ($decoded !== false && strlen($decoded) > 0) {
                $filename = 'img_' . uniqid() . '_' . time() . '.' . $ext;
                file_put_contents($destinationPath . '/' . $filename, $decoded);
                $url = asset('uploads/plantillas_imagenes/' . $filename);
                return '<img' . $prefix . 'src="' . $url . '"' . $suffix . '>';
            }

            return $matches[0];
        }, $html);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('plantillas.create')) {
            abort(403, 'Acceso restringido: No tienes autorización para crear nuevas plantillas.');
        }

        $encabezados = PlantillaMembrete::encabezados()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();
        $pies = PlantillaMembrete::pies()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();

        return view('plantillas.create', compact('encabezados', 'pies'));
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('plantillas.create')) {
            abort(403, 'Acceso restringido: No tienes autorización para crear nuevas plantillas.');
        }

        $validated = $request->validate([
            'nombre_plantilla' => 'required|string|max:255|unique:plantillas,nombre_plantilla',
            'descripcion' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
            'encabezado_id' => 'nullable|exists:plantilla_membretes,id',
            'pie_id' => 'nullable|exists:plantilla_membretes,id',
            'contenido_html' => 'required|string',
        ]);

        $validated['categoria'] = !empty($validated['categoria']) ? $validated['categoria'] : 'General';
        $validated['tipo_documento'] = !empty($validated['tipo_documento']) ? $validated['tipo_documento'] : 'general';
        $validated['contenido_html'] = $this->processBase64Images($validated['contenido_html']);
        $validated['activo'] = true;

        Plantilla::create($validated);

        return redirect()->route('plantillas.index')->with('success', '¡Plantilla guardada correctamente!');
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.edit')) {
            abort(403, 'Acceso restringido: No tienes autorización para editar plantillas de documentos.');
        }

        $encabezados = PlantillaMembrete::encabezados()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();
        $pies = PlantillaMembrete::pies()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();

        return view('plantillas.edit', compact('plantilla', 'encabezados', 'pies'));
    }

    /**
     * Update the specified template in storage.
     */
    public function update(Request $request, Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.edit')) {
            abort(403, 'Acceso restringido: No tienes autorización para editar plantillas de documentos.');
        }

        $validated = $request->validate([
            'nombre_plantilla' => 'required|string|max:255|unique:plantillas,nombre_plantilla,' . $plantilla->id_plantilla . ',id_plantilla',
            'descripcion' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
            'encabezado_id' => 'nullable|exists:plantilla_membretes,id',
            'pie_id' => 'nullable|exists:plantilla_membretes,id',
            'contenido_html' => 'required|string',
        ]);

        $validated['categoria'] = !empty($validated['categoria']) ? $validated['categoria'] : ($plantilla->categoria ?? 'General');
        $validated['tipo_documento'] = !empty($validated['tipo_documento']) ? $validated['tipo_documento'] : ($plantilla->tipo_documento ?? 'general');
        $validated['contenido_html'] = $this->processBase64Images($validated['contenido_html']);

        $plantilla->update($validated);

        return redirect()->route('plantillas.index')->with('success', '¡Plantilla actualizada con éxito!');
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.delete')) {
            abort(403, 'Acceso restringido: No tienes autorización para eliminar plantillas de documentos.');
        }

        $plantilla->delete();
        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada correctamente.');
    }

    /**
     * Duplicate an existing template in 1 click.
     */
    public function duplicar(Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.create')) {
            abort(403, 'Acceso restringido: No tienes autorización para crear o duplicar plantillas.');
        }

        $nuevoNombre = 'Copia de ' . $plantilla->nombre_plantilla;
        $count = Plantilla::where('nombre_plantilla', 'like', "{$nuevoNombre}%")->count();
        if ($count > 0) {
            $nuevoNombre .= ' (' . ($count + 1) . ')';
        }

        $clon = Plantilla::create([
            'nombre_plantilla' => $nuevoNombre,
            'descripcion' => $plantilla->descripcion,
            'contenido_html' => $plantilla->contenido_html,
            'tipo_documento' => $plantilla->tipo_documento,
            'categoria' => $plantilla->categoria,
            'encabezado_id' => $plantilla->encabezado_id,
            'pie_id' => $plantilla->pie_id,
            'activo' => true,
        ]);

        return redirect()->route('plantillas.edit', $clon->id_plantilla)
            ->with('success', '¡Plantilla duplicada con éxito! Ya puedes editarla.');
    }

    /**
     * Import and parse a document (PDF, DOCX, TXT) into editable HTML.
     */
    public function importPdf(Request $request)
    {
        if (!Auth::user()->hasPermission('plantillas.create') && !Auth::user()->hasPermission('plantillas.edit')) {
            return response()->json(['success' => false, 'message' => 'No tienes autorización para importar plantillas.'], 403);
        }
        $request->validate([
            'documento' => 'required|file|max:20480', // max 20MB
        ]);

        $file = $request->file('documento');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $htmlOutput = '';

        try {
            if ($extension === 'docx') {
                // Parse Word DOCX using ZipArchive & XML
                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    $xmlContent = $zip->getFromName('word/document.xml');
                    $zip->close();

                    if ($xmlContent) {
                        // Limpiar tags XML y extraer párrafos y negritas
                        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                        $namespaces = $xml->getNamespaces(true);
                        $xml->registerXPathNamespace('w', $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                        
                        $paragraphs = $xml->xpath('//w:p');
                        foreach ($paragraphs as $p) {
                            $pText = '';
                            $runs = $p->xpath('.//w:r');
                            foreach ($runs as $r) {
                                $isBold = !empty($r->xpath('.//w:b'));
                                $tNodes = $r->xpath('.//w:t');
                                $runText = '';
                                foreach ($tNodes as $t) {
                                    $runText .= (string)$t;
                                }
                                if ($isBold && trim($runText) !== '') {
                                    $pText .= '<strong>' . htmlspecialchars($runText) . '</strong>';
                                } else {
                                    $pText .= htmlspecialchars($runText);
                                }
                            }
                            if (trim(strip_tags($pText)) !== '') {
                                $htmlOutput .= '<p style="text-align: justify; margin-bottom: 8pt; line-height: 1.45;">' . $pText . '</p>' . "\n";
                            }
                        }
                    }
                }
            } elseif ($extension === 'txt') {
                $content = file_get_contents($file->getRealPath());
                $lines = explode("\n", $content);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if ($trimmed !== '') {
                        $htmlOutput .= '<p style="text-align: justify; margin-bottom: 8pt; line-height: 1.45;">' . htmlspecialchars($trimmed) . '</p>' . "\n";
                    }
                }
            } else {
                // Fallback básico para texto plano o streams
                $content = @file_get_contents($file->getRealPath());
                if ($content) {
                    // Extraer cadenas legibles de texto
                    preg_match_all('/[\x20-\x7E\x{00A0}-\x{00FF}]{4,}/u', $content, $matches);
                    $readable = array_filter($matches[0] ?? [], fn($s) => !str_contains($s, 'obj') && !str_contains($s, 'endobj') && !str_contains($s, 'stream'));
                    foreach (array_slice($readable, 0, 100) as $textLine) {
                        $trimmed = trim($textLine);
                        if (strlen($trimmed) > 15) {
                            $htmlOutput .= '<p style="text-align: justify; margin-bottom: 8pt;">' . htmlspecialchars($trimmed) . '</p>' . "\n";
                        }
                    }
                }
            }

            if (empty(trim($htmlOutput))) {
                $htmlOutput = '<p>Documento importado: ' . htmlspecialchars($originalName) . '</p>';
            }

            return response()->json([
                'success' => true,
                'html' => $htmlOutput,
                'nombre' => $originalName,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error importando documento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar el archivo en el servidor: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show the document generator view with substituted client variables.
     */
    public function generar(Request $request, Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.generar') && !Auth::user()->hasPermission('plantillas.view')) {
            abort(403, 'Acceso restringido: No tienes autorización para generar documentos notariales.');
        }

        $clienteId = $request->query('cliente');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;
        $clientes = Cliente::orderBy('c_apellido', 'asc')->get(['id_cliente', 'c_nombre', 'c_apellido', 'c_identificacion']);

        $encabezados = PlantillaMembrete::encabezados()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();
        $pies = PlantillaMembrete::pies()->orderBy('es_predeterminado', 'desc')->orderBy('nombre', 'asc')->get();

        // Extraer variables custom no nativas del cliente (ej. [[apoderado_nombre]], [[inmueble_direccion]])
        $customVars = $this->extractCustomVariables($plantilla->contenido_html);

        $contenidoGenerado = $plantilla->renderForCliente($cliente);

        return view('plantillas.generar', compact('plantilla', 'cliente', 'clientes', 'contenidoGenerado', 'customVars', 'encabezados', 'pies'));
    }

    /**
     * Store the generated document in the client's expediente history.
     */
    public function guardarDocumentoCliente(Request $request, ?Plantilla $plantilla = null)
    {
        if (!Auth::user()->hasPermission('plantillas.generar') && !Auth::user()->hasPermission('plantillas.create')) {
            return response()->json(['success' => false, 'message' => 'No tienes autorización para guardar documentos generados.'], 403);
        }

        $plantillaId = $plantilla ? $plantilla->id_plantilla : $request->input('plantilla_id');
        $titulo = $request->input('titulo_documento') ?: ($plantilla ? $plantilla->nombre_plantilla : 'Documento Notarial');

        $request->validate([
            'id_cliente' => 'required|exists:cliente,id_cliente',
            'contenido_html' => 'required|string',
        ]);

        $contenidoHtml = $this->processBase64Images($request->input('contenido_html'));

        DocumentoGenerado::create([
            'id_cliente' => $request->input('id_cliente'),
            'plantilla_id' => $plantillaId,
            'titulo_documento' => $titulo,
            'contenido_html' => $contenidoHtml,
            'usuario_creador' => Auth::user()->name ?? 'Sistema',
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Documento guardado exitosamente en el expediente del cliente!'
        ]);
    }

    /**
     * Convert any local image tags inside HTML to base64 URIs for rock-solid DomPDF rendering.
     */
    private function convertImagesForPdf(?string $html): string
    {
        if (empty($html)) return '';

        return preg_replace_callback('/<img([^>]+)src=["\']([^"\']+)["\']([^>]*)>/i', function ($matches) {
            $prefix = $matches[1];
            $src = $matches[2];
            $suffix = $matches[3];

            if (str_starts_with($src, 'data:image/')) {
                return $matches[0];
            }

            $localPath = null;
            if (str_starts_with($src, '/')) {
                $localPath = public_path(ltrim($src, '/'));
            } elseif (str_contains($src, 'localhost') || str_contains($src, '127.0.0.1') || str_contains($src, asset(''))) {
                $parsedPath = parse_url($src, PHP_URL_PATH);
                $localPath = public_path(ltrim($parsedPath, '/'));
            } elseif (!str_starts_with($src, 'http://') && !str_starts_with($src, 'https://')) {
                $localPath = public_path($src);
            }

            if ($localPath && file_exists($localPath)) {
                $mime = mime_content_type($localPath) ?: 'image/jpeg';
                $data = file_get_contents($localPath);
                $base64 = 'data:' . $mime . ';base64,' . base64_encode($data);
                return '<img' . $prefix . 'src="' . $base64 . '"' . $suffix . '>';
            }

            return $matches[0];
        }, $html);
    }

    /**
     * Download or stream the final generated PDF with notary branding and optional watermark.
     */
    public function descargarPdf(Request $request, Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.generar') && !Auth::user()->hasPermission('plantillas.view')) {
            abort(403, 'Acceso restringido: No tienes autorización para descargar o visualizar documentos generados.');
        }

        $htmlContenido = $request->input('contenido_html', $plantilla->contenido_html);
        $clienteId = $request->input('id_cliente');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;
        $marcaAgua = $request->input('marca_agua', '');

        // Membretes opcionales desde request o por defecto de la plantilla
        $encabezadoId = $request->input('encabezado_id', $plantilla->encabezado_id);
        $pieId = $request->input('pie_id', $plantilla->pie_id);

        $encabezado = $encabezadoId ? PlantillaMembrete::find($encabezadoId) : null;
        $pie = $pieId ? PlantillaMembrete::find($pieId) : null;

        // Auto-guardar en el historial del cliente si hay cliente seleccionado
        if ($cliente) {
            DocumentoGenerado::create([
                'id_cliente' => $cliente->id_cliente,
                'plantilla_id' => $plantilla->id_plantilla,
                'titulo_documento' => $plantilla->nombre_plantilla,
                'contenido_html' => $htmlContenido,
                'usuario_creador' => Auth::user()->name ?? 'Sistema',
            ]);
        }

        $cleanHtml = $this->sanitizeHtmlForPdf($htmlContenido);
        $cleanHtml = $this->convertImagesForPdf($cleanHtml);

        $encabezadoHtml = $encabezado ? $this->convertImagesForPdf($encabezado->contenido_html) : '';
        $pieHtml = $pie ? $this->convertImagesForPdf($pie->contenido_html) : '';

        $pdf = Pdf::loadView('plantillas.documento_pdf', [
            'plantilla' => $plantilla,
            'cliente' => $cliente,
            'htmlContenido' => $cleanHtml,
            'encabezadoHtml' => $encabezadoHtml,
            'pieHtml' => $pieHtml,
            'marcaAgua' => $marcaAgua,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Doc_' . str_replace(' ', '_', $plantilla->nombre_plantilla) . '_' . ($cliente ? $cliente->c_identificacion : 'Doc') . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Export generated document directly to Microsoft Word (.docx).
     */
    public function descargarDocx(Request $request, Plantilla $plantilla)
    {
        if (!Auth::user()->hasPermission('plantillas.generar') && !Auth::user()->hasPermission('plantillas.view')) {
            abort(403, 'Acceso restringido: No tienes autorización para exportar documentos a Word.');
        }
        $htmlContenido = $request->input('contenido_html', $plantilla->contenido_html);
        $clienteId = $request->input('id_cliente');
        $cliente = $clienteId ? Cliente::find($clienteId) : null;

        $encabezadoId = $request->input('encabezado_id', $plantilla->encabezado_id);
        $pieId = $request->input('pie_id', $plantilla->pie_id);

        $encabezado = $encabezadoId ? PlantillaMembrete::find($encabezadoId) : null;
        $pie = $pieId ? PlantillaMembrete::find($pieId) : null;

        $cleanHtml = $this->sanitizeHtmlForWord($htmlContenido);
        $encabezadoHtml = $encabezado ? $this->sanitizeHtmlForWord($encabezado->contenido_html) : '';
        $pieHtml = $pie ? $this->sanitizeHtmlForWord($pie->contenido_html) : '';

        $wordHtml = '<html xmlns:v="urn:schemas-microsoft-com:vml"
        xmlns:o="urn:schemas-microsoft-com:office:office"
        xmlns:w="urn:schemas-microsoft-com:office:word"
        xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
        xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>100</w:Zoom>
                    <w:DoNotOptimizeForBrowser/>
                </w:WordDocument>
            </xml>
            <![endif]-->
            <title>' . htmlspecialchars($plantilla->nombre_plantilla) . '</title>
            <style>
                @page Section1 {
                    size: 595.3pt 841.9pt; /* A4 */
                    margin: ' . (!empty($encabezadoHtml) ? '70.0pt' : '45.0pt') . ' 45.0pt ' . (!empty($pieHtml) ? '60.0pt' : '45.0pt') . ' 45.0pt;
                    mso-header-margin: 28.0pt;
                    mso-footer-margin: 28.0pt;
                    mso-header: url("#h1") h1;
                    mso-footer: url("#f1") f1;
                    mso-paper-source: 0;
                }
                div.Section1 { page: Section1; }
                body { 
                    font-family: Arial, Helvetica, sans-serif; 
                    font-size: 11pt; 
                    line-height: 1.45; 
                    color: #000000; 
                }
                h1 { font-size: 13.5pt; font-weight: bold; text-align: center; margin: 8pt 0 4pt 0; }
                h2 { font-size: 12pt; font-weight: bold; text-align: center; margin: 8pt 0 4pt 0; }
                h3 { font-size: 11pt; font-weight: bold; margin: 6pt 0 3pt 0; }
                p { text-align: justify; margin-bottom: 8pt; line-height: 1.45; }
                table { width: 100%; border-collapse: collapse; margin-top: 10pt; margin-bottom: 10pt; }
                td { vertical-align: top; }
                img { max-width: 100%; height: auto; }
                div[style*="mso-element:header"] { mso-element: header; }
                div[style*="mso-element:footer"] { mso-element: footer; }
            </style>
        </head>
        <body>
            <div class="Section1">
                <!-- Contenido Principal del Documento -->
                ' . $cleanHtml . '

                <!-- Encabezado Repetido en Todas las Páginas en Word -->
                ' . (!empty($encabezadoHtml) ? '<div style="mso-element:header" id="h1"><p class="MsoHeader">' . $encabezadoHtml . '</p></div>' : '') . '

                <!-- Pie de Página Repetido en Todas las Páginas en Word -->
                ' . (!empty($pieHtml) ? '<div style="mso-element:footer" id="f1"><p class="MsoFooter">' . $pieHtml . '</p></div>' : '') . '
            </div>
        </body></html>';

        $fileName = 'Documento_' . str_replace(' ', '_', $plantilla->nombre_plantilla) . '_' . ($cliente ? $cliente->c_identificacion : 'General') . '.doc';

        return response($wordHtml, 200, [
            'Content-Type' => 'application/msword; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Sanitize and format HTML specifically for Microsoft Word compatibility.
     */
    private function sanitizeHtmlForWord(string $html): string
    {
        // 1. Limpiar marcas de selección UI
        $html = str_replace(['selected-img', 'free-moving'], '', $html);

        // 2. Convertir imágenes con tablas y atributos nativos de Word
        $html = preg_replace_callback('/<img([^>]+)>/i', function($matches) {
            $tag = $matches[1];
            
            // Extraer src
            preg_match('/src=["\']([^"\']+)["\']/i', $tag, $srcMatch);
            $src = $srcMatch[1] ?? '';
            if (empty($src)) return '';

            // Si es URL local, incrustar en base64 para que Word no dependa de HTTP
            if (str_contains($src, '/uploads/')) {
                $parsedPath = parse_url($src, PHP_URL_PATH);
                $localFilePath = public_path(ltrim($parsedPath, '/'));
                if (file_exists($localFilePath)) {
                    $fileContent = file_get_contents($localFilePath);
                    $mime = mime_content_type($localFilePath) ?: 'image/png';
                    $src = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
                }
            }
            
            // Extraer ancho deseado
            $width = 200;
            if (preg_match('/width:\s*([0-9]+)px/i', $tag, $wMatch)) {
                $width = (int)$wMatch[1];
            } elseif (preg_match('/max-width:\s*([0-9]+)px/i', $tag, $mwMatch)) {
                $width = (int)$mwMatch[1];
            } elseif (preg_match('/width=["\']([0-9]+)["\']/i', $tag, $wAttrMatch)) {
                $width = (int)$wAttrMatch[1];
            }
            
            $width = max(60, min(450, $width));
            $widthPt = round($width * 0.75); // conversión de px a pt para Word

            // Detectar alineación
            $align = 'center';
            if (preg_match('/float:\s*left/i', $tag) || preg_match('/left:\s*([0-9]+)px/i', $tag)) {
                $align = 'left';
            } elseif (preg_match('/float:\s*right/i', $tag)) {
                $align = 'right';
            }

            return '<table border="0" cellspacing="0" cellpadding="0" align="' . $align . '" width="' . $width . '" style="width: ' . $widthPt . 'pt; max-width: ' . $widthPt . 'pt; border: none; margin: 8pt auto; border-collapse: collapse;">
                <tr>
                    <td align="' . $align . '" width="' . $width . '" style="width: ' . $widthPt . 'pt; border: none; padding: 0;">
                        <img src="' . $src . '" width="' . $width . '" style="width: ' . $widthPt . 'pt; max-width: ' . $widthPt . 'pt; height: auto;" border="0">
                    </td>
                </tr>
            </table>';
        }, $html);

        return $html;
    }

    /**
     * Sanitize HTML specifically for DomPDF renderer to avoid overlapping.
     */
    private function sanitizeHtmlForPdf(string $html): string
    {
        // Limpiar clases de selección de UI
        $html = str_replace(['selected-img'], '', $html);

        // Convertir imágenes con position: absolute a flujo natural compatible con DomPDF
        $html = preg_replace_callback('/<img([^>]+)>/i', function($matches) {
            $tag = $matches[1];
            
            // Extraer src
            preg_match('/src=["\']([^"\']+)["\']/i', $tag, $srcMatch);
            $src = $srcMatch[1] ?? '';
            if (empty($src)) return '';

            // Si es imagen local, usar ruta directa del sistema de archivos para DomPDF
            if (str_contains($src, '/uploads/')) {
                $parsedPath = parse_url($src, PHP_URL_PATH);
                $localFilePath = public_path(ltrim($parsedPath, '/'));
                if (file_exists($localFilePath)) {
                    $src = $localFilePath;
                }
            }

            // Extraer ancho
            $width = 200;
            if (preg_match('/width:\s*([0-9]+)px/i', $tag, $wMatch)) {
                $width = (int)$wMatch[1];
            } elseif (preg_match('/max-width:\s*([0-9]+)px/i', $tag, $mwMatch)) {
                $width = (int)$mwMatch[1];
            } elseif (preg_match('/width=["\']([0-9]+)["\']/i', $tag, $wAttrMatch)) {
                $width = (int)$wAttrMatch[1];
            }
            $width = max(60, min(500, $width));

            $isAbsolute = str_contains($tag, 'position: absolute') || str_contains($tag, 'position:absolute');
            $left = 0;
            if (preg_match('/left:\s*([0-9]+)px/i', $tag, $lMatch)) {
                $left = (int)$lMatch[1];
            }

            // Si estaba a la izquierda
            if (str_contains($tag, 'float: left') || str_contains($tag, 'float:left') || ($isAbsolute && $left < 200)) {
                return '<img src="' . $src . '" style="float: left; width: ' . $width . 'px; max-width: ' . $width . 'px; height: auto; margin: 0 25px 15px 0; display: inline-block;" border="0">';
            }
            
            // Si estaba a la derecha
            if (str_contains($tag, 'float: right') || str_contains($tag, 'float:right') || ($isAbsolute && $left > 350)) {
                return '<img src="' . $src . '" style="float: right; width: ' . $width . 'px; max-width: ' . $width . 'px; height: auto; margin: 0 0 15px 25px; display: inline-block;" border="0">';
            }

            // Centrado en bloque
            return '<div style="text-align: center; margin: 0 auto 18px auto; clear: both;"><img src="' . $src . '" style="width: ' . $width . 'px; max-width: ' . $width . 'px; height: auto; display: inline-block;" border="0"></div>';
        }, $html);

        return $html;
    }

    /**
     * Helper to detect custom tags in template that are not default client attributes.
     */
    private function extractCustomVariables(string $html): array
    {
        preg_match_all('/\[\[([a-zA-Z0-9_\-]+)\]\]/', $html, $matches);
        $found = array_unique($matches[1] ?? []);

        $defaultClientTags = [
            'cliente_nombre_completo', 'cliente_nombre', 'cliente_apellido',
            'cliente_identificacion', 'cliente_telefono', 'cliente_email',
            'cliente_direccion', 'cliente_direccion_completa', 'cliente_ciudad',
            'cliente_estado', 'cliente_pais', 'cliente_codpostal', 'cliente_napartamento',
            'cliente_deuda', 'cliente_saldo', 'fecha_actual', 'fecha_actual_corta',
            'anio_actual', 'notario_nombre', 'notary_info_name', 'notary_address',
            'notary_info_title', 'notary_tel', 'notary_whatsapp', 'notary_email',
            'lugar_otorgamiento', 'fecha_otorgamiento', 'fecha_otorgamiento_raw_usa',
            'poderdante_nombre', 'poderdante_identificacion', 'poderdante_telefono',
            'poderdante_telefono_formatted', 'poderdante_domicilio_calle',
            'poderdante_domicilio_ciudad', 'poderdante_domicilio_estado', 'poderdante_domicilio_pais'
        ];

        $custom = [];
        foreach ($found as $tag) {
            if (!in_array($tag, $defaultClientTags)) {
                $label = ucwords(str_replace('_', ' ', $tag));
                $custom[] = [
                    'tag' => '[[' . $tag . ']]',
                    'key' => $tag,
                    'label' => $label,
                ];
            }
        }

        return $custom;
    }

    /**
     * Display the digital archive of all documents generated from templates.
     */
    public function expediente(Request $request)
    {
        if (!Auth::user()->hasPermission('plantillas.view')) {
            abort(403, 'Acceso restringido: No tienes autorización para ver el expediente de plantillas.');
        }

        $search = $request->query('search');
        $plantillaId = $request->query('plantilla_id');
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');

        $query = DocumentoGenerado::with(['cliente', 'plantilla']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo_documento', 'like', "%{$search}%")
                  ->orWhere('usuario_creador', 'like', "%{$search}%")
                  ->orWhereHas('cliente', function ($cq) use ($search) {
                      $cq->where('c_nombre', 'like', "%{$search}%")
                         ->orWhere('c_apellido', 'like', "%{$search}%")
                         ->orWhere('c_identificacion', 'like', "%{$search}%");
                  });
            });
        }

        if ($plantillaId && $plantillaId !== 'todas') {
            $query->where('plantilla_id', $plantillaId);
        }

        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        $documentos = $query->orderBy('created_at', 'desc')->paginate(15);
        $plantillas = Plantilla::orderBy('nombre_plantilla', 'asc')->get(['id_plantilla', 'nombre_plantilla']);
        $totalDocs = DocumentoGenerado::count();

        return view('plantillas.expediente', compact('documentos', 'plantillas', 'totalDocs', 'search', 'plantillaId', 'fechaDesde', 'fechaHasta'));
    }

    /**
     * Remove a generated document record from the archive.
     */
    public function destroyExpediente(DocumentoGenerado $documento)
    {
        if (!Auth::user()->hasPermission('plantillas.delete')) {
            abort(403, 'Acceso restringido: No tienes autorización para eliminar documentos del expediente.');
        }

        $documento->delete();

        return redirect()->route('plantillas.expediente')->with('success', 'Documento eliminado del expediente exitosamente.');
    }
}
