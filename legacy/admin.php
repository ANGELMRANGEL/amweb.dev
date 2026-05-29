<?php
session_start();
require 'db.php';

// --- LOGICA DE SESION ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    if ($_POST['pin'] === $ADMIN_PIN) {
        $_SESSION['admin_auth'] = true;
        header("Location: admin.php"); // Recargar para limpiar POST
        exit;
    } else {
        $error = "PIN Incorrecto";
    }
}

// --- VISTA: LOGIN (Si no está logueado) ---
if (!isset($_SESSION['admin_auth'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Acceso Admin</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'League Spartan', sans-serif;
                background-color: #000;
                color: #e5e5e5;
            }

            .bg-carmesi {
                background-color: #D62B2E;
            }

            input:focus {
                outline: none;
                border-color: #D62B2E;
                box-shadow: 0 0 0 1px #D62B2E;
            }

            .animate-fade-in {
                animation: fadeIn 0.4s ease-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>

    <body class="min-h-screen flex flex-col items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="text-center mb-10">
                <img src="logo.png" alt="Logo" class="h-28 mx-auto mb-4 object-contain opacity-90">
                <h1 class="text-gray-500 uppercase tracking-[0.2em] text-xs">Zona Administrativa</h1>
            </div>

            <div class="bg-[#111] p-8 rounded-2xl border border-[#222] shadow-2xl">
                <form method="POST" class="flex flex-col gap-6">
                    <div>
                        <label class="block text-gray-500 text-xs font-bold uppercase mb-2 tracking-wide">PIN DE
                            SEGURIDAD</label>
                        <input type="password" name="pin" placeholder="••••" inputmode="numeric"
                            class="w-full bg-black border border-[#333] text-white text-center text-3xl py-4 rounded-xl transition-all"
                            required>
                    </div>

                    <?php if ($error): ?>
                        <div class="text-[#D62B2E] text-center text-sm font-bold animate-pulse"><?= $error ?></div>
                    <?php endif; ?>

                    <button type="submit"
                        class="bg-carmesi text-white font-bold py-4 rounded-xl uppercase tracking-widest hover:brightness-110 transition-all shadow-[0_0_15px_rgba(214,43,46,0.3)]">
                        Ingresar
                    </button>

                    <a href="index.php" class="text-center text-xs text-gray-600 mt-2 hover:text-white">Volver a
                        Consultas</a>
                </form>
            </div>
        </div>
    </body>

    </html>
    <?php exit;
}

// ==========================================
// --- VISTA: DASHBOARD (Si ya entró) ---
// ==========================================

// 1. Procesar Datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = false;
    // Ingreso
    if (isset($_POST['nuevo_pago'])) {
        $socio_id = !empty($_POST['socio_id']) ? $_POST['socio_id'] : null;
        $db->addPago($socio_id, $_POST['monto'], $_POST['nota'], $_POST['categoria']);
        $_SESSION['flash_msg'] = "Ingreso registrado.";
        $redirect = "ingresos";
    }
    // Gasto
    if (isset($_POST['nuevo_gasto'])) {
        try {
            $db->pdo->exec("CREATE TABLE IF NOT EXISTS gastos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                descripcion VARCHAR(255) NOT NULL,
                monto DECIMAL(10,2) NOT NULL,
                fecha DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");
            $db->addGasto($_POST['descripcion'], $_POST['monto']);
            $_SESSION['flash_msg'] = "Gasto de $" . number_format($_POST['monto'], 2) . " registrado correctamente.";
        } catch (Exception $e) {
            $_SESSION['flash_msg'] = "ERROR CRÍTICO: No se pudo guardar el gasto. " . $e->getMessage();
        }
        $redirect = "egresos";
    }
    // Nuevo Socio
    if (isset($_POST['nuevo_socio'])) {
        try {
            $db->pdo->exec("ALTER TABLE socios ADD COLUMN IF NOT EXISTS telefono VARCHAR(50)");
            $db->pdo->exec("ALTER TABLE socios ADD COLUMN IF NOT EXISTS cuota DECIMAL(10,2) DEFAULT 0");
            if ($db->addSocio($_POST['nombre'], $_POST['telefono'])) {
                $_SESSION['flash_msg'] = "Jugador creado.";
            } else {
                $_SESSION['flash_msg'] = "Error al crear.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_msg'] = "Error DB: " . $e->getMessage();
        }
        $redirect = "socios";
    }
    // Eliminar Pago
    if (isset($_POST['eliminar_pago'])) {
        $db->deletePago($_POST['id']);
        $_SESSION['flash_msg'] = "Ingreso eliminado.";
        $redirect = "historial";
    }
    // Eliminar Gasto
    if (isset($_POST['eliminar_gasto'])) {
        $db->deleteGasto($_POST['id']);
        $_SESSION['flash_msg'] = "Gasto eliminado.";
        $redirect = "historial";
    }
    // Actualizar Cuota Global
    if (isset($_POST['actualizar_cuota_global'])) {
        $db->setAjuste('cuota_minima', $_POST['cuota_minima']);
        $_SESSION['flash_msg'] = "Aporte mínimo actualizado.";
        $redirect = "cuotas";
    }
    // Editar Socio
    if (isset($_POST['editar_socio'])) {
        $db->updateSocio($_POST['id'], $_POST['nombre'], $_POST['telefono']);
        $_SESSION['flash_msg'] = "Socio actualizado.";
        $redirect = "socios";
    }
    // Editar Pago
    if (isset($_POST['editar_pago'])) {
        $socio_id = !empty($_POST['socio_id']) ? $_POST['socio_id'] : null;
        $db->updatePago($_POST['id'], $socio_id, $_POST['monto'], $_POST['nota'], $_POST['categoria']);
        $_SESSION['flash_msg'] = "Ingreso actualizado.";
        $redirect = "historial";
    }
    // Editar Gasto
    if (isset($_POST['editar_gasto'])) {
        $db->updateGasto($_POST['id'], $_POST['descripcion'], $_POST['monto']);
        $_SESSION['flash_msg'] = "Gasto actualizado.";
        $redirect = "historial";
    }

    if ($redirect) {
        header("Location: admin.php?tab=" . $redirect);
        exit;
    }
}

// Mensaje Flash
$msg = $_SESSION['flash_msg'] ?? "";
unset($_SESSION['flash_msg']);

// 2. Calcular Balances
$balance = $db->getBalance();
$total_ingresos = $balance['ingresos'];
$total_egresos = $balance['egresos'];
$saldo_actual = $balance['saldo'];

// 3. Cargar Datos
$socios = $db->getSocios();
$historial = $db->getHistorial(40);
$cuota_minima_global = $db->getAjuste('cuota_minima');
$resumen_socios = $db->getResumenSocios();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Dashboard Club</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'League Spartan', sans-serif;
            background-color: #000;
            color: #ddd;
        }

        input,
        select {
            background-color: #111;
            border: 1px solid #333;
            color: white;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #D62B2E;
        }

        .tab-btn.active {
            background-color: #D62B2E;
            color: white;
            border-color: #D62B2E;
        }

        .tab-btn {
            background-color: #111;
            color: #666;
            border: 1px solid #222;
        }

        .modal {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body class="p-4 max-w-xl mx-auto pb-24">

    <div class="mb-6 animate-fade-in">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-gray-500 text-xs uppercase tracking-widest">Panel de Control</h1>
            <a href="?logout=1" class="text-xs text-[#D62B2E] font-bold">CERRAR SESIÓN</a>
        </div>

        <div class="bg-[#111] rounded-2xl border border-[#222] p-6 text-center relative overflow-hidden shadow-2xl">
            <div class="relative z-10">
                <p class="text-gray-500 text-[10px] uppercase tracking-widest mb-1">Caja Chica (Saldo)</p>
                <h2 class="text-5xl font-bold text-white tracking-tighter mb-4">$<?= number_format($saldo_actual, 2) ?>
                </h2>

                <div class="h-24 w-full mb-4">
                    <canvas id="balanceChart"></canvas>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t border-[#222] pt-4">
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase">Entradas</p>
                        <p class="text-gray-300 font-bold text-sm">+$<?= number_format($total_ingresos, 2) ?></p>
                    </div>
                    <div class="border-l border-[#222]">
                        <p class="text-[10px] text-gray-500 uppercase">Salidas</p>
                        <p class="text-[#D62B2E] font-bold text-sm">-$<?= number_format($total_egresos, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($msg)
        echo "<div class='bg-[#222] text-white text-center text-xs uppercase tracking-widest p-3 rounded-lg mb-6 border border-[#333]'>$msg</div>"; ?>

    <div class="grid grid-cols-5 gap-1 mb-6">
        <button onclick="setTab('ingresos')" id="btn-ingresos"
            class="tab-btn active py-3 rounded-l-lg text-[10px] font-bold uppercase transition-all">Ingreso</button>
        <button onclick="setTab('egresos')" id="btn-egresos"
            class="tab-btn py-3 text-[10px] font-bold uppercase transition-all">Gasto</button>
        <button onclick="setTab('historial')" id="btn-historial"
            class="tab-btn py-3 text-[10px] font-bold uppercase transition-all">Balance</button>
        <button onclick="setTab('socios')" id="btn-socios"
            class="tab-btn py-3 text-[10px] font-bold uppercase transition-all">Socios</button>
        <button onclick="setTab('cuotas')" id="btn-cuotas"
            class="tab-btn py-3 rounded-r-lg text-[10px] font-bold uppercase transition-all">Cuotas</button>
    </div>

    <div id="tab-ingresos" class="tab-content">
        <form method="POST" class="bg-[#111] p-5 rounded-2xl border border-[#222]">
            <input type="hidden" name="nuevo_pago" value="1">
            <h3 class="text-white font-bold text-xs uppercase mb-4 tracking-widest border-b border-[#222] pb-2">
                Registrar Ingreso</h3>
            <?php if (!$socios): ?>
                <p class="text-xs text-gray-500">Primero registra jugadores en la pestaña SOCIOS.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1 uppercase">Tipo de Ingreso</label>
                            <select name="categoria" class="w-full p-4 rounded-xl text-sm appearance-none bg-black"
                                onchange="toggleSocioSelect(this.value)">
                                <option value="Aporte">Aporte (Socio)</option>
                                <option value="Patrocinio">Patrocinio</option>
                                <option value="Siembra">Siembra</option>
                            </select>
                        </div>
                        <div id="socio-select-container">
                            <label class="block text-[10px] text-gray-500 mb-1 uppercase">Jugador/Socio</label>
                            <div class="relative">
                                <select name="socio_id" class="w-full p-4 rounded-xl text-sm appearance-none bg-black">
                                    <option value="">- Seleccionar -</option>
                                    <?php foreach ($socios as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                    ▼</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-1/2">
                            <label class="block text-[10px] text-gray-500 mb-1 uppercase">Monto ($)</label>
                            <input type="number" step="0.01" name="monto"
                                class="w-full p-4 rounded-xl text-lg font-bold text-center bg-black" placeholder="0.00"
                                required>
                        </div>
                        <div class="w-1/2">
                            <label class="block text-[10px] text-gray-500 mb-1 uppercase">Motivo</label>
                            <input type="text" name="nota" class="w-full p-4 rounded-xl text-sm bg-black"
                                placeholder="Ej: Marzo">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full bg-white text-black font-bold py-4 rounded-xl uppercase tracking-widest hover:bg-gray-200 mt-2 text-xs">
                        Guardar Ingreso
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div id="tab-egresos" class="tab-content hidden">
        <form method="POST" class="bg-[#190a0a] p-5 rounded-2xl border border-[#D62B2E]/30 relative overflow-hidden">
            <input type="hidden" name="nuevo_gasto" value="1">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#D62B2E] opacity-5 rounded-full blur-2xl -mr-10 -mt-10">
            </div>

            <h3
                class="text-[#D62B2E] font-bold text-xs uppercase mb-4 tracking-widest border-b border-[#D62B2E]/20 pb-2">
                Registrar Gasto</h3>
            <div class="space-y-4 relative z-10">
                <div>
                    <label class="block text-[10px] text-[#D62B2E] mb-1 uppercase">¿En qué se gastó?</label>
                    <input type="text" name="descripcion"
                        class="w-full p-4 rounded-xl text-sm bg-black border-[#D62B2E]/30 focus:border-[#D62B2E] placeholder-red-900/50"
                        placeholder="Ej: Arbitraje, Agua..." required>
                </div>
                <div>
                    <label class="block text-[10px] text-[#D62B2E] mb-1 uppercase">Monto a retirar ($)</label>
                    <input type="number" step="0.01" name="monto"
                        class="w-full p-4 rounded-xl text-2xl font-bold text-center bg-black border-[#D62B2E]/30 text-[#D62B2E]"
                        placeholder="0.00" required>
                </div>
                <button type="submit"
                    class="w-full bg-[#D62B2E] text-white font-bold py-4 rounded-xl uppercase tracking-widest hover:bg-red-700 mt-2 shadow-[0_0_20px_rgba(214,43,46,0.2)] text-xs">
                    Registrar Gasto
                </button>
            </div>
        </form>
    </div>

    <div id="tab-socios" class="tab-content hidden">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">Plantilla de Socios</h3>
            <button onclick="openAddSocio()"
                class="bg-white text-black px-4 py-2 rounded-lg text-[10px] font-bold uppercase hover:bg-gray-200 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Socio
            </button>
        </div>

        <div class="bg-[#111] rounded-2xl border border-[#222] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-black/50 text-gray-500 uppercase text-[9px] tracking-tighter">
                            <th class="p-4">Socio</th>
                            <th class="p-4 text-center">Aportado</th>
                            <th class="p-4 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#222]">
                        <?php foreach ($resumen_socios as $s): ?>
                            <?php
                            $diff = $s['total_pagado'] - $cuota_minima_global;
                            $color = $diff >= 0 ? 'text-green-500' : 'text-[#D62B2E]';
                            ?>
                            <tr class="hover:bg-[#151515] transition cursor-pointer"
                                onclick="openEditSocio(<?= htmlspecialchars(json_encode($s)) ?>)">
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-white"><?= htmlspecialchars($s['nombre']) ?></p>
                                        <?php if ($s['telefono']):
                                            $mora = $cuota_minima_global - $s['total_pagado'];
                                            if ($mora > 0):
                                                $tel_clean = preg_replace('/[^0-9]/', '', $s['telefono']);
                                                $monto_fmt = number_format($mora, 2);
                                                $msj = sprintf(
                                                    "Para: %s.\nDe: Tesorería.\n\nEstimado socio de Legacy FC, a la fecha de hoy presenta una mora de colaboración con respecto al equipo de $%s. Puedes ir abonando.\n\n(Si hay algún error, no dudes en comunicarlo para actualizar el saldo).",
                                                    $s['nombre'],
                                                    $monto_fmt
                                                );
                                                $wa_link = "https://wa.me/" . $tel_clean . "?text=" . urlencode($msj);
                                                ?>
                                                <a href="<?= $wa_link ?>" target="_blank" onclick="event.stopPropagation()"
                                                    class="hover:scale-125 transition-transform">
                                                    <img src="wicon.svg" class="h-4 w-4" alt="WA">
                                                </a>
                                            <?php endif; endif; ?>
                                    </div>
                                    <p class="text-[9px] text-gray-600"><?= $s['telefono'] ?: 'Sin tlf' ?></p>
                                </td>
                                <td class="p-4 text-center font-mono font-bold">$<?= number_format($s['total_pagado'], 2) ?>
                                </td>
                                <td class="p-4 text-right font-mono font-bold <?= $color ?>">
                                    <?= $diff >= 0 ? '+' : '' ?>$<?= number_format($diff, 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-historial" class="tab-content hidden">
        <div class="bg-[#111] rounded-2xl border border-[#222] overflow-hidden">
            <div class="p-4 bg-[#0a0a0a] border-b border-[#222] flex flex-col gap-3">
                <h3 class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">Movimientos Recientes</h3>
                <div class="relative">
                    <input type="text" id="filter-balance" onkeyup="filterBalance()"
                        placeholder="Buscar por nombre o descripción..."
                        class="w-full bg-black border border-[#222] text-xs p-3 rounded-xl pl-10 focus:border-[#D62B2E] transition-all">
                    <div class="absolute left-3 top-3 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-[#222] max-h-[500px] overflow-y-auto" id="balance-list">
                <?php foreach ($historial as $h): ?>
                    <div class="p-4 flex justify-between items-center hover:bg-[#151515] transition group balance-item cursor-pointer"
                        onclick='openEditMovimiento(<?= json_encode($h, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                        <div class="flex items-center gap-3">
                            <form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este registro?')"
                                class="transition-opacity">
                                <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                <input type="hidden" name="<?= $h['tipo'] === 'ingreso' ? 'eliminar_pago' : 'eliminar_gasto' ?>" value="1">
                                <button type="submit"
                                    class="text-[#D62B2E] hover:text-red-400 text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            <div>
                                <p class="text-white text-sm font-bold truncate max-w-[160px]">
                                    <?= htmlspecialchars($h['descripcion']) ?></p>
                                <p class="text-gray-600 text-[10px] uppercase tracking-wide">
                                    <span class="text-[#D62B2E] font-bold"><?= $h['categoria'] ?></span> •
                                    <?= $h['tipo'] === 'ingreso' ? ($h['autor'] ?? 'Externo') : 'GASTO ADMIN' ?> •
                                    <?= date('d M', strtotime($h['fecha'])) ?>
                                </p>
                            </div>
                        </div>
                        <span class="font-bold font-mono <?= $h['tipo'] === 'ingreso' ? 'text-white' : 'text-[#D62B2E]' ?>">
                            <?= $h['tipo'] === 'ingreso' ? '+' : '-' ?>$<?= number_format($h['monto'], 2) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="tab-cuotas" class="tab-content hidden">
        <form method="POST" class="bg-[#111] p-8 rounded-3xl border border-[#D62B2E]/20 relative overflow-hidden">
            <input type="hidden" name="actualizar_cuota_global" value="1">
            <!-- Decoración -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#D62B2E] opacity-5 rounded-full blur-3xl"></div>

            <div class="relative z-10 text-center">
                <h3 class="text-white font-bold text-lg uppercase mb-2 tracking-widest">Aporte Mínimo Global</h3>
                <p class="text-xs text-gray-500 mb-8 italic">Este monto es el objetivo base que deben cumplir todos los
                    socios del club.</p>

                <div class="max-w-xs mx-auto mb-8">
                    <label class="block text-[10px] text-[#D62B2E] mb-2 uppercase font-bold tracking-widest">Monto
                        Determinado por la Junta ($)</label>
                    <input type="number" step="0.01" name="cuota_minima" value="<?= $cuota_minima_global ?>"
                        class="w-full p-6 rounded-2xl text-4xl font-bold text-center bg-black border-2 border-[#222] focus:border-[#D62B2E] transition-all"
                        placeholder="0.00">
                </div>

                <button type="submit"
                    class="bg-[#D62B2E] text-white font-bold py-4 px-10 rounded-xl uppercase tracking-widest hover:scale-105 active:scale-95 transition-all shadow-[0_10px_20px_rgba(214,43,46,0.3)] text-xs">
                    Establecer para todo el club
                </button>
            </div>
        </form>
    </div>



    <script>
        // Manejo de pestañas por URL
        window.onload = function () {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            if (tab) setTab(tab);
        };

        function setTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active', 'bg-[#D62B2E]', 'text-white', 'border-[#D62B2E]');
                el.classList.add('bg-[#111]', 'text-[#666]', 'border-[#222]');
            });
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            const btn = document.getElementById('btn-' + tabId);
            if (btn) {
                btn.classList.remove('bg-[#111]', 'text-[#666]', 'border-[#222]');
                btn.classList.add('active', 'bg-[#D62B2E]', 'text-white', 'border-[#D62B2E]');
            }
        }

        // Prevención de Doble Envío
        document.querySelectorAll('form').forEach(form => {
            form.onsubmit = function () {
                const btn = form.querySelector('button[type="submit"]') || form.querySelector('button[name]');
                if (btn) {
                    setTimeout(() => {
                        btn.disabled = true;
                        btn.innerHTML = "Procesando...";
                        btn.style.opacity = "0.5";
                    }, 50);
                }
            };
        });

        // Modal de Socios
        function openAddSocio() {
            document.getElementById('modal-title').innerText = "Nuevo Socio";
            document.getElementById('socio-id').value = "";
            document.getElementById('socio-nombre').value = "";
            document.getElementById('socio-telefono').value = "";
            document.getElementById('edit-only-fields').classList.add('hidden');
            document.getElementById('btn-socio-submit').name = "nuevo_socio";
            document.getElementById('btn-socio-submit').innerText = "Crear Socio";
            document.getElementById('modal-socio').classList.remove('hidden');
        }

        function openEditSocio(socio) {
            document.getElementById('modal-title').innerText = "Editar Socio";
            document.getElementById('socio-id').value = socio.id;
            document.getElementById('socio-nombre').value = socio.nombre;
            document.getElementById('socio-telefono').value = socio.telefono || "";
            document.getElementById('btn-socio-submit').name = "editar_socio";
            document.getElementById('btn-socio-submit').innerText = "Guardar Cambios";
            document.getElementById('modal-socio').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal-socio').classList.add('hidden');
        }

        // Modal de Movimientos
        function openEditMovimiento(mov) {
            document.getElementById('mov-id').value = mov.id;
            document.getElementById('mov-monto').value = mov.monto;

            const ingresoFields = document.getElementById('mov-ingreso-fields');
            const egresoFields = document.getElementById('mov-egreso-fields');
            const btnSubmit = document.getElementById('btn-mov-submit');
            const notaContainer = document.getElementById('mov-nota-container');

            if (mov.tipo === 'ingreso') {
                document.getElementById('mov-modal-title').innerText = "Editar Ingreso";
                ingresoFields.classList.remove('hidden');
                egresoFields.classList.add('hidden');
                notaContainer.classList.remove('hidden');
                document.getElementById('mov-socio-id').value = mov.socio_id || "";
                document.getElementById('mov-categoria').value = mov.categoria || "Aporte";
                document.getElementById('mov-nota').value = mov.descripcion || "";
                btnSubmit.name = "editar_pago";
            } else {
                document.getElementById('mov-modal-title').innerText = "Editar Gasto";
                ingresoFields.classList.add('hidden');
                egresoFields.classList.remove('hidden');
                notaContainer.classList.add('hidden');
                document.getElementById('mov-descripcion').value = mov.descripcion || "";
                btnSubmit.name = "editar_gasto";
            }

            document.getElementById('modal-movimiento').classList.remove('hidden');
        }

        function closeMovimientoModal() {
            document.getElementById('modal-movimiento').classList.add('hidden');
        }

        function toggleSocioSelect(val) {
            const container = document.getElementById('socio-select-container');
            if (val === 'Aporte') {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            } else {
                container.style.opacity = '0.3';
                container.style.pointerEvents = 'none';
                container.querySelector('select').value = '';
            }
        }

        // Gráfica de Balance
        const ctx = document.getElementById('balanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_reverse(array_map(function ($h) {
                    return date('d M', strtotime($h['fecha'])); }, array_slice($historial, 0, 10)))) ?>,
                datasets: [{
                    label: 'Balance',
                    data: <?= json_encode(array_reverse(array_map(function ($h) {
                        return $h['monto']; }, array_slice($historial, 0, 10)))) ?>,
                    borderColor: '#D62B2E',
                    borderWidth: 3,
                    tension: 0,
                    fill: false,
                    pointRadius: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });

        function filterBalance() {
            const query = document.getElementById('filter-balance').value.toLowerCase();
            const items = document.querySelectorAll('.balance-item');

            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
    <!-- MODAL SOCIOS -->
    <div id="modal-socio" class="modal fixed inset-0 z-[5000] flex items-center justify-center p-4 hidden">
        <div class="bg-[#111] w-full max-w-sm rounded-3xl border border-[#222] shadow-2xl p-6 relative">
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-500 hover:text-white text-2xl">&times;</button>
            <h3 id="modal-title"
                class="text-white font-bold text-xs uppercase mb-6 tracking-widest border-b border-[#222] pb-2">Gestión
                Socio</h3>
            <form method="POST" id="form-socio" class="space-y-4">
                <input type="hidden" name="id" id="socio-id">
                <div>
                    <label class="block text-[10px] text-gray-500 mb-1 uppercase tracking-tighter">Nombre
                        Completo</label>
                    <input type="text" name="nombre" id="socio-nombre" class="w-full p-4 rounded-xl text-sm bg-black"
                        required>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-500 mb-1 uppercase tracking-tighter">Teléfono</label>
                    <input type="tel" name="telefono" id="socio-telefono"
                        class="w-full p-4 rounded-xl text-sm bg-black">
                </div>
                <button type="submit" name="editar_socio" id="btn-socio-submit"
                    class="w-full bg-[#D62B2E] text-white font-bold py-4 rounded-xl uppercase tracking-widest mt-4 text-xs">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL MOVIMIENTOS -->
    <div id="modal-movimiento" class="modal fixed inset-0 z-[5000] flex items-center justify-center p-4 hidden">
        <div class="bg-[#111] w-full max-w-sm rounded-3xl border border-[#222] shadow-2xl p-6 relative">
            <button onclick="closeMovimientoModal()"
                class="absolute top-4 right-4 text-gray-500 hover:text-white text-2xl">&times;</button>
            <h3 id="mov-modal-title"
                class="text-white font-bold text-xs uppercase mb-6 tracking-widest border-b border-[#222] pb-2">Editar
                Registro</h3>
            <form method="POST" id="form-movimiento" class="space-y-4">
                <input type="hidden" name="id" id="mov-id">
                <div id="mov-ingreso-fields" class="hidden space-y-4">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1 uppercase">Socio/Origen</label>
                        <select name="socio_id" id="mov-socio-id" class="w-full p-4 rounded-xl text-sm bg-black">
                            <option value="">Externo (Patrocinio/Siembra)</option>
                            <?php foreach ($socios as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-1 uppercase">Categoría</label>
                        <select name="categoria" id="mov-categoria" class="w-full p-4 rounded-xl text-sm bg-black">
                            <option value="Aporte">Aporte</option>
                            <option value="Patrocinio">Patrocinio</option>
                            <option value="Siembra">Siembra</option>
                        </select>
                    </div>
                </div>
                <div id="mov-egreso-fields" class="hidden">
                    <label class="block text-[10px] text-gray-500 mb-1 uppercase">Descripción</label>
                    <input type="text" name="descripcion" id="mov-descripcion"
                        class="w-full p-4 rounded-xl text-sm bg-black">
                </div>
                <div class="flex gap-3">
                    <div class="w-1/2">
                        <label class="block text-[10px] text-gray-500 mb-1 uppercase">Monto ($)</label>
                        <input type="number" step="0.01" name="monto" id="mov-monto"
                            class="w-full p-4 rounded-xl text-sm bg-black" required>
                    </div>
                    <div id="mov-nota-container" class="w-1/2">
                        <label class="block text-[10px] text-gray-500 mb-1 uppercase">Motivo</label>
                        <input type="text" name="nota" id="mov-nota" class="w-full p-4 rounded-xl text-sm bg-black">
                    </div>
                </div>
                <button type="submit" name="editar_pago" id="btn-mov-submit"
                    class="w-full bg-[#D62B2E] text-white font-bold py-4 rounded-xl uppercase tracking-widest mt-4 text-xs">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</body>

</html>