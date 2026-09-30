<script>
    fetch('../controlador/api_tokens.php/verificar')
        //al "mensaje" que token le envía a su API, lo llama response y lo decodifica de json a java
        .then(response => response.json())
        //al resultado lo llama data, con data hace todas las operaciones
        .then(data => {
            if (data.usr_rol !== 'administrador') {
                window.location.href = 'login.html';
                return;
            }

            document.getElementById('bienvenida').textContent = `SiGeRu - Oficina de ${data.usr_name}`;

            console.log('Usuario autorizado');
        })
        .catch(error => {
            console.error(error);
            window.location.href = 'login.html';
        });
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="author" content="GudEngine">
    <meta name="description" content="Backoffice e interfaz de administrador del sistema SiGeRu">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GudEngine - Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="stylesheet" href="../css/index.css">

</head>

<body>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <span class="navbar-brand fw-bold" id="bienvenida">
                SiGeRu - Oficina de administracion
            </span>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-light btn-sm" id="btn-theme">
                    <i class="bi bi-moon-stars"></i> Modo Oscuro
                </button>
                <!-- Botón para destruir la sesión y salir -->
                <button type="button" class="btn btn-danger btn-sm" id="btn-logout">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row g-4">

            <!--FORMULARIOS-->
            <div class="col-12 col-lg-4">
                <div class="card p-4 rounded shadow-sm">

                    <div class="row g-2 mb-3">
                        <!-- Selector de Entidad -->
                        <div class="col-12">
                            <!-- Cambiado a col-12 para que ocupe todo el ancho disponible de la tarjeta -->
                            <label class="form-label small fw-bold text-primary mb-1">1. Seleccione Gestión</label>
                            <select id="selector_entidad" class="form-select border-primary fw-bold"
                                onchange="cambiarEntidad()">
                                <option value="usuarios">Gestión de Personal (Usuarios)</option>
                                <option value="contenedores">Gestión de Infraestructura (Contenedores)</option>
                                <option value="camiones">Gestión de la flota de camiones</option>
                                <option value="rutas">Gestión de rutas</option>
                                <option value="cuadrillas">Gestión de cuadrillas</option>
                                <option value="acopios">Gestión de centros de acopio</option>
                                <option value="herramientas">Gestión de herramientas</option>
                                <option value="incidencias">Gestión de incidencias</option>

                            </select>
                        </div>

                        <!-- Selector de Operación -->
                        <div class="col-12 mb-4">
                            <label class="form-label small fw-bold text-primary mb-1">2. Seleccione Operación</label>
                            <select id="selector_operacion" class="form-select border-primary fw-bold"
                                onchange="cambiarFormularios()">
                                <option value="registro_usuarios">Registrar Nuevo Usuario</option>
                                <option value="modificacion_usuarios">Modificar Usuario</option>
                                <option value="eliminacion_usuarios">Eliminar Usuario</option>
                            </select>
                        </div>
                    </div>

                    <!--REGISTRAR usuarios-->
                    <form id="form_registro_usuarios">
                        <h5 class="fw-bold mb-3 text-success">Alta de Personal</h5>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cédula de Identidad</label>
                            <input type="text" id="ci" class="form-control" maxlength="8" placeholder="ej. 12345678"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" id="name" class="form-control" placeholder="ej. Juan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" id="apellido" class="form-control" placeholder="ej. Pérez" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" id="email" class="form-control" placeholder="juan@hotmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Teléfono de Contacto</label>
                            <input type="text" id="telefono" class="form-control" maxlength="9"
                                placeholder="ej. 99123456,NO 099123456 ">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Edad</label>
                            <input type="text" id="edad" class="form-control" maxlength="2" placeholder="ej. 22 ">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Rol Operativo</label>
                            <select id="rol" class="form-select" required>
                                <option value="" disabled selected>Seleccione un rol...</option>
                                <option value="recolector">Cuadrilla de Recolección</option>
                                <option value="operario_acopio">Administrador de centro de acopio</option>
                                <option value="operario_vertedero">Administrador de vertedero</option>
                                <option value="operario_taller">Administrador de taller</option>

                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold">Confirmar Registro</button>
                    </form>
                    <!--modificar usuarios-->
                    <form id="form_modificacion_usuarios" class="d-none">
                        <h5 class="fw-bold mb-3 text-warning">Modificar Datos</h5>
                        <p class="fw-bold small">>La CI no se puede cambiar.</p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cédula del usuario a modificar</label>
                            <input type="text" id="mod_ci" class="form-control border-warning" maxlength="8"
                                placeholder="Ingrese CI existente" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nuevo Nombre</label>
                            <input type="text" id="mod_name" class="form-control" placeholder="Nombre actualizado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nuevo Apellido</label>
                            <input type="text" id="mod_apellido" class="form-control"
                                placeholder="Apellido actualizado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nuevo Email</label>
                            <input type="email" id="mod_email" class="form-control" placeholder="Email actualizado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nuevo Teléfono</label>
                            <input type="text" id="mod_telefono" class="form-control" maxlength="9"
                                placeholder="Teléfono actualizado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nueva edad</label>
                            <input type="text" id="mod_edad" class="form-control" maxlength="2" placeholder="ej. 22 ">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Nuevo Rol</label>
                            <select id="mod_rol" class="form-select">
                                <option value="recolector">Cuadrilla de Recolección</option>
                                <option value="operario_acopio">Administrador de centro de acopio</option>
                                <option value="operario_vertedero">Administrador de Vertedero</option>
                                <option value="operario_taller">Administrador de taller</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">Guardar Cambios</button>
                    </form>

                    <!--ELIMINAR usuarios-->

                    <form id="form_eliminacion_usuarios" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja del Sistema</h5>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Cédula de Identidad del Funcionario</label>
                            <input type="text" id="cedula_eliminar" class="form-control border-danger form-control-lg"
                                maxlength="8" placeholder="ej. 12345678" required>
                            <p class="fw-bold small"> Atención: Esta acción dará de baja al usuario del sistema.</p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">Eliminar Definitivamente</button>
                    </form>

                    <!-- FORMULARIO: ALTA DE CONTENEDORES -->
                    <form id="form_registro_contenedores" class="d-none">
                        <h5 class="fw-bold mb-3 text-success">Alta de contenedor de residuos</h5>
                        <p class="small">Registre la ubicación y el estado inicial de una nueva unidad en la vía pública
                            o en reserva.</p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Calle / Ubicación</label>
                            <!-- atributo maxlength, pone un máximo de caracteres  VARCHAR(29) -->
                            <input type="text" id="cont_calle" class="form-control" maxlength="29"
                                placeholder="Ej: Av. 18 de Julio" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Tipo de contenedor</label>
                            <select id="cont_tipo" class="form-select">
                                <option value="mezclados">Residuos mezclados</option>
                                <option value="reciclaje">Residuos reciclables</option>
                                <option value="volqueta">Volqueta</option>
                            </select>
                        </div>

                        <!-- Estado Inicial -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Estado del Contenedor</label>
                            <select id="cont_estado" class="form-select">
                                <option value="funcional">Funcional</option>
                                <option value="roto">Roto</option>
                                <option value="desbordado">Desbordado</option>
                                <option value="reserva">En reserva</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Ubicación Geográfica (Seleccione en el mapa)</label>
                            <p class="small text-muted mb-2">Haga clic en el mapa o arrastre el marcador para fijar las
                                coordenadas exactas.</p>

                            <!-- Contenedor del Mapa Selector -->
                            <div id="mapa_selector" style="height: 250px; border-radius: 8px;" class="border mb-3">
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label extra-small text-muted fw-bold">Latitud</label>
                                    <input type="text" id="cont_latitud" class="form-control bg-light" value="0.000000"
                                        readonly required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label extra-small text-muted fw-bold">Longitud</label>
                                    <input type="text" id="cont_longitud" class="form-control bg-light" value="0.000000"
                                        readonly required>
                                </div> <!--Notarán el readonly, obviamente significa que es de solo lectura-->
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold text-white">Registrar
                            Contenedor</button>
                    </form>

                    <!--Modificación de contenedores-->
                    <form id="form_modificacion_contenedores" class="d-none">
                        <h5 class="fw-bold mb-3 text-warning">Modificar Contenedor</h5>
                        <p class="fw-bold small text-muted">El ID no se puede modificar.</p>

                        <!-- 1. ID del Contenedor -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ID del Contenedor</label>
                            <input type="number" id="mod_cont_id" class="form-control border-warning"
                                placeholder="Ingrese ID existente" required>
                        </div>

                        <!-- 2. Estado -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Nuevo Estado</label>
                            <select id="mod_cont_estado" class="form-select">
                                <option value="funcional">Funcional</option>
                                <option value="roto">Roto</option>
                                <option value="desbordado">Desbordado</option>
                                <option value="reserva">En reserva</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Tipo de contenedor</label>
                            <select id="mod_cont_tipo" class="form-select">
                                <option value="mezclados">Residuos mezclados</option>
                                <option value="reciclaje">Residuos reciclables</option>
                                <option value="volqueta">Volqueta</option>
                            </select>
                        </div>

                        <!-- 3. Switch para Cambiar Ubicación -->
                        <div class="form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="check_mod_ubicacion">
                            <label class="small fw-bold" for="check_mod_ubicacion">Cambiar dirección y ubicación
                                geográfica</label>
                        </div>

                        <!-- Secciones de Calle y Mapa (deshabilitadas por defecto) -->
                        <div id="bloque_mod_ubicacion" class="opacity-50 pointer-events-none">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nueva Calle / Ubicación</label>
                                <input type="text" id="mod_cont_calle" class="form-control" maxlength="29"
                                    placeholder="Ej: Av. 18 de Julio" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Seleccionar en el mapa</label>

                                <!-- Mapa Selector de Modificación -->
                                <div id="mapa_selector_mod" style="height: 250px; border-radius: 8px;"
                                    class="border mb-3"></div>

                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label extra-small text-muted fw-bold">Latitud</label>
                                        <input type="text" id="mod_cont_latitud" class="form-control bg-light"
                                            value="0.000000" readonly disabled>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label extra-small text-muted fw-bold">Longitud</label>
                                        <input type="text" id="mod_cont_longitud" class="form-control bg-light"
                                            value="0.000000" readonly disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold text-dark mt-2">Guardar
                            Cambios</button>
                    </form>

                    <!--BAJA DE CONTENEDORES-->
                    <form id="form_eliminacion_contenedores" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja de Contenedor</h5>

                        <div class="mb-4">
                            <label for="cont_id_eliminar" class="form-label small fw-bold">ID del Contenedor</label>
                            <input type="number" id="cont_id_eliminar"
                                class="form-control border-danger form-control-lg" min="1" placeholder="ej. 1042"
                                required>
                            <p class="fw-bold small text-muted mt-2"> Atención: Esta acción eliminará permanentemente el
                                contenedor del mapa y del sistema de recolección.</p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">Eliminar Definitivamente</button>
                    </form>

                    <!--Alta de camioncitos-->
                    <form id="form_registro_camiones" class="d-none">
                        <h3 class="text-secondary mb-3">Registrar Nuevo Camión</h3>

                        <!-- Matrícula (Uruguaya, empieza con S) -->
                        <div class="mb-3">
                            <label for="cam_matricula" class="form-label fw-bold">Matrícula (Patente)</label>
                            <input type="text" class="form-control border-secondary" id="cam_matricula"
                                placeholder="SXX 1234" maxlength="8" required>
                            <p class="fw-bold small">Debe empezar con 'S' (Montevideo) seguido de 2 letras y 4 números
                                (ej: SAB 1234).</p>
                        </div>

                        <!-- Tipo de Camión (Selector) -->
                        <div class="mb-3">
                            <label for="cam_tipo" class="form-label fw-bold">Tipo de Camión</label>
                            <select class="form-select border-secondary" id="cam_tipo" required>
                                <option value="" disabled selected>Seleccione el tipo</option>
                                <option value="mezclados">Camión de residuos mezclados</option>
                                <option value="reciclaje">Camión de reciclaje</option>
                                <option value="volqueta">Camión de leva de volquetas</option>
                            </select>
                        </div>

                        <!-- Modelo (Selector) -->
                        <div class="mb-3">
                            <label for="cam_modelo" class="form-label fw-bold">Modelo / Marca</label>
                            <select class="form-select border-secondary" id="cam_modelo" required>
                                <option value="" disabled selected>Seleccione el modelo</option>
                                <option value="mercedes-benz">Mercedes-Benz</option>
                                <option value="caterpillar">Caterpillar</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="cam_estado" class="form-label fw-bold">Estado del Vehículo</label>
                            <select class="form-select border-secondary" id="cam_estado" required>
                                <option value="" disabled selected>Seleccione el estado</option>
                                <option value="funcional">Funcional</option>
                                <option value="roto">Necesita reparación</option>
                            </select>
                        </div>

                        <!-- Capacidad -->
                        <div class="mb-3">
                            <label for="cam_capacidad" class="form-label fw-bold">Digite la capacidad de carga del
                                camión en toneladas</label>
                            <input type="number" id="cam_capacidad" class="form-control border-secondary"
                                placeholder="ej: 15" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <!-- este tal bi es para poner íconos bonitos -->
                            <i class="bi bi-truck"></i> Registrar Camión
                        </button>
                    </form>

                    <!--Modificación de camioncitos-->
                    <form id="form_modificacion_camiones" class="d-none">
                        <h3 class="text-warning mb-3">Modificar Datos del Camión</h3>
                        <p class="fw-bold small text-muted">La matrícula no se puede modificar.</p>

                        <!-- Matrícula -->
                        <div class="mb-3">
                            <label for="mod_cam_matricula" class="form-label fw-bold">Matrícula (Patente)</label>
                            <input type="text" class="form-control border-warning" id="mod_cam_matricula"
                                placeholder="SXX 1234" maxlength="8" required>
                            <p class="fw-bold small">Ingrese la matrícula del camión que desea modificar (ej: SAB 1234).
                            </p>
                        </div>

                        <!-- Tipo de Camión (Selector) -->
                        <div class="mb-3">
                            <label for="mod_cam_tipo" class="form-label fw-bold">Nuevo tipo del camión</label>
                            <select class="form-select border-secondary" id="mod_cam_tipo" required>
                                <option value="" disabled selected>Seleccione el tipo</option>
                                <option value="mezclados">Camión de residuos mezclados</option>
                                <option value="reciclaje">Camión de reciclaje</option>
                                <option value="volqueta">Camión de leva de volquetas</option>
                            </select>
                        </div>

                        <!-- Modelo (Selector) -->
                        <div class="mb-3">
                            <label for="mod_cam_modelo" class="form-label fw-bold">Nuevo modelo / marca</label>
                            <select class="form-select border-secondary" id="mod_cam_modelo" required>
                                <option value="" disabled selected>Seleccione el modelo</option>
                                <option value="mercedes-benz">Mercedes-Benz</option>
                                <option value="caterpillar">Caterpillar</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="mod_cam_estado" class="form-label fw-bold">Nuevo estado del vehículo</label>
                            <select class="form-select border-secondary" id="mod_cam_estado" required>
                                <option value="" disabled selected>Seleccione el estado</option>
                                <option value="funcional">Funcional</option>
                                <option value="roto">Necesita reparación</option>
                            </select>
                        </div>

                        <!-- Capacidad -->
                        <div class="mb-3">
                            <label for="mod_cam_capacidad" class="form-label fw-bold">Digite la nueva capacidad de carga
                                del camión en toneladas</label>
                            <input type="number" id="mod_cam_capacidad" class="form-control border-secondary"
                                placeholder="ej: 15" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="bi bi-pencil-square"></i> Guardar cambios
                        </button>
                    </form>

                    <!--Baja de camioncitos-->
                    <form id="form_eliminacion_camiones" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja del Sistema</h5>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Matrícula del camión a eliminar</label>
                            <input type="text" class="form-control border-warning" id="matricula_eliminar"
                                placeholder="SXX 1234" maxlength="8" required>
                            <p class="fw-bold small"> Atención: Esta acción dará de baja al camión del sistema.</p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">Eliminar Definitivamente</button>
                    </form>
                    <!--Alta de rutas-->
                    <form id="form_registro_rutas" class="d-none">
                        <h3 class="text-secondary mb-3">Registrar Nueva Ruta de Recolección</h3>

                        <!-- Número de Ruta -->
                        <div class="mb-3">
                            <label for="ruta_id" class="form-label fw-bold">Número de Ruta</label>
                            <input type="number" class="form-control border-secondary" id="ruta_id"
                                placeholder="Ej: 101" min="1" required>
                            <p class="fw-bold small">Asigne un identificador numérico único para la ruta.</p>
                        </div>

                        <!-- Fecha de la Ruta -->
                        <div class="mb-3">
                            <label for="ruta_fecha" class="form-label fw-bold">Fecha de Planificación</label>
                            <input type="date" class="form-control border-secondary" id="ruta_fecha" required>
                        </div>

                        <!-- Selección de Camión (Dinámico) -->
                        <div class="mb-3">
                            <label for="ruta_camion" class="form-label fw-bold">Camión Asignado</label>
                            <select class="form-select border-secondary" id="ruta_camion" required>
                                <option value="" disabled selected>Cargando camiones disponibles...</option>
                            </select>
                        </div>

                        <!-- Selección de Contenedores (Texto separado por comas) -->
                        <div class="mb-3">
                            <label for="ruta_contenedores" class="form-label fw-bold">IDs de Contenedores
                                asignados</label>
                            <input type="text" class="form-control border-secondary" id="ruta_contenedores"
                                placeholder="Ej: 12, 15, 18" required>
                            <p class="fw-bold small">>Ingrese los IDs de los contenedores separados por comas.</p>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <i class="bi bi-geo-alt"></i> Crear y Asignar Ruta
                        </button>
                    </form>

                    <!--Alta de cuadrillas-->

                    <form id="form_registro_cuadrillas" class="d-none">
                        <h5 class="fw-bold mb-3 text-success">Alta de Cuadrillas</h5>

                        <!-- Camión (Selector) -->
                        <div class="mb-3">
                            <label for="cuad_cam" class="form-label fw-bold">Seleccione el camión de esta
                                cuadrilla</label>
                            <select class="form-select border-secondary" id="cuad_cam" required>
                                <option value="" disabled selected>Cargando camiones...</option>
                            </select>
                        </div>

                        <!-- Primer Recolector (Selector) -->
                        <div class="mb-3">
                            <label for="cuad_ci1" class="form-label small fw-bold">Primer recolector</label>
                            <select id="cuad_ci1" class="form-select" required>
                                <option value="" disabled selected>Cargando recolectores...</option>
                            </select>
                        </div>

                        <!-- Segundo Recolector (Selector) -->
                        <div class="mb-3">
                            <label for="cuad_ci2" class="form-label small fw-bold">Segundo recolector</label>
                            <select id="cuad_ci2" class="form-select" required>
                                <option value="" disabled selected>Cargando recolectores...</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <i class="bi bi-people-fill"></i> Crear Cuadrilla
                        </button>
                    </form>

                    <!--Modificación de cuadrilla-->
                    <!--
                    <form id="form_modificacion_cuadrillas" class="d-none">
                        <h5 class="fw-bold mb-3 text-success">Modificación de Cuadrillas</h5>
                         <p class="fw-bold small text-muted">El ID no se puede modificar.</p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">ID de la cuadrilla</label>
                            <input type="number" id="mod_cuad_id" class="form-control border-warning" placeholder="Ingrese ID existente" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cuad_cam" class="form-label fw-bold">Seleccione el  nuevo camión de esta cuadrilla</label>
                            <select class="form-select border-secondary" id="mod_cuad_cam" required>
                                <option value="" disabled selected>Cargando camiones...</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar Cambios</button>
                    </form>
                    -->

                    <!--BAJA DE CUADRILLAS-->
                    <form id="form_eliminacion_cuadrillas" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja de Cuadrilla</h5>

                        <div class="mb-4">
                            <label for="cuad_id_eliminar" class="form-label small fw-bold">ID de la cuadrilla</label>
                            <input type="number" id="cuad_id_eliminar"
                                class="form-control border-danger form-control-lg" min="1" placeholder="ej. 1042"
                                required>
                            <p class="fw-bold small text-muted mt-2"> Atención: Esta acción dará de baja la cuadrilla.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">Dar de baja</button>
                    </form>

                    <!-- ALTA DE CENTRO DE ACOPIO -->
                    <form id="form_registro_acopios" class="d-none">
                        <h5 class="fw-bold mb-3 text-success">Alta de centro de acopio</h5>
                        <p class="small text-muted mb-4">Ingrese los datos del nuevo establecimiento y sus métricas
                            iniciales de capacidad.</p>

                        <!-- 1. Dirección (Calle y Número de Puerta) -->
                        <div class="row mb-3">
                            <div class="col-8">
                                <label for="cent_calle" class="form-label small fw-bold">Calle</label>
                                <input type="text" id="cent_calle" class="form-control" maxlength="29"
                                    placeholder="Ej: Av. Italia" required>
                            </div>
                            <div class="col-4">
                                <label for="cent_num_puerta" class="form-label small fw-bold">N° Puerta</label>
                                <input type="number" id="cent_num_puerta" class="form-control" min="1"
                                    placeholder="Ej: 2514" required>
                            </div>
                        </div>

                        <!-- 2. Horarios de Funcionamiento -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="cent_hora_apertura" class="form-label small fw-bold">Hora de
                                    Apertura</label>
                                <input type="time" id="cent_hora_apertura" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label for="cent_hora_cierre" class="form-label small fw-bold">Hora de Cierre</label>
                                <input type="time" id="cent_hora_cierre" class="form-control" required>
                            </div>
                        </div>

                        <!-- 3. Capacidad y Llenado Inicial -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="cent_capacidad" class="form-label small fw-bold">Capacidad Máxima
                                    (m³)</label>
                                <input type="number" id="cent_capacidad" class="form-control" min="1"
                                    placeholder="Ej: 5000" required>
                            </div>
                            <div class="col-6">
                                <label for="cent_llenado" class="form-label small fw-bold">Nivel Llenado Inicial del
                                    establecimiento</label>
                                <input type="number" id="cent_llenado" class="form-control" min="0" value="0"
                                    placeholder="0" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold text-white">Registrar Centro de
                            Acopio</button>
                    </form>

                    <!--Modificación DE CENTROS DE ACOPIO-->
                    <form id="form_modificacion_acopios" class="d-none">
                        <h3 class="text-warning mb-3">Modificar Centro de Acopio</h3>
                        <p class="fw-bold small text-muted">El ID del centro de acopio no se puede modificar.</p>

                        <!-- ID del Centro de Acopio -->
                        <div class="mb-3">
                            <label for="mod_cent_id" class="form-label fw-bold">ID del Centro de Acopio</label>
                            <input type="number" id="mod_cent_id" class="form-control border-warning" min="1"
                                placeholder="Ej: 15" required>
                            <p class="fw-bold small">
                                Ingrese el ID del centro de acopio que desea modificar.
                            </p>
                        </div>

                        <!-- 1. Dirección -->
                        <div class="row mb-3">
                            <div class="col-8">
                                <label for="mod_cent_calle" class="form-label fw-bold">Nueva calle</label>
                                <input type="text" id="mod_cent_calle" class="form-control border-secondary"
                                    maxlength="29" placeholder="Ej: Av. Italia" required>
                            </div>

                            <div class="col-4">
                                <label for="mod_cent_num_puerta" class="form-label fw-bold"> N° Puerta</label>
                                <input type="number" id="mod_cent_num_puerta" class="form-control border-secondary"
                                    min="1" placeholder="Ej: 2514" required>
                            </div>
                        </div>

                        <!-- 2. Horarios -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="mod_cent_hora_apertura" class="form-label fw-bold">Nueva hora de apertura
                                </label>
                                <input type="time" id="mod_cent_hora_apertura" class="form-control border-secondary"
                                    required>
                            </div>

                            <div class="col-6">
                                <label for="mod_cent_hora_cierre" class="form-label fw-bold">Nueva hora de cierre
                                </label>
                                <input type="time" id="mod_cent_hora_cierre" class="form-control border-secondary"
                                    required>
                            </div>
                        </div>

                        <!-- 3. Capacidad y llenado -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <label for="mod_cent_capacidad" class="form-label fw-bold">
                                    Nueva capacidad máxima (m³)
                                </label>
                                <input type="number" id="mod_cent_capacidad" class="form-control border-secondary"
                                    min="1" placeholder="Ej: 5000" required>
                            </div>

                            <div class="col-6">
                                <label for="mod_cent_llenado" class="form-label fw-bold">
                                    Nuevo nivel de llenado
                                </label>
                                <input type="number" id="mod_cent_llenado" class="form-control border-secondary" min="0"
                                    placeholder="Ej: 250" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="bi bi-pencil-square"></i> Actualizar Centro de Acopio
                        </button>
                    </form>
                    <!--BAJA DE CENTROS DE ACOPIO-->
                    <form id="form_eliminacion_acopios" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja de Centro de Acopio</h5>

                        <div class="mb-4">
                            <label for="cent_id_eliminar" class="form-label small fw-bold">
                                ID del Centro de Acopio
                            </label>

                            <input type="number" id="cent_id_eliminar"
                                class="form-control border-danger form-control-lg" min="1" placeholder="ej. 15"
                                required>

                            <p class="fw-bold small text-muted mt-2">
                                Atención: Esta acción eliminará permanentemente el centro de acopio del sistema.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            Eliminar Definitivamente
                        </button>
                    </form>

                    <!--ALTA DE  HERRAMIENTAS-->

                    <form id="form_registro_herramientas" class="d-none">
                        <h5 class="fw-bold mb-3 text-success">Alta de Herramienta</h5>

                        <!-- Tipo de herramienta -->
                        <div class="mb-3">
                            <label for="herram_tipo" class="form-label fw-bold">
                                Tipo de herramienta
                            </label>
                            <input type="text" id="herram_tipo" class="form-control border-secondary" maxlength="40"
                                placeholder="Ej: Pala" required>
                            <p class="fw-bold small text-muted mt-2">
                                Ingrese el tipo de herramienta que desea registrar.
                            </p>
                        </div>

                        <!-- Establecimiento -->
                        <div class="mb-4">
                            <label for="herram_estab" class="form-label fw-bold">
                                ID del establecimiento
                            </label> <input type="number" id="herram_estab" class="form-control
                            border-secondary" min="1" placeholder="Ej: 15" required>
                            <p class="fw-bold small text-muted mt-2">
                                Ingrese el ID del establecimiento al que pertenece la herramienta.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold">
                            <i class="bi bi-tools"></i> Registrar Herramienta
                        </button>
                    </form>

                    <!--MODIFICACION DE HERRAMIENTAS---->

                    <form id="form_modificacion_herramientas" class="d-none">
                        <h5 class="fw-bold mb-3 text-warning">Modificar herramienta</h5>

                        <!-- ID de la herramienta -->
                        <div class="mb-3">
                            <label for="mod_herram_id" class="form-label fw-bold">
                                ID de la herramienta
                            </label>

                            <input type="number" id="mod_herram_id" class="form-control border-warning" min="1"
                                placeholder="Ej: 12" required>
                            <p class="fw-bold small text-muted mt-2">
                                Ingrese el ID de la herramienta que desea modificar.
                            </p>
                        </div>

                        <!-- Tipo de herramienta -->
                        <div class="mb-3">
                            <label for="mod_herram_tipo" class="form-label fw-bold">
                                Nuevo tipo de herramienta
                            </label>
                            <input type="text" id="mod_herram_tipo" class="form-control border-secondary" maxlength="40"
                                placeholder="Ej: Pala" required>
                            <p class="fw-bold small text-muted mt-2">
                                Ingrese el nuevo tipo de herramienta.
                            </p>
                        </div>

                        <!-- Establecimiento -->
                        <div class="mb-4">
                            <label for="mod_herram_estab" class="form-label fw-bold">
                                Nuevo ID del establecimiento
                            </label>
                            <input type="number" id="mod_herram_estab" class="form-control border-secondary" min="1"
                                placeholder="Ej: 15" required>

                            <p class="fw-bold small text-muted mt-2">
                                Ingrese el ID del establecimiento al que pertenecerá la herramienta.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="bi bi-pencil-square"></i> Actualizar Herramienta
                        </button>
                    </form>

                    <!--BAJA DE HERRAMIENTAS-->

                    <form id="form_eliminacion_herramientas" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Baja de herramientas</h5>

                        <div class="mb-4">
                            <label for="herram_id_eliminar" class="form-label small fw-bold">ID de la
                                herramienta</label>
                            <input type="number" id="herram_id_eliminar"
                                class="form-control border-danger form-control-lg" min="1" placeholder="ej. 1042"
                                required>
                            <p class="fw-bold small text-muted mt-2"> Atención: Esta acción eliminará la herramienta del
                                sistema.</p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">Eliminar Definitivamente</button>
                    </form>

                    <!--Asignar cuadrilla a la incidencia-->
                    <form id="form_asignar_cuadrilla" class="d-flex flex-column gap-3 d-none">

                        <div>
                            <label for="inc_id" class="form-label fw-bold">
                                Número de Incidencia
                            </label>
                            <input type="number" class="form-control border-secondary" id="inc_id" placeholder="Ej: 25"
                                min="1" required>
                            <p class="fw-bold small">
                                Ingrese el identificador de la incidencia que desea asignar.
                            </p>
                        </div>

                        <div>
                            <label for="inc_cuadrilla" class="form-label fw-bold">
                                Cuadrilla asignada
                            </label>
                            <select class="form-select border-secondary" id="inc_cuadrilla" required>
                                <option value="" disabled selected>Cargando cuadrillas disponibles...</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-people-fill me-1"></i>
                            Asignar cuadrilla
                        </button>

                    </form>

                    <!-- RECHAZO DE INCIDENCIAS -->
                    <form id="form_rechazar_incidencia" class="d-none">
                        <h5 class="fw-bold mb-3 text-danger">Rechazar Incidencia</h5>

                        <div class="mb-3">
                            <label for="inc_id_rechazar" class="form-label small fw-bold">
                                ID de la incidencia
                            </label>

                            <input type="number" id="inc_id_rechazar" class="form-control border-danger form-control-lg"
                                min="1" placeholder="ej. 1042" required>
                        </div>

                        <div class="mb-4">
                            <label for="inc_motivo_rechazo" class="form-label small fw-bold">
                                Motivo del rechazo
                            </label>

                            <textarea id="inc_motivo_rechazo" class="form-control border-danger" rows="4"
                                placeholder="Explicá por qué se rechaza la incidencia..." required></textarea>

                            <p class="fw-bold small text-muted mt-2">
                                Atención: el motivo ingresado reemplazará la descripción realizada por el vecino.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 fw-bold">
                            Rechazar incidencia
                        </button>
                    </form>
                </div>
            </div>
            <!--TABLAS-->
            <div class="col-12 col-lg-8">
                <div class="card p-2 mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <!-- Selector  para las tablas -->
                        <select id="selector_tabla" class="form-select border-secondary fw-bold w-auto"
                            onchange="cambiarTablaVisualizada()">
                            <option value="tabla_usuarios" selected>Ver Tabla: Personal / Usuarios</option>
                            <option value="tabla_contenedores">Ver Tabla:Contenedores</option>
                            <option value="tabla_camiones">Ver Tabla: Flota (Camiones)</option>
                            <option value="tabla_rutas">Ver Tabla: Rutas</option>
                            <option value="tabla_cuadrillas">Ver tabla: Cuadrillas</option>
                            <option value="tabla_acopios">Ver tabla: Centros de acopio</option>
                            <option value="tabla_herramientas">Ver tabla: Herramientas</option>
                            <option value="tabla_incidencias">Ver tabla: incidencias</option>
                            <option value="tabla_circuitos"> Ver Mapa: Circuito de tu municipalidad</option>

                        </select>
                    </div>

                    <!-- Tabla con los usuarios -->
                    <div id="contenedor_tabla_usuarios">
                        <div id="tabla_usuarios" class="table-responsive">
                            <!-- me gusta creer que acá se inyectará magicamente la tabla-->
                        </div>
                    </div>

                    <!-- Tabla  con mis bellos contenedores -->
                    <div id="contenedor_tabla_contenedores" class="d-none">
                        <div id="tabla_contenedores" class="table-responsive">

                        </div>
                    </div>

                    <!-- Tabla con camiones-->
                    <div id="contenedor_tabla_camiones" class="d-none">
                        <div id="tabla_camiones" class="table-responsive">

                        </div>
                    </div>

                    <!-- Tabla con rutas-->
                    <div id="contenedor_tabla_rutas" class="d-none">
                        <div id="tabla_rutas" class="table-responsive">

                        </div>
                    </div>

                    <!-- Tabla con cuadrillas-->
                    <div id="contenedor_tabla_cuadrillas" class="d-none">
                        <div id="tabla_cuadrillas" class="table-responsive"></div>
                    </div>

                    <!-- Tabla con centros de acopio-->

                    <div id="contenedor_tabla_acopios" class="d-none">
                        <div id="tabla_acopios" class="table-responsive"></div>
                    </div>

                    <!-- Tabla de herramientas-->
                    <div id="contenedor_tabla_herramientas" class="d-none">
                        <div id="tabla_herramientas" class="table-responsive"></div>
                    </div>

                    <!-- Tabla de incidencias-->
                    <div id="contenedor_tabla_incidencias" class="d-none">
                        <p class="small">Leyenda: Gris=En espera Naranja =Abierta Verde=Cerrada</p>
                        <div id="tabla_incidencias" class="table-responsive">

                        </div>
                    </div>

                    <!-- Mapa de circuito, le puse tabla para que encaje en la función cambiarTablaVisualizada-->
                    <div id="contenedor_tabla_circuitos" class="d-none">
                        <div id="tabla_circuitos" style="height: 500px;"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <select id="filtro_tipo_circuito" class="form-select">
        <option value="todos">Todos los circuitos</option>
        <option value="mezclados">Mezclados</option>
        <option value="reciclaje">Reciclaje</option>
    </select>



    <!-- cambio de formularios-->
    <script>
        // Esta función se ejecuta cuando cambias entre entidades
        function cambiarEntidad() {
            const entidad = document.getElementById('selector_entidad').value;
            const selector_operacion = document.getElementById('selector_operacion');

            // Limpiamos las opciones anteriores del segundo selector
            selector_operacion.innerHTML = '';

            if (entidad === 'usuarios') {
                selector_operacion.innerHTML = `
            <option value="registro_usuarios">Registrar Nuevo Usuario</option>
            <option value="modificacion_usuarios">Modificar Usuario</option>
            <option value="eliminacion_usuarios">Eliminar Usuario</option>
        `;
            } else if (entidad === 'contenedores') {
                selector_operacion.innerHTML = `
            <option value="registro_contenedores">Alta de contenedor</option>
            <option value="modificacion_contenedores">Modificar contenedor</option>
            <option value="eliminacion_contenedores">Eliminar contenedor</option>
        `;
            } else if (entidad === 'camiones') {
                selector_operacion.innerHTML = `
            <option value="registro_camiones">Alta de camión</option>
            <option value="modificacion_camiones">Modificar camión </option>
            <option value="eliminacion_camiones" >Eliminar camión </option>
        `;
            } else if (entidad === 'rutas') {
                selector_operacion.innerHTML = `
            <option value="registro_rutas">Alta de ruta</option>
            <option value="modificacion_rutas" disabled>Modificar ruta (Una aventura)</option>
            <option value="eliminacion_rutas" disabled>Eliminar ruta (para otro día)</option>
        `;
            } else if (entidad === 'cuadrillas') {
                selector_operacion.innerHTML = `
            <option value="registro_cuadrillas">Alta de cuadrilla</option>
            <option value="eliminacion_cuadrillas">Eliminar cuadrilla</option>
        `;
            } else if (entidad === 'acopios') {
                selector_operacion.innerHTML = `
            <option value="registro_acopios">Alta de centro de acopio</option>
            <option value="modificacion_acopios">Modificar centro de acopio</option>
            <option value="eliminacion_acopios">Eliminar centro de acopio</option>
        `;
            } else if (entidad === 'herramientas') {
                selector_operacion.innerHTML = `
            <option value="registro_herramientas">Alta de herramienta</option>
            <option value="modificacion_herramientas">Modificar herramienta</option>
            <option value="eliminacion_herramientas">Eliminar herramienta</option>
        `;
            } else if (entidad === 'incidencias') {
                selector_operacion.innerHTML = `
            <option value="asignar_cuadrilla">Asignar cuadrilla</option>
            <option value="rechazar_incidencia">Rechazar incidencia</option>
        `;
            }

            selector_operacion.selectedIndex = 0;
            // Forzamos la actualización de la vista de los formularios
            cambiarFormularios();
        }

        // Esta función oculta y muestra los formularios
        function cambiarFormularios() {
            const operacion_seleccionada = document.getElementById('selector_operacion').value;

            // 1. arreglo de TODOS los formularios existentes en la página
            const formularios = [
                'form_registro_usuarios',
                'form_modificacion_usuarios',
                'form_eliminacion_usuarios',
                'form_registro_contenedores',
                'form_modificacion_contenedores',
                'form_eliminacion_contenedores',
                'form_registro_camiones',
                'form_modificacion_camiones',
                'form_eliminacion_camiones',
                'form_registro_rutas',
                'form_registro_cuadrillas',
                'form_eliminacion_cuadrillas',
                'form_registro_acopios',
                'form_modificacion_acopios',
                'form_eliminacion_acopios',
                'form_registro_herramientas',
                'form_modificacion_herramientas',
                'form_eliminacion_herramientas',
                'form_asignar_cuadrilla',
                'form_rechazar_incidencia'
            ];


            // Recorremos y mostramos solo el seleccionado
            formularios.forEach(id_form => {
                const formulario = document.getElementById(id_form);
                if (formulario) {
                    if (id_form === `form_${operacion_seleccionada}`) {
                        formulario.classList.remove('d-none');
                        if (id_form === `form_registro_rutas`) {
                            inicializarFormularioRutas(); // Editar, no sé, fijate si es útil cargar algo
                        }
                        else if (id_form === 'form_registro_contenedores') {
                            document.getElementById('cont_longitud').value = 0;
                            document.getElementById('cont_latitud').value = 0;

                            inicializarMapaSelector('mapa_selector');
                        }
                        else if (id_form === 'form_modificacion_contenedores') {
                            inicializarMapaSelector('mapa_selector_mod');
                            document.getElementById('mod_cont_longitud').value = 0;
                            document.getElementById('mod_cont_latitud').value = 0;
                        }
                        else if (id_form === 'form_registro_cuadrillas') {
                            cargarCamionesEnSelector('cuad_cam');
                            cargarRecolectoresEnSelectores();
                        } else if (id_form === 'form_asignar_cuadrilla') {
                            inicializarFormularioIncidencias();
                        }
                    } else {
                        formulario.classList.add('d-none');
                    }
                }
            });


        }
        function cambiarTablaVisualizada() {
            const tabla_seleccionada = document.getElementById('selector_tabla').value;

            const tablas = [
                'usuarios',
                'contenedores',
                'camiones',
                'rutas',
                'cuadrillas',
                'acopios',
                'herramientas',
                'incidencias',
                'circuitos'
            ];

            tablas.forEach(nombre => {
                const div = document.getElementById(`contenedor_tabla_${nombre}`);

                if (div) {
                    if (tabla_seleccionada === `tabla_${nombre}`) {
                        div.classList.remove('d-none');
                        
                        // Cargar los datos de la tabla seleccionada
                        //Slice quita la "u" de "usuarios", nombre.charAtblablabla agarra la "U" y la mayusculiza
                        //luego se concatenan para que quede Usuarios(como en cargarUsuarios)
                        //existe el método window.funcion, pero no sabemos el nombre de la función, por eso
                        //usamos window[], para obtenerla, luego la ejecutamos fon funcion_cargar
                        const funcion_cargar = window[`cargar${nombre.charAt(0).toUpperCase() + nombre.slice(1)}`];

                        funcion_cargar();
  

                    } else {
                        div.classList.add('d-none');
                    }
                }
            });
        }
    </script>

    <!--1. CARGAR Y LISTAR USUARIOS (GET)-->

    <script>
        const API_URL = '../controlador/api_usuarios.php';
        function cargarUsuarios() {
            fetch(`${API_URL}/funcionarios`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_usuarios');

                    // Si el elemento no existe en el HTML, salimos en  sin romper el script
                    if (!tbody) return;

                    //por si ya se había cargado la tabla antes, limpia el tbody para no duplicar los
                    //filas de usuarios anteriores
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo en el div
                    if (!data || data.length === 0) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay personal operativo registrado.</p>`;
                        return;
                    }

                    // Si hay datos, llamamos a la función de dibujo
                    loadUsuarios(data);
                })
                .catch(err => console.error("Error al cargar usuarios:", err));
        }
        // 4. LÓGICA AUXILIAR: MODO OSCURO & BOTONES
        document.getElementById('btn-actualizar')?.addEventListener('click', cargarUsuarios);

        document.getElementById('btn-theme').addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            document.getElementById('btn-theme').innerHTML = isDark
                ? '<i class="bi bi-sun"></i> Modo Claro'
                : '<i class="bi bi-moon-stars"></i> Modo Oscuro';
        });

        // Carga inicial al abrir la página
        document.addEventListener("DOMContentLoaded", cargarUsuarios);
    </script>
    <!--1 Cargar y Listar conteiners-->
    <script>
        const API_CONTENEDORES = '../controlador/api_contenedores.php';
        function cargarContenedores() {
            fetch(`${API_CONTENEDORES}/contenedores`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_contenedores');

                    // Escudo protector: si no existe, no rompemos nada
                    if (!tbody) return;

                    tbody.innerHTML = ''; // Limpiamos

                    // Validamos si no hay datos o viene el mensaje de bypass del backend
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay infraestructura de contenedores registrada.</p>`;
                        return;
                    }

                    loadContenedores(data);
                })
                .catch(err => console.error("Error al cargar contenedores:", err));
        }
    </script>

    <!--1 Cargar y listar camiones -->
    <script>
        const API_CAMIONES = '../controlador/api_camiones.php';
        function cargarCamiones() {
            fetch(`${API_CAMIONES}/camiones`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_camiones');

                    // Escudo protector: si no existe el div, salimos
                    if (!tbody) return;
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay camiones registrados en la flota.</p>`;
                        return;
                    }

                    loadCamiones(data);
                })
                .catch(err => console.error("Error al cargar camiones:", err));
        }
    </script>
    <!--1. cargar y listar Rutas-->
    <script>
        const API_RUTAS = '../controlador/api_rutas.php';

        function cargarRutas() {
            fetch(`${API_RUTAS}/rutas`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_rutas');

                    // Escudo protector: si no existe el contenedor, salimos
                    if (!tbody) return;
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay rutas planificadas en el sistema.</p>`;
                        return;
                    }

                    // Pasamos los datos al load
                    loadRutas(data);
                })
                .catch(err => console.error("Error al cargar rutas:", err));
        }
    </script>

    <!--1. Cargar y listar Cuadrillas-->
    <script>
        const API_CUADRILLAS = '../controlador/api_cuadrillas.php';
        function cargarCuadrillas() {
            fetch(`${API_CUADRILLAS}/cuadrillas`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_cuadrillas');

                    // Escudo protector: si no existe el div, salimos
                    if (!tbody) return;
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay cuadrillas registrados en el sistema.</p>`;
                        return;
                    }

                    loadCuadrillas(data);
                })
                .catch(err => console.error("Error al cargar Cuadrillas:", err));
        }
    </script>

    <!--1. Cargar y listar Centros de acopio-->

    <script>
        const API_ACOPIOS = '../controlador/api_acopios.php';
        function cargarAcopios() {
            fetch(`${API_ACOPIOS}/acopios`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_acopios');

                    // Escudo protector: si no existe el div, salimos
                    if (!tbody) return;
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay centros de acopio registrados en el sistema.</p>`;
                        return;
                    }

                    loadAcopios(data);
                })
                .catch(err => console.error("Error al cargar Centros de acopio:", err));
        }
    </script>

    <!-- 1. cargar herramientas-->

    <script>
        const API_HERRAMIENTAS = '../controlador/api_herramientas.php';
        function cargarHerramientas() {
            fetch(`${API_HERRAMIENTAS}/herramientas`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_herramientas');

                    // Escudo protector: si no existe el div, salimos
                    if (!tbody) return;
                    tbody.innerHTML = ''; // Limpiamos

                    // Si no hay datos, mostramos el mensaje directo
                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay herramientas registradas en el sistema.</p>`;
                        return;
                    }

                    loadHerramientas(data);
                })
                .catch(err => console.error("Error al cargar herramientas:", err));
        }
    </script>

    <!-- 1. Cargar Incidencias-->
    <script>

        const API_INCIDENCIAS = '../controlador/api_incidencias.php';

        function cargarIncidencias() {
            fetch(`${API_INCIDENCIAS}/incidencias`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla_incidencias');

                    if (!tbody) return;

                    tbody.innerHTML = '';

                    if (!data || data.length === 0 || data.mensaje) {
                        tbody.innerHTML = `<p class="text-center text-muted py-3">No hay incidencias registrados.</p>`;
                        return;
                    }

                    loadIncidencias(data);
                })
                .catch(err => console.error("Error al cargar incidencias:", err));
        }

    </script>
    <!-- 1.0 cargar camiones funcionales-->
    <script>
        function inicializarFormularioRutas() {
            const selector_camion = document.getElementById('ruta_camion');
            if (!selector_camion) return;

            // mirá si no seremos grosos que reutilizamos código
            fetch('../controlador/api_camiones.php/camiones')
                .then(response => response.json())
                .then(camiones_lista => {
                    selector_camion.innerHTML = '<option value="" disabled selected>Seleccione un camión</option>';

                    // Filtramos solo los funcionales para que no asignen un camión roto
                    const camiones_activos = camiones_lista.filter(c => c.cam_estado === "funcional");
                    //bitacora del integrante: despues filtrame que el camion para las rutas sea de ruta, estaría lindo
                    if (camiones_activos.length === 0) {
                        selector_camion.innerHTML = '<option value="" disabled>No hay camiones funcionales disponibles</option>';
                        return;
                    }

                    camiones_activos.forEach(c => {
                        selector_camion.innerHTML += `<option value="${c.cam_matricula}">${c.cam_matricula} (${c.cam_modelo})</option>`;
                    });
                })
                .catch(err => {
                    console.error("Error al cargar camiones para el formulario:", err);
                    selector_camion.innerHTML = '<option value="" disabled>Error al cargar camiones</option>';
                });
        }
    </script>

    <!-- 2. CREar contenedores-->
    <script>
        document.getElementById('form_registro_contenedores').addEventListener('submit', function (e) {
            e.preventDefault();

            const calle = document.getElementById('cont_calle').value.trim();
            const tipo = document.getElementById('cont_tipo').value;
            const estado = document.getElementById('cont_estado').value;
            const lat_str = document.getElementById('cont_latitud').value.trim();
            const lng_str = document.getElementById('cont_longitud').value.trim();

            if (calle === "") {
                alert("⚠️ Por favor, ingrese el nombre de la calle o ubicación.");
                document.getElementById('cont_calle').focus();
                return;
            }

            if (calle.length > 29) {
                alert("⚠️ La dirección no puede superar los 29 caracteres.");
                return;
            }

            // Expresión regular para permitir letras, números, espacios y signos típicos de dirección (. , - /)
            const regex_calle = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\/]+$/;
            if (!regex_calle.test(calle)) {
                alert("⚠️ La dirección contiene caracteres no válidos.");
                return;
            }

            // me parece que comparar con un array es menos largo que poner || tipo = 
            const tipos_permitido = ['mezclados', 'reciclaje', 'volqueta'];
            if (!tipos_permitido.includes(tipo)) {
                alert("⚠️ Seleccione un tipo de contenedor válido.");
                return;
            }

            const estados_permitidos = ['funcional', 'roto', 'desbordado', 'reserva'];
            if (!estados_permitidos.includes(estado)) {
                alert("⚠️ Seleccione un estado de contenedor válido.");
                return;
            }

            // 4. Validación de Coordenadas
            if (!lat_str || !lng_str || lat_str === "0.000000" || lng_str === "0.000000") {
                alert("⚠️ Por favor, seleccione la ubicación geográfica en el mapa.");
                return;
            }

            // 5. Si todo pasa con éxito, se arma el JSON limpio
            const nuevo_contenedor = {
                cont_calle: calle,
                cont_tipo: tipo,
                cont_estado: estado,
                cont_latitud: parseFloat(lat_str),
                cont_longitud: parseFloat(lng_str)
            };

            // Envío al backend
            fetch(`${API_CONTENEDORES}/contenedores`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevo_contenedor)
            })
                .then(response => response.json())
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("éxito")) {
                        document.getElementById('form_registro_contenedores').reset();

                        if (marcadorSeleccion) {
                            mapaSelector.removeLayer(marcadorSeleccion);
                            marcadorSeleccion = null;
                        }
                        cargarContenedores();
                    }
                })
                .catch(err => {
                    console.error("Error al registrar el contenedor:", err);
                    alert("Hubo un fallo crítico al conectar con la API de infraestructura.");
                });
        });
    </script>
    <!-- 2. CREAr usuario -->
    <script>

        document.getElementById('form_registro_usuarios').addEventListener('submit', function (e) {
            e.preventDefault();
            // 1. Extracción y limpieza (trim elimina espacios al principio y final)
            const ci = document.getElementById('ci').value.trim();
            const name = document.getElementById('name').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const email = document.getElementById('email').value.trim();
            const rol = document.getElementById('rol').value.trim();
            const telefono = parseInt(document.getElementById('telefono').value.trim(), 10);
            const edad = parseInt(document.getElementById('edad').value.trim(), 10);

            // 2. Validación de Cédula (Exactamente 8 dígitos numéricos)
            const regex_numeros = /^\d+$/; // Solo números
            if (!regex_numeros.test(ci) || ci.length !== 8) {
                alert("⚠️ La Cédula de Identidad debe contener exactamente 8 números, sin puntos ni guiones.");
                document.getElementById('ci').focus();
                return;
            }

            // 3. Validación de Teléfono (Entre 8 y 9 dígitos)
            if (!regex_numeros.test(telefono) || telefono.length < 8 || telefono.length > 9) {
                alert("⚠️ El teléfono debe ser numérico y contener entre 8 y 9 dígitos.");
                document.getElementById('telefono').focus();
                return;
            }

            // 4. Validación de Edad (Entre 16 y 100 años)
            if (isNaN(edad) || edad < 16 || edad > 100) {
                alert("⚠️ La edad debe ser un número entero válido (mayor o igual a 16 años y menor a 100).");
                document.getElementById('edad').focus();
                return;
            }

            // 5. Validación de Nombre y Apellido (Solo letras y espacios, incluyendo tildes y ñ)
            const regex_texto = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            if (!regex_texto.test(name) || !regex_texto.test(apellido)) {
                alert("⚠️ El nombre y el apellido solo pueden incluir letras y espacios.");
                return;
            }

            // 6. Validación de Email (uno o más carácteres distintos de espacio y @, un @, carácteres hasta un ., carácteres finales)
            const regex_email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex_email.test(email)) {
                alert("⚠️ El formato del correo electrónico no es válido.");
                document.getElementById('email').focus();
                return;
            }

            // 7. Validación de Roles permitidos
            const roles_permitidos = ['vecino', 'administrador', 'operario_vertedero', 'operario_taller', 'operario_acopio', 'recolector'];
            if (!roles_permitidos.includes(rol)) {
                alert("⚠️ El rol seleccionado no es válido dentro del sistema.");
                document.getElementById('rol').focus();
                return;
            }
            // armamiento del paquete JSON con los datos ingresados
            const nuevo_usuario = {
                usr_ci: parseInt(ci, 10),
                usr_name: name,
                usr_apellido: apellido,
                usr_email: email,
                usr_rol: rol,
                usr_telefono: telefono,
                usr_edad: edad

            };

            fetch(`${API_URL}/usuarios`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevo_usuario)
            })//tengo una piterson teorías
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Si el mensaje del backend confirma el éxito, actualiza la lista y limpia el form
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_registro_usuarios').reset();
                        cargarUsuarios();
                    }
                })
                .catch(err => {
                    console.error("Error al registrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>
    <!--2. crear camion-->
    <script>
        document.getElementById('form_registro_camiones').addEventListener('submit', function (e) {
            e.preventDefault();
            // la / indica que esto es un regex, la \s cualquier espacio en blanco, la/g indica que para todo el texto
            const matricula = document.getElementById('cam_matricula').value.trim().toUpperCase().replace(/\s/g, '');
            const tipo = document.getElementById('cam_tipo').value.trim();
            const modelo = document.getElementById('cam_modelo').value.trim();
            const estado = document.getElementById('cam_estado').value.trim();
            const capacidad = parseInt(document.getElementById('cam_capacidad').value.trim(), 10);

            // 2. Validación de Matrícula
            if (matricula === "") {
                alert("⚠️ Por favor, ingrese la matrícula del camión.");
                document.getElementById('cam_matricula').focus();
                return;
            }

            // Expresión regular: Obliga a empezar con 'S', seguida de exactamente 2 letras de la A a la Z, y exactamente 4 números.
            const regex_matricula = /^S[A-Z]{2}\d{4}$/;
            if (!regex_matricula.test(matricula)) {
                alert("⚠️ Formato de matrícula inválido. Debe ser de Montevideo (empezar con 'S'), seguida de 2 letras y 4 números (Ej: SAB 1234).");
                document.getElementById('cam_matricula').focus();
                return;
            }

            // 3. Validación de Tipo
            const tipos_permitidos = ['mezclados', 'reciclaje', 'volqueta'];
            if (!tipos_permitidos.includes(tipo)) {
                alert("⚠️ Seleccione un tipo de camión válido de la lista.");
                return;
            }

            // 4. Validación de Modelo
            const modelos_permitidos = ['mercedes-benz', 'caterpillar'];
            if (!modelos_permitidos.includes(modelo)) {
                alert("⚠️ Seleccione un modelo o marca válido de la lista.");
                return;
            }

            const estados_permitidos = ['funcional', 'roto'];
            if (!estados_permitidos.includes(estado)) {
                alert("⚠️ Seleccione un estado válido para el vehículo.");
                return;
            }

            // 6. Validación de Capacidad
            if (isNaN(capacidad) || capacidad <= 0) {
                alert("⚠️ La capacidad del camión debe ser un número entero mayor a 0.");
                document.getElementById('cam_capacidad').focus();
                return;
            }
            // Armado del paquete JSON con los datos ingresados en el formulario
            const nuevo_camion = {
                cam_matricula: matricula,
                cam_tipo: tipo,
                cam_modelo: modelo,
                cam_estado: estado,
                cam_capacidad: capacidad
            };

            // Envío de los datos mediante POST a la API de camiones
            fetch(`${API_CAMIONES}/camiones`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevo_camion)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    // Mostramos el mensaje (sea de éxito o el error correspondiente de validación)
                    alert(respuesta.mensaje);

                    // Si el backend nos confirma el éxito, reseteamos el formulario y refrescamos la tabla
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_registro_camiones').reset();
                        cargarCamiones();
                    }
                })
                .catch(err => {
                    console.error("Error al registrar camión:", err);
                    alert("Hubo un fallo crítico al conectar con la API de flota.");
                });
        });
    </script>

    <!--2. crear rutas-->
    <script>
        document.getElementById('form_registro_rutas').addEventListener('submit', function (e) {
            e.preventDefault();

            //crea una variable temporal para almacenar los contenedores, así los ponemos en 
            //formate legible para mysql
            const texto_ids = document.getElementById('ruta_contenedores').value;

            // Separamos por comas y limpiamos los espacios de los costados
            const fragmentos = texto_ids.split(',').map(item => item.trim());

            // si el usuario dejó el campo vacío o solo con comas
            if (fragmentos.length === 0 || fragmentos[0] === "") {
                alert("Por favor, ingrese al menos un ID de contenedor.");
                return;
            }

            const vector_ids = [];

            // Checamos que todos los elementos estén bien
            for (let i = 0; i < fragmentos.length; i++) {
                const valor_actual = fragmentos[i];

                // Validamos con una expresión regular que sea un número y nada más
                // Esto se encarga de evitar "7i", "1a", "a", vacíos, etc.
                const es_numero_puro = /^\d+$/.test(valor_actual);

                if (!es_numero_puro) {
                    alert(`Error: "${valor_actual}" no es un número de ID válido. Por favor, revise lo que escribió.`);
                    return; // Corta la ejecución del submit por completo, no se envía nada a la API
                }

                // Si todo está perfecto, se guarda en el arreglo
                vector_ids.push(parseInt(valor_actual), 10);
            }

            // 3. Si el código llegó hasta acá, significa que TODO lo que escribió el administrador está perfecto
            const nueva_ruta = {
                ruta_id: parseInt(document.getElementById('ruta_id').value, 10),
                ruta_fecha: document.getElementById('ruta_fecha').value,
                ruta_camion: document.getElementById('ruta_camion').value,
                contenedores: vector_ids
            };

            // Envío por POST a API de rutas 
            fetch(`${API_RUTAS}/rutas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nueva_ruta)
            })
                .then(response => response.json())
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_registro_rutas').reset();
                        if (!document.getElementById('contenedor_tabla_rutas').classList.contains('d-none')) {
                            cargarRutas();
                        }
                    }
                })
                .catch(err => {
                    console.error("Error al registrar la ruta:", err);
                    alert("Hubo un fallo crítico al conectar con la API de rutas.");
                });
        });
    </script>
    <!--2. Crear cuadrillas-->
    <script>
        document.getElementById('form_registro_cuadrillas').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Extracción y limpieza (tomamos los values de los selectores)
            const matricula = document.getElementById('cuad_cam').value.trim().toUpperCase().replace(/\s/g, '');
            const ci1 = document.getElementById('cuad_ci1').value.trim();
            const ci2 = document.getElementById('cuad_ci2').value.trim();

            // 2. Validación de Matrícula
            if (matricula === "") {
                alert("⚠️ Por favor, seleccione un camión.");
                document.getElementById('cuad_cam').focus();
                return;
            }

            const regex_matricula = /^S[A-Z]{2}\d{4}$/;
            if (!regex_matricula.test(matricula)) {
                alert("⚠️ La matrícula seleccionada tiene un formato inválido. Evite alterar los datos del formulario.");
                document.getElementById('cuad_cam').focus();
                return;
            }

            // 3. Validación de Cédulas (Ambas deben ser exactamente 8 números)
            const regex_numeros = /^\d+$/;

            if (!regex_numeros.test(ci1) || ci1.length !== 8) {
                alert("⚠️ La Cédula del primer recolector es inválida o no fue seleccionada.");
                document.getElementById('cuad_ci1').focus();
                return;
            }

            if (!regex_numeros.test(ci2) || ci2.length !== 8) {
                alert("⚠️ La Cédula del segundo recolector es inválida o no fue seleccionada.");
                document.getElementById('cuad_ci2').focus();
                return;
            }

            // 4. Validación de la regla de negocio: Los recolectores deben ser distintos
            if (ci1 === ci2) {
                alert("⚠️ Una cuadrilla no puede tener al mismo recolector dos veces. Seleccione dos personas distintas.");
                document.getElementById('cuad_ci2').focus();
                return;
            }

            // 5. Armado del paquete JSON (parseamos las cédulas a entero)
            const nueva_cuadrilla = {
                cuad_cam: matricula,
                cuad_ci1: parseInt(ci1, 10),
                cuad_ci2: parseInt(ci2, 10)
            };

            // 6. Envío de datos al Backend
            fetch(`${API_CUADRILLAS}/cuadrillas`, { // Ajustá a tu ruta de API correspondiente
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nueva_cuadrilla)
            })
                .then(response => response.json())
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Si se registró con éxito, reseteamos selectores y actualizamos vistas
                    if (respuesta.mensaje.includes("éxito") || respuesta.status === "success") {
                        document.getElementById('form_registro_cuadrillas').reset();
                        cargarCuadrillas();
                    }
                })
                .catch(err => {
                    console.error("Error al registrar cuadrilla:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>

    <!--2.Crear centros de copio-->
    <script>
        document.getElementById('form_registro_acopios').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Extracción de valores
            const calle = document.getElementById('cent_calle').value.trim();
            const num_puerta_str = parseInt(document.getElementById('cent_num_puerta').value.trim(), 10);
            const hora_apertura = document.getElementById('cent_hora_apertura').value.trim();
            const hora_cierre = document.getElementById('cent_hora_cierre').value.trim();
            const capacidad_str = parseInt(document.getElementById('cent_capacidad').value.trim(), 10);
            const llenado_str = parseInt(document.getElementById('cent_llenado').value.trim(), 10);

            // 2. Validación de Calle
            if (calle === "" || calle.length > 29) {
                alert("⚠️ La dirección es obligatoria y no puede superar los 29 caracteres.");
                document.getElementById('cent_calle').focus();
                return;
            }

            const regex_calle = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\/]+$/;
            if (!regex_calle.test(calle)) {
                alert("⚠️ La dirección contiene caracteres no válidos.");
                document.getElementById('cent_calle').focus();
                return;
            }

            // 3. Validación de Números (Puerta, Capacidad, Llenado)
            const regex_numeros = /^\d+$/;

            if (!regex_numeros.test(num_puerta_str) || num_puerta_str <= 0) {
                alert("⚠️ El número de puerta debe ser un número entero mayor a 0.");
                document.getElementById('cent_num_puerta').focus();
                return;
            }

            if (!regex_numeros.test(capacidad_str) || capacidad_str <= 0) {
                alert("⚠️ La capacidad máxima debe ser un número entero mayor a 0.");
                document.getElementById('cent_capacidad').focus();
                return;
            }

            if (!regex_numeros.test(llenado_str) || llenado_str < 0) {
                alert("⚠️ El llenado inicial debe ser un número entero igual o mayor a 0.");
                document.getElementById('cent_llenado').focus();
                return;
            }

            // Validación lógica: El llenado inicial no puede ser mayor a la capacidad
            if (llenado_str > capacidad_str) {
                alert("⚠️ El llenado inicial no puede ser mayor que la capacidad máxima del centro.");
                document.getElementById('cent_llenado').focus();
                return;
            }

            // Validación lógica : Cierre debe ser posterior a apertura
            if (hora_cierre <= hora_apertura) {
                alert("⚠️ La hora de cierre debe ser posterior a la hora de apertura.");
                document.getElementById('cent_hora_cierre').focus();
                return;
            }

            // 4. Validación de Horas (Debe ser formato HH:MM)
            const regex_hora = /^([01]\d|2[0-3]):([0-5]\d)$/;
            if (!regex_hora.test(hora_apertura) || !regex_hora.test(hora_cierre)) {
                alert("Las horas de apertura y cierre deben ser válidas.");
                return;
            }

            // 5. Construcción del JSON
            const nuevo_centro = {
                cent_calle: calle,
                cent_num_puerta: num_puerta_str,
                cent_hora_apertura: hora_apertura,
                cent_hora_cierre: hora_cierre,
                cent_capacidad: capacidad_str,
                cent_llenado: llenado_str
            };

            // 6. Envío al backend
            fetch(`${API_ACOPIOS}/acopios`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevo_centro)
            })
                .then(response => response.json())
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje && respuesta.mensaje.includes("éxito")) {
                        document.getElementById('form_registro_acopios').reset();
                        cargarAcopios();

                    }
                })
                .catch(err => {
                    console.error("Error al registrar el centro de acopio:", err);
                    alert("Hubo un fallo crítico al conectar con la API de establecimientos.");
                });
        });
    </script>

    <!--2. Crear herramienta-->
    <script>
        document.getElementById('form_registro_herramientas').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Obtención de datos
            const tipo = document.getElementById('herram_tipo').value.trim();
            const estab = parseInt(document.getElementById('herram_estab').value.trim(), 10);

            // 2. Validación del tipo de herramienta
            if (tipo === "") {
                alert("⚠️ Por favor, ingrese el tipo de herramienta.");
                document.getElementById('herram_tipo').focus();
                return;
            }

            if (tipo.length > 40) {
                alert("⚠️ El tipo de herramienta no puede superar los 40 caracteres.");
                document.getElementById('herram_tipo').focus();
                return;
            }

            // 3. Validación del ID del establecimiento
            if (isNaN(estab) || estab <= 0) {
                alert("⚠️ El ID del establecimiento debe ser un número entero mayor a 0.");
                document.getElementById('herram_estab').focus();
                return;
            }

            // 4. Armamiento del paquete JSON
            const nueva_herramienta = {
                herram_tipo: tipo,
                herram_estab: estab
            };

            // 5. Envío de los datos a la API
            fetch(`${API_HERRAMIENTAS}/herramientas`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nueva_herramienta)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_registro_herramientas').reset();
                        cargarHerramientas();
                    }
                })
                .catch(err => {
                    console.error("Error al registrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>

    <!-- 2. Asignar cuadrilla a incidencia-->

    <script>
        document.getElementById('form_asignar_cuadrilla').addEventListener('submit', function (e) {
            e.preventDefault();

            const inc_id = document.getElementById('inc_id').value.trim();
            const inc_cuadrilla = document.getElementById('inc_cuadrilla').value.trim();

            if (!inc_id || !/^\d+$/.test(inc_id) || Number(inc_id) <= 0) {
                alert("⚠️ El número de incidencia debe ser un entero mayor a 0.");
                document.getElementById('inc_id').focus();
                return;
            }

            if (!inc_cuadrilla || !/^\d+$/.test(inc_cuadrilla) || Number(inc_cuadrilla) <= 0) {
                alert("⚠️ Debe seleccionar una cuadrilla válida.");
                document.getElementById('inc_cuadrilla').focus();
                return;
            }

            const asignacion = {
                inc_id: inc_id,
                inc_cuadrilla: inc_cuadrilla
            };

            fetch(`${API_INCIDENCIAS}/asignarCuadrilla`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(asignacion)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {

                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_asignar_cuadrilla').reset();
                        cargarIncidencias();
                    }
                })
                .catch(err => {

                    console.error("Error al asignar cuadrilla:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");

                });
        });
    </script>

    <!--2.5 modificar usuarios-->
    <script>
        document.getElementById('form_modificacion_usuarios').addEventListener('submit', function (e) {
            e.preventDefault();
            const ci = document.getElementById('mod_ci').value.trim();
            const name = document.getElementById('mod_name').value.trim();
            const email = document.getElementById('mod_email').value.trim();
            const rol = document.getElementById('mod_rol').value.trim();
            const telefono = document.getElementById('mod_telefono').value.trim();
            const apellido = document.getElementById('mod_apellido').value.trim();
            const edad = document.getElementById('mod_edad').value.trim();

            if (ci === "" || name === "" || email === "" || rol === "" || telefono === "") {
                alert("⚠️ Error: Todos los campos son obligatorios para modificar el usuario.");
                return;
            }

            // 3. Armamos el JSON real con todas las propiedades que espera el backend
            const nuevo_usuario = {
                usr_ci: ci,
                usr_name: name,
                usr_email: email,
                usr_rol: rol,
                usr_telefono: telefono,
                usr_edad: edad,
                usr_apellido: apellido
            };

            fetch(`${API_URL}/modificar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(nuevo_usuario)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Si el mensaje del backend confirma el éxito, actualiza la lista y limpia el form
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_modificacion_usuarios').reset();
                        cargarUsuarios();
                    }
                })
                .catch(err => {
                    console.error("Error al modificar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });

    </script>

    <!--2.5 modificar contenedor-->
    <script>
        // Activar/Desactivar campos según el Switch
        document.getElementById('check_mod_ubicacion').addEventListener('change', function (e) {
            const activo = e.target.checked;
            const bloque = document.getElementById('bloque_mod_ubicacion');

            document.getElementById('mod_cont_calle').disabled = !activo;
            document.getElementById('mod_cont_latitud').disabled = !activo;
            document.getElementById('mod_cont_longitud').disabled = !activo;

            if (activo) {
                bloque.classList.remove('opacity-50', 'pointer-events-none');
                if (mapaSelector) {
                    setTimeout(() => mapaSelector.invalidateSize(), 200);
                }
            } else {
                bloque.classList.add('opacity-50', 'pointer-events-none');
            }
        });

        // Evento Submit de Modificación
        document.getElementById('form_modificacion_contenedores').addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const id = parseInt(document.getElementById('mod_cont_id').value.trim(), 10);
            const estado = document.getElementById('mod_cont_estado').value.trim();
            const modifica_ubicacion = document.getElementById('check_mod_ubicacion').checked;
            const tipo = document.getElementById('mod_cont_tipo').value.trim();

            if (!id || id <= 0) {
                alert(" Ingrese un ID de contenedor válido.");
                return;
            }

            const estados_permitidos = ['funcional', 'roto', 'desbordado', 'reserva'];
            if (!estados_permitidos.includes(estado)) {
                alert(" Seleccione un estado válido.");
                return;
            }

            const tipos_permitido = ['mezclados', 'reciclaje', 'volqueta'];
            if (!tipos_permitido.includes(tipo)) {
                alert(" Seleccione un tipo de contenedor válido.");
                return;
            }
            // Armamos el objeto base
            const datos_actualización = {
                cont_id: id,
                cont_estado: estado,
                cont_tipo: tipo,
                modifica_ubicacion: modifica_ubicacion
            };

            // Validaciones extra solo si el usuario activó el switch
            if (modifica_ubicacion) {
                const calle = document.getElementById('mod_cont_calle').value.trim();
                const lat_str = document.getElementById('mod_cont_latitud').value.trim();
                const lng_str = document.getElementById('mod_cont_longitud').value.trim();

                if (calle === "") {
                    alert("⚠️ Ingrese el nombre de la calle.");
                    document.getElementById('mod_cont_calle').focus();
                    return;
                }

                if (calle.length > 29) {
                    alert("⚠️ La dirección no puede superar los 29 caracteres.");
                    return;
                }

                const regex_calle = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\-\/]+$/;
                if (!regex_calle.test(calle)) {
                    alert("⚠️ La dirección contiene caracteres no válidos.");
                    return;
                }

                if (!lat_str || !lng_str || lat_str === "0.000000" || lng_str === "0.000000") {
                    alert("⚠️ Seleccione la nueva ubicación en el mapa.");
                    return;
                }

                // Agregamos los campos de ubicación al payload
                datos_actualización.cont_calle = calle;
                datos_actualización.cont_latitud = parseFloat(lat_str);
                datos_actualización.cont_longitud = parseFloat(lng_str);
            }

            fetch(`${API_CONTENEDORES}/modificar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos_actualización)
            })
                .then(response => response.json())
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    if (respuesta.mensaje && respuesta.mensaje.includes("éxito")) {
                        document.getElementById('form_modificacion_contenedores').reset();
                        document.getElementById('check_mod_ubicacion').dispatchEvent(new Event('change'));

                        if (typeof cargarContenedores === 'function') {
                            cargarContenedores();
                        }
                    }
                })
                .catch(err => {
                    console.error("Error al modificar el contenedor:", err);
                    alert("Fallo crítico al conectar con la API de infraestructura.");
                });
        });
    </script>

    <!--2.5 modificar camioncito-->
    <script>
        document.getElementById('form_modificacion_camiones').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Limpieza y obtención de datos usando los IDs del formulario de modificación
            const matricula = document.getElementById('mod_cam_matricula').value.trim().toUpperCase().replace(/\s/g, '');
            const tipo = document.getElementById('mod_cam_tipo').value.trim();
            const modelo = document.getElementById('mod_cam_modelo').value.trim();
            const estado = document.getElementById('mod_cam_estado').value.trim();

            // Le agregamos el 10 de la base decimal que vimos antes ;)
            const capacidad = parseInt(document.getElementById('mod_cam_capacidad').value.trim(), 10);

            // 2. Validación de Matrícula
            if (matricula === "") {
                alert("⚠️ Por favor, ingrese la matrícula del camión a modificar.");
                document.getElementById('mod_cam_matricula').focus();
                return;
            }

            const regex_matricula = /^S[A-Z]{2}\d{4}$/;
            if (!regex_matricula.test(matricula)) {
                alert("⚠️ Formato de matrícula inválido. Debe ser de Montevideo (empezar con 'S'), seguida de 2 letras y 4 números (Ej: SAB 1234).");
                document.getElementById('mod_cam_matricula').focus();
                return;
            }

            // 3. Validación de Tipo
            const tipos_permitidos = ['mezclados', 'reciclaje', 'volqueta'];
            if (!tipos_permitidos.includes(tipo)) {
                alert("⚠️ Seleccione un tipo de camión válido de la lista.");
                return;
            }

            // 4. Validación de Modelo
            const modelos_permitidos = ['mercedes-benz', 'caterpillar'];
            if (!modelos_permitidos.includes(modelo)) {
                alert("⚠️ Seleccione un modelo o marca válido de la lista.");
                return;
            }

            // 5. Validación de Estado
            const estados_permitidos = ['funcional', 'roto'];
            if (!estados_permitidos.includes(estado)) {
                alert("⚠️ Seleccione un estado válido para el vehículo.");
                return;
            }

            // 6. Validación de Capacidad
            if (isNaN(capacidad) || capacidad <= 0) {
                alert("⚠️ La capacidad del camión debe ser un número entero mayor a 0.");
                document.getElementById('mod_cam_capacidad').focus();
                return;
            }

            // 7. Armado del paquete JSON
            const camion_modificado = {
                cam_matricula: matricula,
                cam_tipo: tipo,
                cam_modelo: modelo,
                cam_estado: estado,
                cam_capacidad: capacidad
            };

            // 8. Envío de los datos mediante post a la API de camiones
            fetch(`${API_CAMIONES}/modificar`, {
                method: 'POST', // Método para actualizar
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(camion_modificado)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Si el backend nos confirma el éxito, reseteamos el formulario y refrescamos la tabla
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_modificacion_camiones').reset();
                        cargarCamiones();
                    }
                })
                .catch(err => {
                    console.error("Error al modificar camión:", err);
                    alert("Hubo un fallo crítico al conectar con la API de flota.");
                });
        });
    </script>

    <!--2.5 MODIFICA CENTROS DE ACOPIO-->
    <script>
        document.getElementById('form_modificacion_acopios').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Limpieza y obtención de datos
            const id = parseInt(document.getElementById('mod_cent_id').value.trim(), 10);
            const calle = document.getElementById('mod_cent_calle').value.trim();
            const num_puerta = parseInt(document.getElementById('mod_cent_num_puerta').value.trim(), 10);
            const hora_apertura = document.getElementById('mod_cent_hora_apertura').value.trim();
            const hora_cierre = document.getElementById('mod_cent_hora_cierre').value.trim();
            const capacidad = parseInt(document.getElementById('mod_cent_capacidad').value.trim(), 10);
            const llenado = parseInt(document.getElementById('mod_cent_llenado').value.trim(), 10);

            // 2. Validación del ID
            if (isNaN(id) || id <= 0) {
                alert("⚠️ El ID del centro de acopio debe ser un número entero mayor a 0.");
                document.getElementById('mod_cent_id').focus();
                return;
            }

            // 3. Validación de Calle
            if (calle === "" || calle.length > 29) {
                alert("⚠️ La dirección es obligatoria y no puede superar los 29 caracteres.");
                document.getElementById('mod_cent_calle').focus();
                return;
            }

            const regex_calle = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,\-\/]+$/;

            if (!regex_calle.test(calle)) {
                alert("⚠️ La dirección contiene caracteres no válidos.");
                document.getElementById('mod_cent_calle').focus();
                return;
            }

            // 4. Validación de números
            if (isNaN(num_puerta) || num_puerta <= 0) {
                alert("⚠️ El número de puerta debe ser un número entero mayor a 0.");
                document.getElementById('mod_cent_num_puerta').focus();
                return;
            }

            if (isNaN(capacidad) || capacidad <= 0) {
                alert("⚠️ La capacidad máxima debe ser un número entero mayor a 0.");
                document.getElementById('mod_cent_capacidad').focus();
                return;
            }

            if (isNaN(llenado) || llenado < 0) {
                alert("⚠️ El llenado debe ser un número entero igual o mayor a 0.");
                document.getElementById('mod_cent_llenado').focus();
                return;
            }

            // 5. Validación lógica del llenado
            if (llenado > capacidad) {
                alert("⚠️ El llenado no puede ser mayor que la capacidad máxima del centro.");
                document.getElementById('mod_cent_llenado').focus();
                return;
            }

            // 6. Validación lógica de horarios
            if (hora_cierre <= hora_apertura) {
                alert("⚠️ La hora de cierre debe ser posterior a la hora de apertura.");
                document.getElementById('mod_cent_hora_cierre').focus();
                return;
            }

            // 7. Validación de formato de horas
            const regex_hora = /^([01]\d|2[0-3]):([0-5]\d)$/;

            if (!regex_hora.test(hora_apertura) || !regex_hora.test(hora_cierre)) {
                alert("⚠️ Las horas de apertura y cierre deben ser válidas.");
                return;
            }

            // 8. Armado del paquete JSON
            const acopio_modificado = {
                cent_a_id: id,
                cent_calle: calle,
                cent_num_puerta: num_puerta,
                cent_hora_apertura: hora_apertura,
                cent_hora_cierre: hora_cierre,
                cent_capacidad: capacidad,
                cent_llenado: llenado
            };

            // 9. Envío de los datos mediante POST a la API
            fetch(`${API_ACOPIOS}/modificar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(acopio_modificado)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_modificacion_acopios').reset();
                        cargarAcopios();
                    }
                })
                .catch(err => {
                    console.error("Error al modificar centro de acopio:", err);
                    alert("Hubo un fallo crítico al conectar con la API de centros de acopio.");
                });
        });
    </script>

    <!--2.5 modificar Cuadrilla-->
    <!--  <script>
        document.getElementById('form_modificacion_cuadrillas').addEventListener('submit', function(e) {
            e.preventDefault();

            const cuad_id = document.getElementById('mod_cuad_id').value.trim();
            const cuad_cam = document.getElementById('mod_cuad_cam').value.trim().toUpperCase().replace(/\s/g, '');

            // 1. Validación de ID de la Cuadrilla
            const regex_numeros = /^\d+$/; 
            if (cuad_id === "" || !regex_numeros.test(cuad_id)) {
                alert("⚠️ Por favor, ingrese un ID de cuadrilla válido (número entero).");
                document.getElementById('mod_cuad_id').focus();
                return;
            }

            // 2. Validación de Matrícula del Camión
            if (cuad_cam === "") {
                alert("⚠️ Por favor, seleccione un camión.");
                document.getElementById('mod_cuad_cam').focus();
                return;
            }

            const regex_matricula = /^S[A-Z]{2}\d{4}$/;
            if (!regex_matricula.test(cuad_cam)) {
                alert("⚠️ La matrícula seleccionada tiene un formato inválido. Evite alterar los datos del formulario.");
                document.getElementById('mod_cuad_cam').focus();
                return;
            }

            // 3. Objeto con los datos que espera PHP ($data['cuad_id'] y $data['cuad_cam'])
            const datos = {
                cuad_id: parseInt(cuad_id, 10),
                cuad_cam: cuad_cam
            };

            // 4. Envío a la API
            fetch(`${API_CUADRILLAS}/modificar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            }).then(response => {
                return response.json();
            })           
            .then(respuesta => {
                alert(respuesta.mensaje); 

                // Si el backend nos confirma el éxito, reseteamos el formulario y refrescamos la tabla
                if (respuesta.mensaje.includes("con éxito")) { 
                    document.getElementById('form_modificacion_cuadrillas').reset();
                    cargarCuadrillas();
                }
            })
            .catch(err => {
                console.error("Error al modificar camión:", err);
                alert("Hubo un fallo crítico al conectar con la API de cuadrillas.");
            });
            
        });
    </script>-->

    <script>
        document.getElementById('form_modificacion_herramientas').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Obtención de datos
            const id = parseInt(document.getElementById('mod_herram_id').value.trim(), 10);
            const tipo = document.getElementById('mod_herram_tipo').value.trim();
            const estab = parseInt(document.getElementById('mod_herram_estab').value.trim(), 10);

            // 2. Validación del ID
            if (isNaN(id) || id <= 0) {
                alert("⚠️ El ID de la herramienta debe ser un número entero mayor a 0.");
                document.getElementById('mod_herram_id').focus();
                return;
            }

            // 3. Validación del tipo de herramienta
            if (tipo === "") {
                alert("⚠️ Por favor, ingrese el tipo de herramienta.");
                document.getElementById('mod_herram_tipo').focus();
                return;
            }

            if (tipo.length > 40) {
                alert("⚠️ El tipo de herramienta no puede superar los 40 caracteres.");
                document.getElementById('mod_herram_tipo').focus();
                return;
            }

            // 4. Validación del ID del establecimiento
            if (isNaN(estab) || estab <= 0) {
                alert("⚠️ El ID del establecimiento debe ser un número entero mayor a 0.");
                document.getElementById('mod_herram_estab').focus();
                return;
            }

            // 5. Armamiento del paquete JSON
            const herramienta_modificada = {
                herram_id: id,
                herram_tipo: tipo,
                herram_estab: estab
            };

            // 6. Envío de los datos a la API
            fetch(`${API_HERRAMIENTAS}/modificar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(herramienta_modificada)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_modificacion_herramientas').reset();
                        cargarHerramientas();
                    }
                })
                .catch(err => {
                    console.error("Error al modificar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>
    <!--3. eliminar usuario-->

    <script>
        document.getElementById('form_eliminacion_usuarios').addEventListener('submit', function (e) {
            e.preventDefault();

            const cedula = parseInt(document.getElementById('cedula_eliminar').value.trim(), 10);
            if (cedula == "") {
                alert("Por favor, ingrese una cédula");
                return;
            }
            if (isNaN(cedula) || cedula <= 0) {
                alert("⚠️ La cédula debe ser un número entero mayor a 0.");
                return;
            }
            fetch(`${API_URL}/usuarios`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usr_ci: cedula })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    //medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_usuarios').reset();
                        cargarUsuarios();
                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        })
    </script>

    <!--3. eliminar contenedor-->

    <script>
        document.getElementById('form_eliminacion_contenedores').addEventListener('submit', function (e) {
            e.preventDefault();

            const cont_id = parseInt(document.getElementById('cont_id_eliminar').value.trim(), 10);
            if (cont_id == "") {
                alert("Por favor, ingrese una id válida");
                return;
            }
            if (isNaN(cont_id) || cont_id <= 0) {
                alert(" El ID debe ser un número entero mayor a 0.");
                return;
            }
            fetch(`${API_CONTENEDORES}/contenedores`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cont_id: cont_id })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    //medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_contenedores').reset();
                        cargarContenedores();
                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        })
    </script>

    <!--3. eliminar camioncito 🥹-->

    <script>
        document.getElementById('form_eliminacion_camiones').addEventListener('submit', function (e) {
            e.preventDefault();

            const matricula = document.getElementById('matricula_eliminar').value.trim().toUpperCase().replace(/\s/g, '');

            // 2. Validación de Matrícula
            if (matricula === "") {
                alert("⚠️ Por favor, ingrese la matrícula del camión a modificar.");
                document.getElementById('mod_cam_matricula').focus();
                return;
            }

            const regex_matricula = /^S[A-Z]{2}\d{4}$/;
            if (!regex_matricula.test(matricula)) {
                alert("⚠️ Formato de matrícula inválido. Debe ser de Montevideo (empezar con 'S'), seguida de 2 letras y 4 números (Ej: SAB 1234).");
                document.getElementById('mod_cam_matricula').focus();
                return;
            }

            fetch(`${API_CAMIONES}/camiones`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cam_matricula: matricula })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    //medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_camiones').reset();
                        cargarCamiones();
                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        })

    </script>

    <!--3. eliminar Cuadrilla-->

    <script>
        document.getElementById('form_eliminacion_cuadrillas').addEventListener('submit', function (e) {
            e.preventDefault();

            const cuad_id = parseInt(document.getElementById('cuad_id_eliminar').value.trim(), 10);
            if (cuad_id == "") {
                alert("Por favor, ingrese una id válida");
                return;
            }
            if (isNaN(cuad_id) || cuad_id <= 0) {
                alert("⚠️ El ID debe ser un número entero mayor a 0.");
                return;
            }
            fetch(`${API_CUADRILLAS}/cuadrillas`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cuad_id: parseInt(cuad_id, 10) })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);
                    //medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_cuadrillas').reset();
                        cargarCuadrillas();
                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        })
    </script>

    <!--3. ELIMINAR ACOPIO-->

    <script>
        document.getElementById('form_eliminacion_acopios').addEventListener('submit', function (e) {
            e.preventDefault();

            const cent_a_id = parseInt(document.getElementById('cent_id_eliminar').value.trim(), 10);

            if (cent_a_id == "") {
                alert("Por favor, ingrese una id válida");
                return;
            }
            if (isNaN(cent_a_id) || cent_a_id <= 0) {
                alert("⚠️ El ID debe ser un número entero mayor a 0.");
                return;
            }
            fetch(`${API_ACOPIOS}/acopios`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cent_a_id: cent_a_id })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_acopios').reset();
                        cargarAcopios();
                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>

    <!--3. ELIMINAR HERRAMIENTA-->

    <script>
        document.getElementById('form_eliminacion_herramientas').addEventListener('submit', function (e) {
            e.preventDefault();

            const herram_id = parseInt(document.getElementById('herram_id_eliminar').value.trim(), 10);

            if (herram_id == "") {
                alert("Por favor, ingrese una id válida");
                return;
            }
            if (isNaN(herram_id) || herram_id <= 0) {
                alert("⚠️ El ID debe ser un número entero mayor a 0.");
                return;
            }
            fetch(`${API_HERRAMIENTAS}/herramientas`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ herram_id: herram_id })
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    // Medio rústico pero funciona
                    if (respuesta.mensaje.includes("con éxito")) {
                        document.getElementById('form_eliminacion_herramientas').reset();
                        cargarHerramientas();

                    }
                })
                .catch(err => {
                    console.error("Error al borrar:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>

    <!--3. Rechazar INCIDENCIA-->

    <script>
        document.getElementById('form_rechazar_incidencia').addEventListener('submit', function (e) {
            e.preventDefault();

            // 1. Obtención de datos
            const inc_id = parseInt(document.getElementById('inc_id_rechazar').value.trim(), 10);
            const motivo = document.getElementById('inc_motivo_rechazo').value.trim();

            // 2. Validación del ID de la incidencia
            if (isNaN(inc_id) || inc_id <= 0) {
                alert("⚠️ El ID de la incidencia debe ser un número entero mayor a 0.");
                document.getElementById('inc_id_rechazar').focus();
                return;
            }

            // 3. Validación del motivo
            if (motivo === "") {
                alert("⚠️ Por favor, ingrese el motivo del rechazo.");
                document.getElementById('inc_motivo_rechazo').focus();
                return;
            }

            // 4. Armamiento del paquete JSON
            const incidencia_rechazada = {
                inc_id: inc_id,
                inc_descripcion: motivo
            };

            // 5. Envío de los datos a la API
            fetch(`${API_INCIDENCIAS}/rechazar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(incidencia_rechazada)
            })
                .then(response => {
                    return response.json();
                })
                .then(respuesta => {
                    alert(respuesta.mensaje);

                    if (respuesta.mensaje.includes("correctamente")) {
                        document.getElementById('form_rechazar_incidencia').reset();
                        cargarIncidencias();
                    }
                })
                .catch(err => {
                    console.error("Error al rechazar incidencia:", err);
                    alert("Hubo un fallo crítico al conectar con la API.");
                });
        });
    </script>

    <!--Cargar la tabla de las rutas-->
    <script>
        function loadRutas(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            // Encabezados de la tabla de rutas
            let headers = ["Ruta", "Fecha", "Camión Asignado", "Contenedores", "Estado General"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold text-center";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            // Recorremos el árbol de rutas agrupadas que viene del backend
            data.forEach(ruta_item => {
                let row = table.insertRow();

                // Columna 1 y 2: Línea única tradicional
                row.insertCell().innerHTML = `# ${ruta_item.ruta_id}`;
                row.insertCell().innerHTML = ruta_item.ruta_fecha;

                // Columna 3: Badge estático en una sola línea
                row.insertCell().innerHTML = `<span class="badge bg-dark text-white p-2">${ruta_item.ruta_camion}</span>`;

                // Columna 4: Celda compleja con bucle interno para los contenedores
                let celda_contenedores = row.insertCell();
                //lo de abajo abre el div para los IDs de los contenedores
                let contenedores_html = '<div class="d-flex flex-column gap-1 align-items-center">';
                ruta_item.contenedores.forEach(cont_item => {
                    let badge_class = cont_item.cont_vaciado ? 'bg-success' : 'bg-danger';
                    let texto_estado = cont_item.cont_vaciado ? 'Vaciado' : 'Pendiente';
                    //con el += iremos sumando IDs de contenedores(concatenando)
                    contenedores_html += `
                        <span class="badge ${badge_class} p-2 text-white" style="width: 110px; font-size: 0.85rem;">
                             ID ${cont_item.cont_id} <br>
                            <small>${texto_estado}</small>
                        </span>
                    `;
                });
                //ya con esto, todo lo que creó el += del span, lo copiamos y lo pegamos junto con este cierre del div
                contenedores_html += '</div>';
                celda_contenedores.innerHTML = contenedores_html;

                const ruta_completada = ruta_item.contenedores.every(c => c.cont_vaciado === true);
                let celda_estado = row.insertCell();
                let clase_estado = ruta_completada ? "bg-success" : "bg-warning text-dark";
                let texto_ruta = ruta_completada ? " Completada" : " En Progreso";

                celda_estado.innerHTML = `<span class="badge ${clase_estado} text-uppercase p-2">${texto_ruta}</span>`;
            });

            // tiramos la tabla en el contenedor div del HTML
            let div_contenedor = document.getElementById("tabla_rutas");
            if (div_contenedor) {
                div_contenedor.innerHTML = "";
                div_contenedor.appendChild(table);
            }
        }
    </script>
    <!--5. Cargar la tabla de camiones-->
    <script>
        function loadCamiones(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            // Encabezados
            let headers = ["Matrícula", "Tipo", "Modelo", "Estado", "Capacidad"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            // Filas
            data.forEach(c => {
                let row = table.insertRow();

                row.insertCell().innerHTML = `<span class="fw-bold">${c.cam_matricula}</span>`;
                row.insertCell().innerHTML = c.cam_tipo;
                row.insertCell().innerHTML = c.cam_modelo;
                // Celda del estado con color
                let estado_cell = row.insertCell();
                let badge_estado = c.cam_estado === "funcional" ? "bg-success" : "bg-danger";
                row.insertCell().innerHTML = c.cam_capacidad;

                estado_cell.innerHTML = `<span class="badge ${badge_estado} text-uppercase">${c.cam_estado}</span>`;
            });

            let dvTable = document.getElementById("tabla_camiones");
            if (dvTable) {
                dvTable.innerHTML = "";
                dvTable.appendChild(table);
            }
        }
    </script>
    <!-- 5. Cargar la tabla de contendores-->
    <script>
        function loadContenedores(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            // Encabezados específicos para la infraestructura
            let headers = ["ID", "Calle / Ubicación", "Estado", "Tipo"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            // Mapeo y renderizado de los datos de la base de datos
            data.forEach(c => {
                let row = table.insertRow();

                // Celdas, en la primera columna de headers añade para la primera fila el primer insert
                row.insertCell().innerHTML = c.cont_id;
                row.insertCell().innerHTML = c.cont_calle;

                // Celda del estado con un badge de color dinámico
                let estado_cell = row.insertCell();
                let badge_estado = "bg-secondary"; // Por defecto

                if (c.cont_estado === "funcional") {
                    badge_estado = "bg-success";   // Verde
                } else if (c.cont_estado === "desbordado") {
                    badge_estado = "bg-warning text-dark"; // Amarillo / Naranja
                } else if (c.cont_estado === "roto") {
                    badge_estado = "bg-danger";    // Rojo
                }
                let tipo_cell = row.insertCell();
                let badge_tipo = "bg-secondary";

                if (c.cont_tipo === "volqueta") {
                    badge_tipo = "bg-warning text-dark";   // Verde
                } else if (c.cont_tipo === "reciclaje") {
                    badge_tipo = "bg-info text-dark"; // Celeste 
                }

                tipo_cell.innerHTML = `<span class="badge ${badge_tipo} text-uppercase">${c.cont_tipo}</span>`;
                estado_cell.innerHTML = `<span class="badge ${badge_estado} text-uppercase">${c.cont_estado}</span>`;
            });

            let dvTable = document.getElementById("tabla_contenedores");
            if (dvTable) {
                dvTable.innerHTML = "";
                dvTable.appendChild(table);
            }
        }
    </script>
    <!--5. Cargar la tabla de usuarios-->
    <script>
        function loadUsuarios(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            let headers = ["CI", "Nombre", "Email", "Rol", "Teléfono"];
            let header_row = table.insertRow();

            header_row.className = "table-light text-uppercase small font-weight-bold";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });
            data.forEach(u => {
                let row = table.insertRow();
                row.insertCell().innerHTML = u.usr_ci;
                row.insertCell().innerHTML = u.usr_name;
                row.insertCell().innerHTML = u.usr_email;
                row.insertCell().innerHTML = u.usr_rol;
                row.insertCell().innerHTML = u.usr_telefono;
            });
            let dvTable = document.getElementById("tabla_usuarios");
            dvTable.innerHTML = "";
            dvTable.appendChild(table);
        }
    </script>

    <!--5. Cargar la tabla de cuadrillas-->
    <script>
        function loadCuadrillas(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";
            // Encabezados específicos para la infraestructura
            let headers = ["ID", "Camión", "Recolector", "Nombre", "Apellido"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";
            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            // Mapeo y renderizado de los datos de la base de datos
            data.forEach(c => {
                let row = table.insertRow();

                // Celdas, en la primera columna de headers añade para la primera fila el primer insert
                row.insertCell().innerHTML = c.cuad_id;
                row.insertCell().innerHTML = c.cuad_cam;
                row.insertCell().innerHTML = c.usr_ci;
                row.insertCell().innerHTML = c.usr_name;
                row.insertCell().innerHTML = c.usr_apellido;

            });

            let dvTable = document.getElementById("tabla_cuadrillas");
            if (dvTable) {
                dvTable.innerHTML = "";
                dvTable.appendChild(table);
            }
        }
    </script>

    <!--5. Cargar la tabla de centros de acopio-->
    <script>
        function loadAcopios(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";
            // Encabezados específicos para la infraestructura
            let headers = ["ID", "Horario", "Dirección", "Capacidad", "Llenado"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";
            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            //  los datos de la base de datos, acá los tableamos
            data.forEach(c => {
                let row = table.insertRow();

                // 1. ID
                row.insertCell().innerHTML = c.cent_a_id;

                // 2. Horario: juntamos apertura y cierre. 
                // Usamos substring(primer_caracter, 5) para quitar los segundos porque sql devuelve 06:00:00
                let aperturaFormateada = c.hora_apertura.substring(0, 5);
                let cierreFormateado = c.hora_cierre.substring(0, 5);
                row.insertCell().innerHTML = `${aperturaFormateada} - ${cierreFormateado}`;

                // 3. Dirección: juntamos calle y número de porta
                row.insertCell().innerHTML = `${c.calle} ${c.num_puerta}`;

                // 4. Capacidad
                row.insertCell().innerHTML = c.cent_a_capacidad;

                // 5. Llenado
                row.insertCell().innerHTML = c.cent_a_llenado;

                let dvTable = document.getElementById("tabla_acopios");
                if (dvTable) {
                    dvTable.innerHTML = "";
                    dvTable.appendChild(table);
                }
            });
        }
    </script>

    <!--5. Cargar la tabla de herramientas-->

    <script>
        function loadHerramientas(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            // Encabezados de la tabla
            let headers = ["ID", "Tipo", "Establecimiento"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            // Datos de las herramientas
            data.forEach(h => {
                let row = table.insertRow();

                row.insertCell().innerHTML = h.herram_id;
                row.insertCell().innerHTML = h.herram_tipo;
                row.insertCell().innerHTML = h.herram_estab;
            });

            // Colocar la tabla dentro del div correspondiente
            let dvTable = document.getElementById("tabla_herramientas");

            if (dvTable) {
                dvTable.innerHTML = "";
                dvTable.appendChild(table);
            }
        }
    </script>

    <!--5. Cargar la tabla de incidencias-->
    <script>
        function loadIncidencias(data) {
            let table = document.createElement("table");
            table.className = "table table-hover align-middle";

            let headers = ["ID", "Tema", "Descripción", "municipio", "Tipo de residuo", "Fecha de apertura", "Fecha de cierre", "Cuadrilla", "Dirección"];
            let header_row = table.insertRow();
            header_row.className = "table-light text-uppercase small font-weight-bold";

            headers.forEach(h => {
                let th = document.createElement("th");
                th.innerHTML = h;
                header_row.appendChild(th);
            });

            data.forEach(i => {
                let row = table.insertRow();

                //  Evaluamos el estado y pintamos TODA la fila de acuerdo a ello
                let estado = i.inc_estado ? i.inc_estado.toLowerCase().trim() : '';

                if (estado === 'abierta') {
                    row.className = 'table-warning'; // Amarillo
                } else if (estado === 'cerrada') {
                    row.className = 'table-success'; // Verde
                } else if (estado === 'en espera' || estado === 'espera') {
                    row.className = 'table-secondary'; // Gris
                }

                row.insertCell().innerHTML = i.inc_id;
                row.insertCell().innerHTML = i.inc_tema;
                row.insertCell().innerHTML = i.inc_descripcion;
                row.insertCell().innerHTML = i.inc_municipio;
                row.insertCell().innerHTML = i.inc_tipo_residuo;
                let aperturaFormateada = i.inc_fecha_hora.substring(0, 16);
                row.insertCell().innerHTML = aperturaFormateada;


                if (i.incidencia_fecha_hora_cierre) {
                    row.insertCell().innerHTML = i.incidencia_fecha_hora_cierre;
                } else {
                    row.insertCell().innerHTML = '<span class="badge text-bg-warning">ABIERTA</span>';
                }
                row.insertCell().innerHTML = i.inc_cuadrilla;
                row.insertCell().innerHTML = `${i.inc_calle} ${i.inc_puerta}`;


            });

            let dvTable = document.getElementById("tabla_incidencias");

            if (dvTable) {
                dvTable.innerHTML = "";
                dvTable.appendChild(table);
            }
        }
    </script>

    <!--6. Mapa para elegir contenedores-->
    <script>
        let mapaSelector = null;
        let marcadorSeleccion = null;

        // Llamá a esta función cuando el formulario pase a ser visible (se le quite la clase d-none)
        function inicializarMapaSelector(id_mapa) {

            if (mapaSelector && mapaSelector.getContainer().id !== id_mapa) {
                mapaSelector.remove();
                mapaSelector = null;
                marcadorSeleccion = null;
            }
            // Si ya fue creado anteriormente, solo recalculamos el tamaño por si estaba oculto
            if (mapaSelector) {
                setTimeout(() => mapaSelector.invalidateSize(), 200);//Obliga a leaflet a recalcular el tamaño del mapa
                return;
            }

            mapaSelector = L.map(id_mapa).setView([-34.8865, -56.1210], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapaSelector);

            // 1. Evento CLICK sobre el mapa para fijar / mover el marcador
            mapaSelector.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                actualizarPosicionMarcador(lat, lng);
            });
        }

        // Función auxiliar para mover el pin y rellenar los inputs
        function actualizarPosicionMarcador(lat, lng) {
            // Si el marcador no existe, lo creamos y lo hacemos draggable (arrastrable)
            if (!marcadorSeleccion) {
                marcadorSeleccion = L.marker([lat, lng], { draggable: true }).addTo(mapaSelector);

                // Evento al terminar de arrastrar el pin con el mouse
                marcadorSeleccion.on('dragend', function (e) {
                    const posicion = e.target.getLatLng();
                    asignaCoordenadasInputs(posicion.lat, posicion.lng);
                });
            } else {
                // Si ya existía, simplemente lo movemos
                marcadorSeleccion.setLatLng([lat, lng]);
            }

            asignaCoordenadasInputs(lat, lng);
        }

        // Escribe los valores recortados a 5 decimales en los inputs de HTML,
        //con esto tenemos una cisión de ~1 metro
        function asignaCoordenadasInputs(lat, lng) {

            const idMapaActual = mapaSelector ? mapaSelector.getContainer().id : 'mapa_selector';
            const es_registro = (idMapaActual === 'mapa_selector');

            const id_lat = es_registro ? 'cont_latitud' : 'mod_cont_latitud';
            const id_lng = es_registro ? 'cont_longitud' : 'mod_cont_longitud';

            document.getElementById(id_lat).value = lat.toFixed(5);
            document.getElementById(id_lng).value = lng.toFixed(5);
        }
    </script>

    <!--6. Camiones y usuarios para registro de cuadrillas-->
    <script>
        function cargarCamionesEnSelector(idSelector) {
            const selector = document.getElementById(idSelector);
            if (!selector) return Promise.resolve();

            return fetch(`${API_CAMIONES}/camiones`)
                .then(response => response.json())
                .then(camiones_lista => {
                    selector.innerHTML = '<option value="" disabled selected>Seleccione un camión</option>';

                    const camiones_activos = camiones_lista.filter(c => c.cam_estado === "funcional");

                    if (camiones_activos.length === 0) {
                        selector.innerHTML = '<option value="" disabled>No hay camiones funcionales disponibles</option>';
                        return;
                    }

                    camiones_activos.forEach(c => {
                        selector.innerHTML += `<option value="${c.cam_matricula}">${c.cam_matricula} (${c.cam_tipo})</option>`;
                    });
                })
                .catch(err => {
                    console.error(`Error al cargar camiones en #${idSelector}:`, err);
                    selector.innerHTML = '<option value="" disabled>Error al cargar camiones</option>';
                });
        }

        // Carga recolectores en los dos selectores del Alta
        function cargarRecolectoresEnSelectores() {
            const selector_ci1 = document.getElementById('cuad_ci1');
            const selector_ci2 = document.getElementById('cuad_ci2');

            if (!selector_ci1 || !selector_ci2) return Promise.resolve();

            return fetch(`${API_URL}/usuarios`)
                .then(response => response.json())
                .then(usuarios_lista => {
                    const defaultOption = '<option value="" disabled selected>Seleccione un recolector</option>';
                    selector_ci1.innerHTML = defaultOption;
                    selector_ci2.innerHTML = defaultOption;

                    const recolectores = usuarios_lista.filter(u => u.usr_rol === "recolector");

                    if (recolectores.length < 2) {
                        const msg = '<option value="" disabled>No hay suficientes recolectores registrados</option>';
                        selector_ci1.innerHTML = msg;
                        selector_ci2.innerHTML = msg;
                        return;
                    }

                    recolectores.forEach(r => {
                        const opcionHTML = `<option value="${r.usr_ci}">${r.usr_name} ${r.usr_apellido} - CI: ${r.usr_ci}</option>`;
                        selector_ci1.innerHTML += opcionHTML;
                        selector_ci2.innerHTML += opcionHTML;
                    });
                })
                .catch(err => {
                    console.error("Error al cargar recolectores:", err);
                    selector_ci1.innerHTML = '<option value="" disabled>Error al cargar recolectores</option>';
                    selector_ci2.innerHTML = '<option value="" disabled>Error al cargar recolectores</option>';
                });
        }
    </script>

    <!--6. Cargar cuadrillas para asignarlas-->
    <script>
        //Gran demostración de como funciona el .then(blabla =>)
        function inicializarFormularioIncidencias() {
            const selector_cuadrilla = document.getElementById('inc_cuadrilla');

            if (!selector_cuadrilla) return;

            fetch('../controlador/api_cuadrillas.php/cuadrillas')
                .then(response => response.json())
                .then(cuadrillas_lista => {

                    selector_cuadrilla.innerHTML =
                        '<option value="" disabled selected>Seleccione una cuadrilla</option>';

                    const cuadrillas_activas = cuadrillas_lista.filter(c => c.cuad_activa == 1);

                    if (cuadrillas_activas.length === 0) {
                        selector_cuadrilla.innerHTML =
                            '<option value="" disabled>No hay cuadrillas activas disponibles</option>';
                        return;
                    }

                    cuadrillas_activas.forEach(cuadrilla => {
                        selector_cuadrilla.innerHTML += `
                        <option value="${cuadrilla.cuad_id}">
                            Cuadrilla ${cuadrilla.cuad_id}
                        </option>
                    `;
                    });
                })
                .catch(err => {
                    console.error("Error al cargar cuadrillas para el formulario:", err);
                    selector_cuadrilla.innerHTML =
                        '<option value="" disabled>Error al cargar cuadrillas</option>';
                });
        }
    </script>

    <!--Cerrar sesión-->
    <script>
        document.getElementById('btn-logout').addEventListener('click', function (e) {
            e.preventDefault();

            const data = {
                usr_key: document.cookie
                    .split('; ')
                    .find(row => row.startsWith('token='))
                    .split('=')[1]
            };

            fetch('../controlador/api_usuarios.php/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sesión cerrada correctamente');
                        // Eliminar la cookie
                        document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        // Recargar la página
                        window.location.href = '../vista/login.html';
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    alert('Error al cerrar sesión');
                    console.error(error);
                });
        });
    </script>
    <!--Cargar el mapa de circuitos-->
    <script>
        let tabla_circuitos;

        function cargarCircuitos() {


            fetch('../controlador/api_circuitos.php/circuitos/municipio')
                .then(response => response.json())
                .then(circuitos => {
                    if (!tabla_circuitos) {
                        tabla_circuitos = L.map('tabla_circuitos').setView([-34.90, -56.16], 12);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(tabla_circuitos);

                    }

                    const poligonos = {};

                    circuitos.forEach(circuito => {

                        //circuito es un objeto, circuito.poli_id accede al id del poligono del circuito
                        //poligono es un diccionario clave:valor, la clave circuito tiene todo el objeto circuito 
                        //cuyo indice es igual a circuito.poli_id
                        if (!poligonos[circuito.poli_id]) {
                            poligonos[circuito.poli_id] = {
                                circuito: circuito,
                                coordenadas: []
                            };
                        }

                        poligonos[circuito.poli_id].coordenadas.push([
                            Number(circuito.pv_latitud),
                            Number(circuito.pv_longitud)
                        ]);
                    });

                    Object.values(poligonos).forEach(poligono => {
                        //manda a leaflet a hacer un poligono con esta lista de coordenadas
                        L.polygon(poligono.coordenadas)
                            .addTo(tabla_circuitos)
                            .bindPopup(`
                        <strong>Circuito ${poligono.circuito.circ_id}</strong><br>
                        Tipo: ${poligono.circuito.circ_tipo}<br>
                        Municipio: ${poligono.circuito.zona_municipio}
                    `);
                    });
                })
                .catch(error => {
                    console.error("Error al cargar los circuitos:", error);
                    alert("⚠️ No se pudieron cargar los circuitos.");
                });
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script de Leaflet (Mapa) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</body>

</html>