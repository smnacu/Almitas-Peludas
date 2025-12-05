<?php
/**
 * Almitas Peludas - API Peluquería
 * Endpoint: Agendar Turno
 * 
 * POST /api/peluqueria/agendar_turno.php
 * 
 * Valida que el barrio coincida con el día de atención:
 * - Lunes: Oeste
 * - Miércoles: Centro  
 * - Viernes: Norte
 * 
 * @package AlmitasPeludas
 */

require_once __DIR__ . '/../../config/database.php';

// Configurar CORS
setCorsHeaders();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método no permitido. Use POST.', 405);
}

// Obtener datos del request
$data = getJsonInput();

// Validar campos requeridos
$requiredFields = ['fecha', 'hora', 'barrio', 'servicio_id', 'cliente_id', 'direccion'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        jsonError("El campo '$field' es requerido.", 400);
    }
}

// Extraer datos
$fecha = $data['fecha'];
$hora = $data['hora'];
$barrio = trim($data['barrio']);
$servicioId = (int) $data['servicio_id'];
$clienteId = (int) $data['cliente_id'];
$direccion = trim($data['direccion']);
$notas = $data['notas'] ?? null;

// ============================================
// LÓGICA DE VALIDACIÓN DE ZONAS POR DÍA
// ============================================
$zonasPorDia = [
    1 => 'Oeste',      // Lunes
    3 => 'Centro',     // Miércoles
    5 => 'Norte',      // Viernes
];

// Obtener el día de la semana (1=Lunes, 7=Domingo)
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fechaObj) {
    jsonError('Formato de fecha inválido. Use YYYY-MM-DD.', 400);
}

$diaSemana = (int) $fechaObj->format('N'); // 1=Lunes, 2=Martes, etc.

// Verificar si el día es válido para turnos
if (!isset($zonasPorDia[$diaSemana])) {
    $diasPermitidos = ['Lunes (Oeste)', 'Miércoles (Centro)', 'Viernes (Norte)'];
    jsonError(
        'Solo atendemos los días: ' . implode(', ', $diasPermitidos) . '. ' .
        'La fecha seleccionada no corresponde a un día de atención.',
        400
    );
}

// Verificar que el barrio coincida con el día
$zonaEsperada = $zonasPorDia[$diaSemana];
$barrioNormalizado = strtolower($barrio);
$zonaEsperadaNormalizada = strtolower($zonaEsperada);

// Mapeo de barrios a zonas (expandible)
$barriosPorZona = [
    'oeste'  => ['oeste', 'barrio oeste', 'zona oeste'],
    'centro' => ['centro', 'barrio centro', 'zona centro', 'downtown'],
    'norte'  => ['norte', 'barrio norte', 'zona norte'],
];

$zonaDelBarrio = null;
foreach ($barriosPorZona as $zona => $barrios) {
    if (in_array($barrioNormalizado, $barrios)) {
        $zonaDelBarrio = $zona;
        break;
    }
}

// Si el barrio no está mapeado, usar el nombre directamente
if ($zonaDelBarrio === null) {
    $zonaDelBarrio = $barrioNormalizado;
}

if ($zonaDelBarrio !== $zonaEsperadaNormalizada) {
    $nombreDia = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$diaSemana - 1];
    jsonError(
        "El día $nombreDia solo atendemos en zona $zonaEsperada. " .
        "El barrio '$barrio' no corresponde a esta zona. " .
        "Por favor, seleccione otra fecha.",
        400
    );
}

// ============================================
// VALIDAR QUE EL SERVICIO EXISTA
// ============================================
try {
    $db = Database::getConnection();

    // Verificar que el servicio existe y está activo
    $stmt = $db->prepare("SELECT id, nombre FROM peluqueria_servicios WHERE id = ? AND activo = 1");
    $stmt->execute([$servicioId]);
    $servicio = $stmt->fetch();

    if (!$servicio) {
        jsonError('El servicio seleccionado no existe o no está disponible.', 404);
    }

    // Verificar que el cliente existe
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE id = ? AND activo = 1");
    $stmt->execute([$clienteId]);
    if (!$stmt->fetch()) {
        jsonError('Cliente no encontrado.', 404);
    }

    // ============================================
    // CREAR EL TURNO
    // ============================================
    $stmt = $db->prepare("
        INSERT INTO peluqueria_turnos 
        (cliente_id, servicio_id, fecha, hora, direccion, barrio, notas, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");

    $stmt->execute([
        $clienteId,
        $servicioId,
        $fecha,
        $hora,
        $direccion,
        $barrio,
        $notas
    ]);

    $turnoId = $db->lastInsertId();

    // Respuesta exitosa
    jsonResponse([
        'success' => true,
        'message' => 'Turno agendado exitosamente',
        'data' => [
            'turno_id' => (int) $turnoId,
            'fecha' => $fecha,
            'hora' => $hora,
            'servicio' => $servicio['nombre'],
            'barrio' => $barrio,
            'estado' => 'pendiente'
        ]
    ], 201);

} catch (PDOException $e) {
    error_log("Error al crear turno: " . $e->getMessage());
    jsonError('Error interno del servidor al procesar el turno.', 500);
}
