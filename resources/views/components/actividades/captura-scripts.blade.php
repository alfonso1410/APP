@props([
    'rutaTabla' => route('admin.actividades.json.tabla'),
    'rutaStore' => route('admin.actividades.store'),
    'rutaUpdateBase' => url('/admin/actividades-diarias/actividades'),
    'rutaDeleteBase' => url('/admin/actividades-diarias/actividades'),
    'rutaGuardarCalificacion' => route('admin.actividades.guardarCalificacion'),
    'rutaSincronizar' => route('admin.actividades.sincronizar'),
    'rutaGruposReplicables' => route('admin.actividades.json.gruposReplicables'),
    'rutaNivelesGradosBase' => url('/admin/json/niveles'),
    'rutaGradosExtra' => route('admin.json.grados.extra'),
    'rutaGradosGruposBase' => url('/admin/json/grados'),
    'rutaGruposMateriasBase' => url('/admin/json/grupos'),
])

<script>
function actividadesDiariasManager() {
    return {
        // === RUTAS INYECTADAS ===
        rutas: {
            tabla: @json($rutaTabla),
            store: @json($rutaStore),
            updateBase: @json($rutaUpdateBase),
            deleteBase: @json($rutaDeleteBase),
            guardarCalificacion: @json($rutaGuardarCalificacion),
            sincronizar: @json($rutaSincronizar),
            gruposReplicables: @json($rutaGruposReplicables),
            nivelesGradosBase: @json($rutaNivelesGradosBase),
            gradosExtra: @json($rutaGradosExtra),
            gradosGruposBase: @json($rutaGradosGruposBase),
            gruposMateriasBase: @json($rutaGruposMateriasBase),
        },

        // === ESTADO ===
        selectedNivel: '',
        selectedGrado: '',
        selectedGrupo: '',
        selectedMateria: '',
        selectedPeriodo: '',
        periodoCerrado: false,
        tablaCargada: false,
        criterioActivo: null,
        filtroRapido: 'todos',

        // === DATOS ===
        grados: [],
        grupos: [],
        materias: [],
        gruposReplicables: [],
        criterios: [],
        actividades: [],
        alumnos: [],

        // === MODALES ===
        modalNuevaActividad: false,
        modalEditarActividad: false,
        modalObs: false,

        // === FORMS ===
        formActividad: {
            nombre_actividad: '',
            descripcion: '',
            materia_criterio_id: '',
            valor_maximo: 10,
            fecha_actividad: '',
            grupos_replicar: []
        },
        formEditarActividad: {
            id: null,
            nombre_actividad: '',
            descripcion: '',
            materia_criterio_id: '',
            valor_maximo: 10,
            fecha_actividad: ''
        },
        obsData: { alumno: null, act: null, alumnoNombre: '', actNombre: '', texto: '' },

        // === LOADING & DEBOUNCE ===
        guardando: false,
        guardandoActividad: false,
        guardandoCambios: false,
        loading: { grados: false, grupos: false, materias: false, tabla: false },
        debounceTimeout: null,
        cambiosPendientes: [],
        toast: false,
        toastMsg: '',

        getFechaLocalActual() {
        const d = new Date();
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
        },
        // === COMPUTED ===
        get hayDesincronizados() {
            return this.criterios.some(c => c.estado === 'desincronizado');
        },

        get alumnosFiltrados() {
    //  Protección contra datos no inicializados
    if (!Array.isArray(this.alumnos)) return [];
    
    const filtro = this.filtroRapido || 'todos';
    
    if (filtro === 'todos') {
        return this.alumnos;
    }
    
    if (filtro === 'desincronizados') {
        const actividades = this.getActividadesDelCriterioActivo();
        const actsDesincIds = actividades
            .filter(a => a && a.desincronizada)
            .map(a => a.id);
        
        if (actsDesincIds.length === 0) return [];
        
        return this.alumnos.filter(alumno => {
            if (!alumno || !alumno.calificaciones) return false;
            return actsDesincIds.some(actId => 
                alumno.calificaciones[actId]?.estado === 'ENTREGADO'
            );
        });
    }
    
    if (filtro === 'pendientes') {
        const actividades = this.getActividadesDelCriterioActivo();
        if (!actividades || actividades.length === 0) return [];
        
        return this.alumnos.filter(alumno => {
            if (!alumno) return false;
            return actividades.some(act => {
                const cal = alumno.calificaciones?.[act.id];
                // Pendiente = sin calificación O entregado pero sin valor numérico
                return !cal || (cal.estado === 'ENTREGADO' && (cal.valor === null || cal.valor === '' || isNaN(Number(cal.valor))));
            });
        });
    }
    
    return this.alumnos;
},

        // === INIT CON AUTOCARGA ===
        async init() {
            const params = new URLSearchParams(window.location.search);
            const nivelId   = params.get('nivel_id');
            const gradoId   = params.get('grado_id');
            const grupoId   = params.get('grupo_id');
            const materiaId = params.get('materia_id');
            const periodoId = params.get('periodo_id');
            const criterioId = params.get('criterio_id');

            if (periodoId) this.selectedPeriodo = periodoId;
            if (nivelId)   this.selectedNivel   = nivelId;

            if (gradoId && nivelId) {
                this.loading.grados = true;
                try {
                    const url = nivelId === 'extracurricular'
                        ? this.rutas.gradosExtra
                        : `${this.rutas.nivelesGradosBase}/${nivelId}/grados`;
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    this.grados = await res.json();
                } catch (e) { console.error(e); }
                this.loading.grados = false;
                await this.$nextTick();
                this.selectedGrado = gradoId;
            }

            if (grupoId && gradoId) {
                this.loading.grupos = true;
                try {
                    const res = await fetch(`${this.rutas.gradosGruposBase}/${gradoId}/grupos`, { headers: { 'Accept': 'application/json' } });
                    this.grupos = await res.json();
                } catch (e) { console.error(e); }
                this.loading.grupos = false;
                await this.$nextTick();
                this.selectedGrupo = grupoId;
            }

            if (materiaId && grupoId) {
                this.loading.materias = true;
                try {
                    const res = await fetch(`${this.rutas.gruposMateriasBase}/${grupoId}/materias`, { headers: { 'Accept': 'application/json' } });
                    this.materias = await res.json();
                } catch (e) { console.error(e); }
                this.loading.materias = false;
                await this.$nextTick();
                this.selectedMateria = materiaId;
            }

            if (this.selectedGrupo && this.selectedMateria && this.selectedPeriodo) {
                await this.cargarTabla();
                if (criterioId) {
                    const cid = parseInt(criterioId, 10);
                    if (this.criterios.some(c => c.id === cid)) this.criterioActivo = cid;
                }
            }
        },

        // === CASCADA DE SELECTORES ===
        async nivelChanged() {
            this.selectedGrado = ''; this.selectedGrupo = ''; this.selectedMateria = '';
            this.grados = []; this.grupos = []; this.materias = [];
            this.tablaCargada = false;
            if (!this.selectedNivel) { this.loading.grados = false; return; }
            this.loading.grados = true;
            try {
                const url = this.selectedNivel === 'extracurricular'
                    ? this.rutas.gradosExtra
                    : `${this.rutas.nivelesGradosBase}/${this.selectedNivel}/grados`;
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                this.grados = await res.json();
            } catch (e) { console.error('Error cargando grados:', e); this.grados = []; }
            this.loading.grados = false;
        },

        async gradoChanged() {
            this.selectedGrupo = ''; this.selectedMateria = '';
            this.grupos = []; this.materias = [];
            this.tablaCargada = false;
            if (!this.selectedGrado) return;
            this.loading.grupos = true;
            try {
                const res = await fetch(`${this.rutas.gradosGruposBase}/${this.selectedGrado}/grupos`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                this.grupos = await res.json();
            } catch (e) { console.error(e); this.grupos = []; }
            this.loading.grupos = false;
        },

        async grupoChanged() {
            this.selectedMateria = ''; this.materias = [];
            this.tablaCargada = false;
            if (!this.selectedGrupo) return;
            this.loading.materias = true;
            try {
                const res = await fetch(`${this.rutas.gruposMateriasBase}/${this.selectedGrupo}/materias`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                this.materias = await res.json();
            } catch (e) { console.error(e); this.materias = []; }
            this.loading.materias = false;
        },

        // === CARGAR TABLA ===
       async cargarTabla() {
    if (!this.selectedGrupo || !this.selectedMateria || !this.selectedPeriodo) {
        alert('Por favor selecciona Periodo, Grupo y Materia.');
        return;
    }

    clearTimeout(this.debounceTimeout);
    if (this.cambiosPendientes.length > 0) await this.flushCambiosPendientes();

    this.loading.tabla = true;

    // 1. Detección reactiva de periodo cerrado
    const periodosCatalogo = @json($periodos ?? []);
    const pActual = periodosCatalogo.find(p => String(p.periodo_id) === String(this.selectedPeriodo));
    
    if (pActual) {
        this.periodoCerrado = String(pActual.estado).toUpperCase() === 'CERRADO';
    } else {
        const selectPeriodo = document.querySelector('select[x-model="selectedPeriodo"]');
        if (selectPeriodo && selectPeriodo.selectedIndex >= 0) {
            const opt = selectPeriodo.options[selectPeriodo.selectedIndex];
            this.periodoCerrado = opt?.getAttribute('data-estado') === 'CERRADO';
        } else {
            this.periodoCerrado = false;
        }
    }

    try {
        const params = new URLSearchParams({
            grupo_id: this.selectedGrupo,
            materia_id: this.selectedMateria,
            periodo_id: this.selectedPeriodo
        });

        const res = await fetch(`${this.rutas.tabla}?${params.toString()}`, { 
            headers: { 'Accept': 'application/json' } 
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        // 2. RESET LIMPIO DE ESTADO (Desconecta las referencias reactivas viejas)
        this.criterios = [];
        this.actividades = [];
        this.alumnos = [];
        await this.$nextTick(); // Espera a que Alpine limpie el DOM iterado

        // 3. ASIGNACIÓN REFRESCADA
        this.criterios = data.criterios || [];
        this.actividades = data.actividades || [];

        // Asegura que cada alumno tenga su objeto de calificaciones instanciado explícitamente
        this.alumnos = (data.alumnos || []).map(alumno => {
            if (!alumno.calificaciones) alumno.calificaciones = {};
            return alumno;
        });

        // Mantener criterio activo si aún existe, sino seleccionar el primero
        if (!this.criterioActivo || !this.criterios.some(c => c.id === this.criterioActivo)) {
            this.criterioActivo = this.criterios[0]?.id || null;
        }

        this.tablaCargada = true;
    } catch (e) {
        console.error('Error cargando tabla:', e);
        this.mostrarToast('⚠ Error al cargar calificaciones.');
    } finally {
        this.loading.tabla = false;
    }
},
        // === HELPERS DE TABLA ===
        getCriterioActivo() {
            return this.criterios.find(c => c.id === this.criterioActivo) || { nombre: '', peso: '', estado: 'sin_actividades', calculadoEn: '—', calculadoPor: '—' };
        },

        getActividadesDelCriterioActivo() {
            return this.actividades.filter(a => a.criterio_id === this.criterioActivo);
        },

        getCalificacion(alumno, actId) {
            if (!alumno.calificaciones) alumno.calificaciones = {};
            if (!alumno.calificaciones[actId]) {
                alumno.calificaciones[actId] = { valor: null, estado: 'ENTREGADO', observaciones: '' };
            }
            return alumno.calificaciones[actId];
        },

        validarRango(event, maximo) {
            const val = parseFloat(event.target.value);
            const max = parseFloat(maximo);
            if (isNaN(val)) return;
            event.target.classList.toggle('input-error', val > max || val < 0);
            event.target.classList.toggle('input-success', val >= 0 && val <= max);
        },

        getInputClass(cal, maximo) {
            if (!cal || cal.estado !== 'ENTREGADO') return '';
            const val = parseFloat(cal.valor);
            const max = parseFloat(maximo);
            if (!isNaN(val) && val > max) return 'input-error';
            if (!isNaN(val) && val >= 0) return 'input-success';
            return '';
        },

        calcularPromedioCriterio(alumno) {
            const acts = this.getActividadesDelCriterioActivo();
            if (acts.length === 0) return { 'n/a': true, valor: 0 };
            let sumaObtenida = 0, sumaMaxima = 0, tieneEntregas = false;
            acts.forEach(act => {
                const cal = alumno.calificaciones?.[act.id];
                if (!cal || cal.estado === 'FALTA_JUSTIFICADA') return;
                sumaMaxima += parseFloat(act.valorMaximo) || 0;
                tieneEntregas = true;
                if (cal.estado === 'ENTREGADO') {
                    const v = parseFloat(cal.valor);
                    if (!isNaN(v)) sumaObtenida += v;
                }
            });
            if (sumaMaxima === 0 || !tieneEntregas) return { 'n/a': true, valor: 0 };
            return { 'n/a': false, valor: (sumaObtenida / sumaMaxima) * 10 };
        },

        // === AUTO-GUARDADO ===
        autoGuardarDebounced(alumno, act) {
            clearTimeout(this.debounceTimeout);
            const cal = this.getCalificacion(alumno, act.id);
            const key = `${alumno.id}-${act.id}`;
            this.cambiosPendientes = this.cambiosPendientes.filter(c => c.key !== key);
            this.cambiosPendientes.push({
                key, alumno_id: alumno.id, actividad_id: act.id,
                calificacion_obtenida: cal.valor, estado_entrega: cal.estado,
                observaciones: cal.observaciones, criterio_id: act.criterio_id, act_ref: act
            });
            this.debounceTimeout = setTimeout(() => this.flushCambiosPendientes(), 800);
        },

        async flushCambiosPendientes() {
            if (this.cambiosPendientes.length === 0 || this.guardandoCambios) return;
            this.guardandoCambios = true;
            const cambios = [...this.cambiosPendientes];
            this.cambiosPendientes = [];
        
        const payloadCalificaciones = cambios.map(c => {
        let valorFinal = null;

        if (c.estado_entrega === 'ENTREGADO') {
            valorFinal = (c.calificacion_obtenida !== null && c.calificacion_obtenida !== '') 
                ? parseFloat(c.calificacion_obtenida) 
                : null;
        } else if (c.estado_entrega === 'NO_ENTREGO') {
            valorFinal = 0; // Garantizar valor 0 cuando no entregó
        }

        return {
            actividad_id: parseInt(c.actividad_id, 10),
            alumno_id: parseInt(c.alumno_id, 10),
            calificacion_obtenida: valorFinal,
            estado_entrega: c.estado_entrega,
            observaciones: c.observaciones || null
        };
    });

    try {
        const res = await fetch(this.rutas.guardarCalificacion, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ calificaciones: payloadCalificaciones })
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            console.error('Errores de validación 422:', errData);
            throw new Error('Error guardando');
        }

        cambios.forEach(c => {
            const crit = this.criterios.find(cr => cr.id === c.criterio_id);
            if (crit) crit.estado = 'desincronizado';
            if (c.act_ref) c.act_ref.desincronizada = true;
        });

    } catch (e) {
        console.error('Flush error:', e);
        // Volver a encolar cambios si falló la red
        this.cambiosPendientes = [...cambios, ...this.cambiosPendientes];
        this.mostrarToast('⚠ Error al guardar calificaciones.');
    } finally {
        this.guardandoCambios = false;
    }
        },

        // === SINCRONIZAR ===
        async sincronizar() {
            clearTimeout(this.debounceTimeout);
            if (this.cambiosPendientes.length > 0) await this.flushCambiosPendientes();
            this.guardando = true;
            try {
                const res = await fetch(this.rutas.sincronizar, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ grupo_id: this.selectedGrupo, materia_id: this.selectedMateria, periodo_id: this.selectedPeriodo })
                });
                const data = await res.json();
                if (data.success) {
                    this.criterios.forEach(c => { if (c.actividadesRegistradas > 0) c.estado = 'al_dia'; });
                    this.actividades.forEach(a => a.desincronizada = false);
                    this.mostrarToast('¡Calificaciones actualizadas correctamente!');
                } else { alert(data.mensaje); }
            } catch (e) { alert('Error al sincronizar calificaciones.'); }
            this.guardando = false;
        },

        // === NUEVA ACTIVIDAD ===
       async abrirModalNuevaActividad() {
    // 1. Asegurar que materia_criterio_id tenga un valor válido
    const criterioInicial = this.criterioActivo || (this.criterios && this.criterios.length > 0 ? this.criterios[0].id : '');

    this.formActividad = {
        nombre_actividad: '',
        descripcion: '',
        materia_criterio_id: criterioInicial,
        valor_maximo: 10,
        fecha_actividad: this.getFechaLocalActual(),
        grupos_replicar: []
    };
    this.gruposReplicables = [];

    // 2. Obtener grado_id de forma segura (soporta tanto Admin como Maestro)
    const gradoId = this.selectedGrado 
        || (this.contexto && this.contexto.gradoId) 
        || (this.grupoSeleccionado && this.grupoSeleccionado.grado_id) 
        || null;

    const grupoId = this.selectedGrupo 
        || (this.contexto && this.contexto.grupoId) 
        || (this.grupoSeleccionado && this.grupoSeleccionado.grupo_id) 
        || null;

    // Solo consultar grupos replicables si ambos IDs numéricos existen
    if (gradoId && grupoId) {
        try {
            const res = await fetch(`${this.rutas.gruposReplicables}?grado_id=${gradoId}&grupo_id_actual=${grupoId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                this.gruposReplicables = await res.json();
            } else {
                console.warn('No se pudieron obtener grupos replicables. Status:', res.status);
                this.gruposReplicables = [];
            }
        } catch (e) {
            console.error('Error al obtener grupos replicables:', e);
            this.gruposReplicables = [];
        }
    }

    this.modalNuevaActividad = true;
},

       async guardarNuevaActividad() {
    this.guardandoActividad = true;

    // Validar antes de enviar que los campos base existan
    if (!this.selectedGrupo || !this.selectedMateria || !this.selectedPeriodo) {
        alert('Faltan datos de contexto (Grupo, Materia o Periodo).');
        this.guardandoActividad = false;
        return;
    }

    if (!this.formActividad.materia_criterio_id) {
        alert('Por favor selecciona un criterio.');
        this.guardandoActividad = false;
        return;
    }

    try {
        const payload = {
            grupo_id: this.selectedGrupo,
            materia_id: this.selectedMateria,
            periodo_id: this.selectedPeriodo,
            nombre_actividad: this.formActividad.nombre_actividad,
            descripcion: this.formActividad.descripcion,
            materia_criterio_id: this.formActividad.materia_criterio_id,
            valor_maximo: this.formActividad.valor_maximo,
            fecha_actividad: this.formActividad.fecha_actividad,
            grupos_replicar: this.formActividad.grupos_replicar || []
        };

        const res = await fetch(this.rutas.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (res.ok && data.success) {
            this.modalNuevaActividad = false;
            this.mostrarToast('Actividad creada correctamente');
            
            // Recargar tabla según el rol activo
            if (typeof this.cargarTablaYAutoenfocar === 'function') {
                await this.cargarTablaYAutoenfocar();
            } else {
                await this.cargarTabla();
            }
        } else {
            // Mostrar los errores específicos de validación de Laravel
            if (data.errors) {
                const mensajes = Object.values(data.errors).flat().join('\n');
                alert(`Errores de validación:\n${mensajes}`);
            } else {
                alert(data.mensaje || 'Error al guardar la actividad.');
            }
        }
    } catch (e) {
        console.error('Error al guardar actividad:', e);
        alert('Error inesperado al guardar la actividad.');
    } finally {
        this.guardandoActividad = false;
    }
},
        // === EDITAR ACTIVIDAD ===
        abrirModalEditarActividad(act) {
       

            this.formEditarActividad = {
                id: act.id, nombre_actividad: act.nombre,
                descripcion: act.descripcion || '',
                materia_criterio_id: act.criterio_id,
                valor_maximo: act.valorMaximo,
                fecha_actividad: act.fecha_actividad
            };
            this.modalEditarActividad = true;
        },

        async guardarEdicionActividad() {
            this.guardandoActividad = true;
            try {
                const res = await fetch(`${this.rutas.updateBase}/${this.formEditarActividad.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.formEditarActividad)
                });
                const data = await res.json();
                if (data.success) {
                    this.modalEditarActividad = false;
                    this.mostrarToast('Actividad actualizada correctamente');
                    await this.cargarTabla();
                } else { alert(data.mensaje); }
            } catch (e) { alert('Error al actualizar la actividad.'); }
            this.guardandoActividad = false;
        },

        // === ELIMINAR ACTIVIDAD ===
        async confirmarEliminarActividad(act) {
            if (!confirm(`¿Estás seguro de eliminar "${act.nombre}"?\nSe borrarán todas sus calificaciones.`)) return;
            try {
                const res = await fetch(`${this.rutas.deleteBase}/${act.id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (data.success) {
                    this.mostrarToast(data.mensaje);
                    await this.cargarTabla();
                } else { alert(data.mensaje); }
            } catch (e) { alert('Error al eliminar la actividad.'); }
        },

        // === OBSERVACIONES ===
        abrirObservacion(alumno, act) {
            this.obsData = {
                alumno, act,
                alumnoNombre: alumno.nombre,
                actNombre: act.nombre,
                texto: this.getCalificacion(alumno, act.id).observaciones || ''
            };
            this.modalObs = true;
        },

        guardarObservacionModal() {
            const cal = this.getCalificacion(this.obsData.alumno, this.obsData.act.id);
            cal.observaciones = this.obsData.texto;
            this.autoGuardarDebounced(this.obsData.alumno, this.obsData.act);
            this.modalObs = false;
            this.mostrarToast('Observación guardada');
        },

        // === TOAST ===
        mostrarToast(msg) {
            this.toastMsg = msg;
            this.toast = true;
            setTimeout(() => this.toast = false, 3000);
        },
    };
}
</script>