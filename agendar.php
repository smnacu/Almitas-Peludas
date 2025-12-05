<?php
/**
 * Almitas Peludas - Agendar Turno
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Agendar Turno';
$servicios = getServicios();

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width: 700px;">
        <h1 class="section-title">Agendar <span class="text-gradient">Turno</span></h1>
        
        <!-- Info de Zonas -->
        <div class="zonas-info">
            <div class="zona-card">
                <div class="zona-dia">Lunes</div>
                <div class="zona-nombre">Oeste</div>
            </div>
            <div class="zona-card">
                <div class="zona-dia">Miércoles</div>
                <div class="zona-nombre">Centro</div>
            </div>
            <div class="zona-card">
                <div class="zona-dia">Viernes</div>
                <div class="zona-nombre">Norte</div>
            </div>
        </div>
        
        <div id="alert-container"></div>
        
        <form id="turno-form" class="card">
            <div class="form-group">
                <label class="form-label">📅 Fecha del turno</label>
                <input type="date" 
                       name="fecha" 
                       id="fecha" 
                       class="form-control" 
                       min="<?= date('Y-m-d') ?>"
                       required>
                <small style="color: var(--text-muted);">Solo Lunes, Miércoles y Viernes</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">🕐 Horario preferido</label>
                <select name="hora" id="hora" class="form-control" required>
                    <option value="">Selecciona un horario</option>
                    <option value="09:00">09:00 - Mañana temprano</option>
                    <option value="10:00">10:00</option>
                    <option value="11:00">11:00</option>
                    <option value="14:00">14:00 - Tarde</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">📍 Zona/Barrio</label>
                <select name="barrio" id="barrio" class="form-control" required>
                    <option value="">Se asigna según el día</option>
                    <option value="Oeste">Oeste</option>
                    <option value="Centro">Centro</option>
                    <option value="Norte">Norte</option>
                </select>
                <small style="color: var(--text-muted);">Se autoselecciona al elegir la fecha</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">🏠 Dirección</label>
                <input type="text" 
                       name="direccion" 
                       class="form-control" 
                       placeholder="Ej: Av. San Martín 1234"
                       required>
            </div>
            
            <div class="form-group">
                <label class="form-label">✂️ Servicio</label>
                <select name="servicio_id" class="form-control" required>
                    <option value="">Selecciona un servicio</option>
                    <?php foreach ($servicios as $s): ?>
                    <option value="<?= $s['id'] ?>">
                        <?= e($s['nombre']) ?> - <?= formatPrecio($s['precio']) ?> (<?= $s['duracion_minutos'] ?> min)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">📝 Notas adicionales</label>
                <textarea name="notas" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Información sobre tu mascota, indicaciones especiales, etc."></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                ✅ Confirmar Turno
            </button>
        </form>
        
        <p class="text-center mt-3" style="color: var(--text-secondary);">
            Te contactaremos por WhatsApp para confirmar tu turno.
        </p>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', initTurnoForm);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
