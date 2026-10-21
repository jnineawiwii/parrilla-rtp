<?php $user = Auth::user(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/paleta.css">
    <link rel="stylesheet" href="/css/estilos.css">
    <script src="/js/accesibilidad.js" defer></script>
    
    <!-- Chart.js (Gráficas) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    
    <!-- Plugin para mostrar porcentajes en las gráficas de dona -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0" defer></script>
    
    <script src="/js/app.js" defer></script>
    <?php if (empty($soloGraficas) && Auth::can('notificaciones.ver')): ?>
        <script src="/js/notificaciones.js" defer></script>
    <?php endif; ?>
    <?php if (!empty($cargarGraficas)): ?>
        <script src="/js/graficas.js" defer></script>
    <?php endif; ?>
    <?php if (!empty($cargarMensajes)): ?>
        <script src="/js/mensajes.js" defer></script>
    <?php endif; ?>

    <!-- ESTILOS PARA EL MENÚ DESPLEGABLE DE GRÁFICAS -->
    <style>
        .dropdown-graficas {
            position: relative;
            display: inline-block;
            /* Alineación vertical con los otros enlaces */
            vertical-align: middle; 
        }
        .dropdown-graficas .dropbtn {
            /* Forzamos el color blanco igual que las demás opciones */
            color: #ffffff; 
            text-decoration: none;
            padding: 10px 15px;
            display: inline-block;
            cursor: pointer;
            font-size: inherit;
            font-family: inherit;
        }
        .dropdown-graficas .dropbtn:hover {
            text-decoration: underline;
        }
        .dropdown-graficas .dropdown-content {
            display: none;
            position: absolute;
            /* Centramos el menú con respecto a la palabra Gráficas */
            left: 50%;
            transform: translateX(-50%);
            /* Le damos un ancho cómodo para que no se vea cortado */
            min-width: 180px; 
            background-color: var(--bg-card, #2b2b36);
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 6px;
            border: 1px solid var(--border-color, #444);
            margin-top: 10px; /* Un poco de espacio entre el botón y el menú */
            padding: 5px 0; /* Espaciado interno */
        }
        .dropdown-graficas .dropdown-content a {
            color: var(--text-color, #fff);
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            text-align: center; /* Centramos el texto de las opciones */
            font-size: 14px;
            transition: background 0.2s;
        }
        .dropdown-graficas .dropdown-content a:hover {
            background-color: var(--vino, #e91e63); 
            color: white;
        }
        .dropdown-graficas.show .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
    <div class="brand">
        <a href="/parrilla"><?= APP_NAME ?></a>
    </div>
    <nav class="topnav">

        <!-- ============================== -->
        <!-- LADO IZQUIERDO: navegación     -->
        <!-- ============================== -->
        <div class="nav-left">
            <?php if (Auth::can('parrilla.ver')): ?>
                <a href="/parrilla">Parrilla</a>
            <?php endif; ?>
            
            <!-- MENÚ DESPLEGABLE DE GRÁFICAS -->
            <?php if (Auth::can('metricas.ver')): ?>
                <div class="dropdown-graficas" id="menuGraficas">
                    <a href="javascript:void(0);" class="dropbtn" onclick="toggleGraficasMenu(event)">Gráficas ▼</a>
                    <div class="dropdown-content">
                        <a href="/graficas/externas">Gráficas Externas</a>
                        <a href="/graficas/internas">Gráficas Internas</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (Auth::can('usuarios.ver')): ?>
                <a href="/usuarios">Usuarios</a>
            <?php endif; ?>
            <?php if (Auth::can('mensajes.usar')): ?>
                <a href="/mensajes">Mensajes</a>
            <?php endif; ?>
        </div>

        <!-- ============================== -->
        <!-- LADO DERECHO: notif + user + ⚙ -->
        <!-- ============================== -->
        <div class="nav-right">

            <!-- Campanita de notificaciones -->
            <?php if (Auth::can('notificaciones.ver')): ?>
            <div class="dropdown-notif">
                <a href="#" id="campanita" title="Notificaciones">
                    🔔 <span id="notif-count" class="badge">0</span>
                </a>
                <div class="dropdown-content" id="notif-list">
                    <div class="header-notif">
                        <span>🔔 Notificaciones</span>
                        <a href="/notificaciones">Ver todas</a>
                    </div>
                    <div class="vacio">Sin notificaciones</div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Nombre de usuario -->
            <span class="user-info">
                <?= htmlspecialchars($user['nombre']) ?>
                <em>(<?= $user['rol'] ?>)</em>
            </span>

            <!-- Menú de accesibilidad (con logout dentro) -->
            <div class="accessibility-menu">
                <button type="button" class="accessibility-trigger" id="accessibilityToggle"
                        aria-expanded="false" aria-controls="accessibilityPanel" title="Ajustes de accesibilidad">
                    <span aria-hidden="true">☷</span><span class="sr-only">Ajustes de accesibilidad</span>
                </button>
                <section class="accessibility-panel" id="accessibilityPanel" aria-label="Configuración rápida" hidden>
                    <header class="accessibility-panel-header">
                        <strong>⚙ Configuración Rápida</strong>
                        <span id="accessibilitySaved">Auto-guardado</span>
                    </header>
                    <div class="accessibility-row">
                        <span>Tema</span>
                        <div class="accessibility-options" role="group" aria-label="Tema">
                            <button type="button" data-theme="light" aria-label="Tema claro" title="Tema claro">☀️</button>
                            <button type="button" data-theme="dark" aria-label="Tema oscuro" title="Tema oscuro">🌙</button>
                            <button type="button" data-theme="system" aria-label="Tema del sistema" title="Tema del sistema">▣</button>
                        </div>
                    </div>
                    <div class="accessibility-row">
                        <span>Tamaño</span>
                        <div class="accessibility-options font-size-options">
                            <button type="button" data-font-step="-1" aria-label="Reducir tamaño de texto">A-</button>
                            <output id="accessibilityFontSize">100%</output>
                            <button type="button" data-font-step="1" aria-label="Aumentar tamaño de texto">A+</button>
                        </div>
                    </div>
                    <label class="accessibility-row" for="highContrast">
                        <span>Alto contraste</span>
                        <input type="checkbox" id="highContrast" class="accessibility-switch">
                    </label>
                    <div class="accessibility-row">
                        <span>Color</span>
                        <div class="accessibility-options accent-options" role="group" aria-label="Color de acento">
                            <button type="button" data-accent="#27a34a" aria-label="Verde"></button>
                            <button type="button" data-accent="#a51d4b" aria-label="Vino"></button>
                            <button type="button" data-accent="#0878e8" aria-label="Azul"></button>
                            <button type="button" data-accent="#7045bd" aria-label="Violeta"></button>
                            <button type="button" data-accent="#f07818" aria-label="Naranja"></button>
                            <button type="button" data-accent="#22bfa0" aria-label="Turquesa"></button>
                            <button type="button" data-accent="#111111" aria-label="Negro"></button>
                        </div>
                    </div>
                    <button type="button" class="accessibility-reset" id="accessibilityReset">↻ Restablecer configuración</button>

                    <!-- ✅ LOGOUT AQUÍ, AL FINAL DEL PANEL -->
                    <form action="/logout" method="POST" class="logout-form">
                        <?= Csrf::campo() ?>
                        <button type="submit" class="logout-btn">Cerrar sesión</button>
                    </form>
                </section>
            </div>

        </div>
    </nav>
</header>

<!-- SCRIPT PARA QUE FUNCIONE EL DESPLEGABLE -->
<script>
    function toggleGraficasMenu(event) {
        event.preventDefault();
        document.getElementById("menuGraficas").classList.toggle("show");
    }

    // Cerrar el menú si se hace clic fuera de él
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-graficas");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>
<?php endif; ?>
<main class="container">
<?php if (empty($soloGraficas)): ?>
<?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-ok" role="alert"><?= htmlspecialchars($_SESSION['flash_ok']) ?></div>
    <?php unset($_SESSION['flash_ok']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-err" role="alert"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
<?php endif; ?>