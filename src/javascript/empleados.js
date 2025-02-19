// Función para búsqueda dinámica con AJAX
function buscarEmpleados() {
    const busqueda = document.getElementById('busqueda').value;
    const pagina = document.getElementById('pagina').value;

    fetch(`../src/config/components/section_empleados/buscar_empleados.php?busqueda=${busqueda}&pagina=${pagina}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('resultados').innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
}

// Función para cambiar de página
function cambiarPagina(pagina) {
    document.getElementById('pagina').value = pagina;
    buscarEmpleados();
}

// Abrir modal de agregar empleado
function abrirModalAgregar() {
    document.getElementById('modalAgregar').style.display = 'block';
}

// Cerrar modal de agregar empleado
function cerrarModalAgregar() {
    document.getElementById('modalAgregar').style.display = 'none';
}

// Guardar empleado
function guardarEmpleado(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('formAgregar'));

    fetch('../src/config/components/guardar_empleado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Mostrar mensaje de éxito o error
        cerrarModalAgregar();
        buscarEmpleados(); // Actualizar la tabla
    })
    .catch(error => console.error('Error:', error));
}

// Obtener datos de un empleado específico
function obtenerEmpleado(id) {
    fetch(`src/config/components/section_empleados/obtener_empleados.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            abrirModalEditar(data); // Abre el modal de edición con los datos
        })
        .catch(error => console.error('Error:', error));
}

// Abrir modal de edición con los datos del empleado
function abrirModalEditar(empleado) {
    document.getElementById('editarId').value = empleado.Numero_Empleado;
    document.getElementById('editarNombre').value = empleado.Nombre;
    document.getElementById('editarNumero').value = empleado.Numero_Empleado;
    document.getElementById('editarDepartamento').value = empleado.Departamento;
    document.getElementById('modalEditar').style.display = 'block';
}

// Cerrar modal de edición
function cerrarModalEditar() {
    document.getElementById('modalEditar').style.display = 'none';
}

// Guardar cambios al editar un empleado
function guardarCambios(event) {
    event.preventDefault();
    const formData = new FormData(document.getElementById('formEditar'));

    fetch('src/config/components/section_empleados/editar_empleado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data); // Mostrar mensaje de éxito o error
        cerrarModalEditar();
        buscarEmpleados(); // Actualizar la tabla
    })
    .catch(error => console.error('Error:', error));
}

// Eliminar empleado
function eliminarEmpleado(id) {
    if (confirm("¿Estás seguro de eliminar este empleado?")) {
        fetch(`src/config/components/section_empleados/eliminar_empleado.php?id=${id}`)
            .then(response => response.text())
            .then(data => {
                alert(data); // Mostrar mensaje de éxito o error
                buscarEmpleados(); // Actualizar la tabla
            })
            .catch(error => console.error('Error:', error));
    }
}

// Cargar resultados al inicio
window.onload = buscarEmpleados;