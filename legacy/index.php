<?php
require 'db.php';
$historial = [];
$socio = null;
$total = 0;

// Obtener lista de todos los socios para el buscador
$lista_socios = $db->getSocios();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['socio_id'])) {
    $socio_id = $_POST['socio_id'];
    
    // Buscar datos del socio
    foreach($lista_socios as $s) {
        if ($s['id'] == $socio_id) { $socio = $s; break; }
    }

    if ($socio) {
        // Buscar sus pagos
        $stmt = $pdo->prepare("SELECT * FROM pagos WHERE socio_id = ? ORDER BY fecha DESC");
        $stmt->execute([$socio_id]);
        $historial = $stmt->fetchAll();
        
        foreach($historial as $h) $total += $h['monto'];

        // Obtener Aporte Mínimo Global
        $stmt_min = $pdo->prepare("SELECT valor FROM ajustes WHERE clave = 'cuota_minima'");
        $stmt_min->execute();
        $cuota_minima = $stmt_min->fetchColumn() ?: 0;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Consulta Equipo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'League Spartan', sans-serif; background-color: #000; color: #e5e5e5; }
        .text-carmesi { color: #D62B2E; }
        .bg-carmesi { background-color: #D62B2E; }
        select { -webkit-appearance: none; appearance: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="logo.png" alt="Logo" class="h-24 mx-auto mb-4 object-contain">
            <h1 class="text-gray-500 uppercase tracking-widest text-xs">Consulta de Aportes</h1>
        </div>

        <?php if (!$socio): ?>
            <div class="bg-[#111] p-6 rounded-xl border border-[#222] shadow-2xl">
                <form method="POST" class="flex flex-col gap-4">
                    <label class="text-gray-500 text-xs font-bold uppercase tracking-wide">Busca tu nombre</label>
                    
                    <div class="relative">
                        <select name="socio_id" class="w-full bg-black border border-[#333] text-white text-lg p-4 rounded-lg focus:border-[#D62B2E] outline-none" required>
                            <option value="" disabled selected>Seleccionar...</option>
                            <?php foreach($lista_socios as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">▼</div>
                    </div>

                    <button type="submit" class="bg-carmesi text-white font-bold py-4 rounded-lg uppercase tracking-widest mt-2 hover:bg-red-700 transition">
                        Ver mis aportes
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="bg-[#111] rounded-xl border border-[#222] shadow-2xl overflow-hidden relative animate-fade-in">
                <a href="index.php" class="absolute top-4 right-4 text-gray-500 hover:text-white text-xl">&times;</a>

                <div class="p-6 border-b border-[#222]">
                    <p class="text-gray-500 text-[10px] uppercase tracking-widest">Jugador</p>
                    <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($socio['nombre']) ?></h2>
                    <?php if($socio['telefono']): ?>
                        <p class="text-gray-400 text-xs mt-1 italic"><?= htmlspecialchars($socio['telefono']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="p-8 text-center bg-gradient-to-b from-[#D62B2E]/10 to-transparent">
                    <p class="text-gray-400 text-xs uppercase mb-1">Total Acumulado</p>
                    <div class="text-5xl font-bold text-white tracking-tighter">$<?= number_format($total, 2) ?></div>
                    
                    <div class="mt-4 flex justify-around border-t border-[#222] pt-4">
                        <div>
                            <p class="text-[9px] text-gray-500 uppercase tracking-widest">Compromiso Club</p>
                            <p class="text-white font-bold text-sm">$<?= number_format($cuota_minima, 2) ?></p>
                        </div>
                        <div class="border-l border-[#222] pl-4">
                            <?php 
                                // Diferencia simple (Total vs Cuota Global)
                                $diferencia = $total - $cuota_minima;
                                $color = $diferencia >= 0 ? 'text-green-500' : 'text-red-500';
                            ?>
                            <p class="text-[9px] text-gray-500 uppercase tracking-widest">Saldo vs Objetivo</p>
                            <p class="<?= $color ?> font-bold text-sm"><?= $diferencia >= 0 ? '+' : '' ?>$<?= number_format($diferencia, 2) ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-black/40 max-h-[400px] overflow-y-auto">
                    <?php if (count($historial) > 0): ?>
                        <div class="divide-y divide-[#222]">
                            <?php foreach($historial as $h): ?>
                                <div class="p-4 flex justify-between items-center">
                                    <div>
                                        <p class="text-[#D62B2E] font-bold text-lg">$<?= number_format($h['monto'], 2) ?></p>
                                        <p class="text-gray-600 text-[10px] uppercase">
                                            <span class="font-bold text-gray-400"><?= $h['categoria'] ?></span> • 
                                            <?= date('d M Y', strtotime($h['fecha'])) ?>
                                        </p>
                                    </div>
                                    <span class="text-sm text-gray-300"><?= htmlspecialchars($h['nota']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="p-6 text-center text-gray-600 text-sm">No hay pagos registrados.</p>
                    <?php endif; ?>
                </div>
                
                <a href="index.php" class="block w-full py-4 text-center text-xs text-gray-500 hover:text-white uppercase tracking-widest border-t border-[#222]">Volver</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>