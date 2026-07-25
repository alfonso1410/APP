<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Transicion de Ciclo: {{ $oldCycle->nombre }} ➔ {{ $newCycle->nombre }}
        </h2>
    </x-slot>

    <div class="py-6" 
         x-data="transitionWizard(
            {{ $oldCycle->ciclo_escolar_id }}, 
            {{ $newCycle->ciclo_escolar_id }}, 
            {{ Js::from($matrixData) }}, 
            {{ Js::from($newGroups) }}
         )">
         
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alerta de error -->
            <div x-show="errorMessage" 
                 x-transition
                 class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" 
                 x-cloak>
                <span class="block sm:inline" x-text="errorMessage"></span>
                <button @click="errorMessage = ''" class="absolute top-2 right-3 text-red-500 hover:text-red-700 font-bold">&times;</button>
            </div>

            <!-- Alerta de exito -->
            <div x-show="successMessage" 
                 x-transition
                 class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" 
                 x-cloak>
                <span class="block sm:inline" x-text="successMessage"></span>
            </div>

            <div class="bg-white shadow-xl sm:rounded-lg flex overflow-hidden min-h-[600px]">
                
                <!-- ══════════════════════════════════════════════════════ -->
                <!-- COLUMNA IZQUIERDA: Lista de Grupos                    -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="w-1/3 bg-gray-50 border-r border-gray-200 overflow-y-auto flex flex-col">
                    <div class="p-4 bg-gray-100 border-b border-gray-200 font-bold text-gray-700">
                        Grupos Actuales ({{ $oldCycle->nombre }})
                    </div>
                    <ul class="flex-1">
                        <template x-for="(group, index) in matrix" :key="group.old_group_id">
                            <li @click="selectedIndex = index; filterView = 'all'" 
                                :class="{
                                    'bg-blue-50 border-l-4 border-blue-500': selectedIndex === index, 
                                    'border-l-4 border-transparent hover:bg-gray-100': selectedIndex !== index
                                }"
                                class="cursor-pointer p-4 border-b border-gray-200 transition">
                                
                                <div class="font-semibold text-gray-800" x-text="group.group_name"></div>
                                
                                <!-- Indicadores de estado -->
                                <div class="text-xs mt-1">
                                    <!-- Grupos de 6to: estado de egreso -->
                                    <template x-if="group.is_graduating">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Egresan (6to Primaria)
                                        </span>
                                    </template>
                                    <!-- Grupos regulares: pendiente o asignado -->
                                    <template x-if="!group.is_graduating && getAssignedCount(group) === 0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">
                                            Pendiente
                                        </span>
                                    </template>
                                    <template x-if="!group.is_graduating && getAssignedCount(group) > 0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Asignado
                                        </span>
                                    </template>
                                </div>

                                <!-- Contadores para grupos regulares -->
                                <template x-if="!group.is_graduating">
                                    <div class="text-xs mt-1 flex gap-3">
                                        <span class="text-green-600 font-medium" 
                                              x-show="getAssignedCount(group) > 0"
                                              x-text="getAssignedCount(group) + ' promovido(s)'"></span>
                                        <span class="text-gray-500 font-medium" 
                                              x-show="getNotPromotedCount(group) > 0"
                                              x-text="getNotPromotedCount(group) + ' sin promover'"></span>
                                    </div>
                                </template>

                                <!-- Contadores para grupos de 6to -->
                                <template x-if="group.is_graduating">
                                    <div class="text-xs mt-1 flex gap-3">
                                        <span class="text-green-600 font-medium" 
                                              x-text="group.graduating_student_ids.length + ' egresa(n)'"></span>
                                        <span class="text-gray-500 font-medium" 
                                              x-show="getNotGraduatingCount(group) > 0"
                                              x-text="getNotGraduatingCount(group) + ' no egresa(n)'"></span>
                                    </div>
                                </template>
                            </li>
                        </template>
                    </ul>

                    <!-- Resumen general -->
                    <div class="p-4 bg-gray-100 border-t border-gray-200 text-xs text-gray-600">
                        <div class="font-bold mb-1">Resumen:</div>
                        <div class="flex justify-between">
                            <span>Asignados: <strong class="text-green-700" x-text="matrix.filter(g => !g.is_graduating && getAssignedCount(g) > 0).length + matrix.filter(g => g.is_graduating).length"></strong></span>
                            <span>Pendientes: <strong class="text-orange-600" x-text="matrix.filter(g => !g.is_graduating && getAssignedCount(g) === 0).length"></strong></span>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- COLUMNA DERECHA: TODOS los grupos renderizados con    -->
                <!-- x-show para que el DOM nunca se destruya.             -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="w-2/3 p-6 flex flex-col bg-white overflow-y-auto">
                    
                    <template x-for="(group, gIdx) in matrix" :key="group.old_group_id">
                        <div x-show="gIdx === selectedIndex" x-cloak>
                            
                            <!-- Encabezado del grupo -->
                            <div class="border-b border-gray-200 pb-4 mb-4">
                                <h3 class="text-2xl font-bold text-gray-800">
                                    Configurar: <span x-text="group.group_name"></span>
                                </h3>
                                <p class="text-gray-600 mt-1">
                                    Total de alumnos activos: <span class="font-semibold" x-text="group.student_count"></span>
                                </p>
                                <!-- Mini-resumen grupos regulares -->
                                <template x-if="!group.is_graduating">
                                    <p class="text-sm mt-1">
                                        <span class="text-green-600 font-medium" x-text="getAssignedCount(group) + ' promovidos'"></span>
                                        <span class="text-gray-400"> / </span>
                                        <span class="text-gray-500 font-medium" x-text="getNotPromotedCount(group) + ' sin promover'"></span>
                                    </p>
                                </template>
                                <!-- Mini-resumen grupos de 6to -->
                                <template x-if="group.is_graduating">
                                    <p class="text-sm mt-1">
                                        <span class="text-green-600 font-medium" x-text="group.graduating_student_ids.length + ' egresan'"></span>
                                        <span class="text-gray-400"> / </span>
                                        <span class="text-gray-500 font-medium" x-text="getNotGraduatingCount(group) + ' no egresan'"></span>
                                    </p>
                                </template>
                            </div>

                            <!-- ─── GRUPO DE 6TO: CHECKBOXES DE EGRESO ─── -->
                            <template x-if="group.is_graduating">
                                <div>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                        <h4 class="text-lg font-medium text-gray-900">Modulo de Egresos (6to de Primaria)</h4>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Selecciona a los alumnos que <strong>SI terminan sus estudios</strong> y egresan. 
                                            Los alumnos que dejes desmarcados (repiten o causan baja) no cambiaran a estado egresado 
                                            y quedaran sin grupo en el nuevo ciclo.
                                        </p>
                                    </div>

                                    <!-- Seleccion masiva para 6to -->
                                    <div class="mb-4 flex gap-2">
                                        <button @click="markAllGraduating(gIdx)" 
                                                type="button"
                                                class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-xs font-semibold uppercase tracking-widest transition">
                                            Marcar todos
                                        </button>
                                        <button @click="unmarkAllGraduating(gIdx)" 
                                                type="button"
                                                class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-100 transition">
                                            Desmarcar todos
                                        </button>
                                    </div>

                                    <!-- Lista de alumnos de 6to con Checkboxes -->
                                    <div class="space-y-1 max-h-[400px] overflow-y-auto border border-gray-200 rounded-lg p-2 bg-white">
                                        <template x-if="group.students.length === 0">
                                            <p class="text-sm text-gray-500 p-4 text-center">No hay alumnos activos en este grupo.</p>
                                        </template>
                                        
                                        <template x-for="(student, sIdx) in group.students" :key="student.alumno_id">
                                            <label class="flex items-center space-x-3 p-2.5 rounded-md cursor-pointer border-b border-gray-100 last:border-0 transition"
                                                   :class="isGraduating(group, student.alumno_id) ? 'bg-green-50 border-l-2 border-l-green-400' : 'bg-gray-50 border-l-2 border-l-gray-300'">
                                                <input type="checkbox"
                                                       :value="student.alumno_id"
                                                       x-model="group.graduating_student_ids"
                                                       class="rounded text-green-600 focus:ring-green-500 h-5 w-5">
                                                <span class="text-sm text-gray-800 font-medium" x-text="student.nombre"></span>
                                                <span class="text-xs ml-auto font-semibold"
                                                      :class="isGraduating(group, student.alumno_id) ? 'text-green-700' : 'text-gray-400'"
                                                      x-text="isGraduating(group, student.alumno_id) ? 'Egresa' : 'No egresa'"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- ─── GRUPO REGULAR: ASIGNACION POR GRADO/GRUPO ─── -->
                            <template x-if="!group.is_graduating">
                                <div>
                                    <!-- 1. Asignacion Masiva -->
                                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            enviar a todos por defecto a un grupo:
                                        </label>
                                        <div class="flex gap-2">
                                            <select x-model="group.default_target_id" 
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <option value="">-- Selecciona grupo predeterminado --</option>
                                                <template x-for="target in group.target_groups" :key="target.grupo_id">
                                                    <option :value="target.grupo_id" x-text="target.nombre_grupo + ' (' + target.grado_nombre + ')'"></option>
                                                </template>
                                            </select>
                                            <button @click="applyDefaultToAll(gIdx)" 
                                                    type="button" 
                                                    class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-xs font-semibold uppercase tracking-widest transition whitespace-nowrap">
                                                Aplicar a todos
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2" x-show="group.default_target_id">
                                            Grupo seleccionado: <strong x-text="getTargetGroupName(group, group.default_target_id)"></strong>
                                        </p>
                                    </div>

                                    <!-- 2. Ajuste Individual -->
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <p class="text-sm font-bold text-gray-700 uppercase">
                                                2. Ajuste Individual por Alumno:
                                            </p>
                                            <div class="flex gap-1">
                                                <button @click="filterView = 'all'" 
                                                        :class="filterView === 'all' ? 'bg-gray-800 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                                                        class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-widest transition">Todos</button>
                                                <button @click="filterView = 'assigned'" 
                                                        :class="filterView === 'assigned' ? 'bg-gray-800 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                                                        class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-widest transition">Promovidos</button>
                                                <button @click="filterView = 'not_promoted'" 
                                                        :class="filterView === 'not_promoted' ? 'bg-gray-800 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'"
                                                        class="px-3 py-1.5 rounded-md text-xs font-semibold uppercase tracking-widest transition">Sin promover</button>
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mb-3">
                                            Cambia el destino individual si un alumno va a otro grupo. Dejalo en "Sin promover" si repite grado o causa baja.
                                        </p>
                                        
                                        <div class="space-y-1 max-h-[400px] overflow-y-auto border border-gray-200 rounded-lg p-2">
                                            <template x-if="group.students.length === 0">
                                                <p class="text-sm text-gray-500 p-4 text-center">No hay alumnos activos en este grupo.</p>
                                            </template>
                                            
                                            <template x-for="(student, sIdx) in group.students" :key="student.alumno_id">
                                                <div x-show="filterView === 'all' 
                                                             || (filterView === 'assigned' && group.students[sIdx].new_group_id !== '') 
                                                             || (filterView === 'not_promoted' && group.students[sIdx].new_group_id === '')"
                                                     class="flex items-center justify-between p-2.5 rounded-md border-b border-gray-100 last:border-0"
                                                     :class="{
                                                         'bg-green-50 border-l-2 border-l-green-400': group.students[sIdx].new_group_id !== '',
                                                         'bg-gray-50 border-l-2 border-l-gray-300': group.students[sIdx].new_group_id === ''
                                                     }">
                                                    
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                                              :class="group.students[sIdx].new_group_id !== '' ? 'bg-green-500' : 'bg-gray-400'"></span>
                                                        <span class="text-sm text-gray-800 font-medium" x-text="student.nombre"></span>
                                                    </div>
                                                    
                                                    <select x-model="group.students[sIdx].new_group_id"
                                                            class="text-xs rounded-md shadow-sm w-52 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 border"
                                                            :class="group.students[sIdx].new_group_id !== '' 
                                                                ? 'border-green-300 bg-green-50' 
                                                                : 'border-gray-300 bg-gray-50'">
                                                        <option value="">-- Sin promover --</option>
                                                        <template x-for="target in group.target_groups" :key="target.grupo_id">
                                                            <option :value="target.grupo_id" x-text="target.nombre_grupo + ' (' + target.grado_nombre + ')'"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Boton de Ejecucion -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    <template x-if="getPendingGroups().length > 0">
                        <span class="text-orange-600 font-medium">
                            Faltan <strong x-text="getPendingGroups().length"></strong> grupo(s) por configurar.
                        </span>
                    </template>
                    <template x-if="getPendingGroups().length === 0">
                        <span class="text-green-600 font-medium">Todos los grupos estan configurados.</span>
                    </template>
                </div>

                <button @click="submitTransition" 
                        :disabled="isSubmitting"
                        :class="{'opacity-50 cursor-not-allowed': isSubmitting}"
                        class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg flex items-center transition uppercase tracking-widest text-sm">
                    
                    <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    
                    <span x-text="isSubmitting ? 'Procesando Transicion...' : 'Ejecutar Transicion de Ciclo'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function transitionWizard(oldCycleId, newCycleId, initialMatrix, newGroups) {
            return {
                oldCycleId: oldCycleId,
                newCycleId: newCycleId,
                newGroups: newGroups,
                selectedIndex: 0,
                isSubmitting: false,
                errorMessage: '',
                successMessage: '',
                filterView: 'all',
                
                // ─── INICIALIZACION DE LA MATRIZ ───
                matrix: initialMatrix.map(group => ({
                    ...group,
                    default_target_id: '',
                    // Para 6to: todos marcados por defecto (la directora desmarca los que no egresan)
                    graduating_student_ids: group.is_graduating 
                        ? group.students.map(s => s.alumno_id) 
                        : [],
                    students: (group.students || []).map(student => ({
                        ...student,
                        new_group_id: ''
                    }))
                })),

                // ─── HELPERS PARA GRUPOS REGULARES ───
                
                getAssignedCount(group) {
                    if (!group.students) return 0;
                    return group.students.filter(s => s.new_group_id !== '').length;
                },

                getNotPromotedCount(group) {
                    if (!group.students) return 0;
                    return group.students.filter(s => s.new_group_id === '').length;
                },

                // ─── HELPERS PARA GRUPOS DE 6TO ───

                isGraduating(group, alumnoId) {
                    return group.graduating_student_ids.includes(alumnoId) 
                        || group.graduating_student_ids.includes(String(alumnoId));
                },

                getNotGraduatingCount(group) {
                    if (!group.students) return 0;
                    return group.students.filter(s => !this.isGraduating(group, s.alumno_id)).length;
                },

                markAllGraduating(gIdx) {
                    this.matrix[gIdx].graduating_student_ids = this.matrix[gIdx].students.map(s => s.alumno_id);
                },

                unmarkAllGraduating(gIdx) {
                    this.matrix[gIdx].graduating_student_ids = [];
                },

                // ─── HELPERS GENERALES ───

                getPendingGroups() {
                    return this.matrix.filter(g => !g.is_graduating && this.getAssignedCount(g) === 0);
                },

                getTargetGroupName(group, groupId) {
                    if (!groupId || !group.target_groups) return '';
                    const target = group.target_groups.find(t => String(t.grupo_id) === String(groupId));
                    return target ? target.nombre_grupo + ' (' + target.grado_nombre + ')' : '';
                },

                // ─── ASIGNACION MASIVA (grupos regulares) ───
                
                applyDefaultToAll(gIdx) {
                    const defaultId = this.matrix[gIdx].default_target_id;
                    if (!defaultId) {
                        this.errorMessage = 'Selecciona un grupo en la asignacion masiva primero.';
                        setTimeout(() => { this.errorMessage = ''; }, 4000);
                        return;
                    }
                    
                    this.matrix[gIdx].students.forEach((student, idx) => {
                        this.matrix[gIdx].students[idx].new_group_id = defaultId;
                    });

                    this.successMessage = 'Se asignaron ' + this.matrix[gIdx].students.length + ' alumnos al grupo seleccionado.';
                    setTimeout(() => { this.successMessage = ''; }, 3000);
                },

                // ─── ENVIO AL BACKEND ───
                
                async submitTransition() {
                    this.errorMessage = '';
                    this.successMessage = '';
                    
                    // Validacion: grupos regulares deben tener al menos 1 alumno promovido
                    const pending = this.getPendingGroups();
                    if (pending.length > 0) {
                        this.errorMessage = 'Falta configurar: ' + pending.map(g => g.group_name).join(', ') + '. Al menos un alumno debe tener grupo destino asignado.';
                        this.selectedIndex = this.matrix.findIndex(g => g.old_group_id === pending[0].old_group_id);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }

                    // Armar payload
                    const payloadMappings = this.matrix.map(g => ({
                        old_group_id: g.old_group_id,
                        // Para 6to: enviamos los IDs de los que SI egresan
                        graduating_student_ids: g.is_graduating 
                            ? g.graduating_student_ids.map(id => parseInt(id)) 
                            : [],
                        // Para regulares: enviamos el mapeo alumno → grupo destino
                        students: g.is_graduating ? [] : g.students.map(s => ({
                            alumno_id: parseInt(s.alumno_id),
                            new_group_id: s.new_group_id ? parseInt(s.new_group_id) : null
                        }))
                    }));

                    this.isSubmitting = true;
                    try {
                        const response = await fetch('{{ route("admin.ciclo-escolar.transicion.ejecutar") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                old_cycle_id: this.oldCycleId,
                                new_cycle_id: this.newCycleId,
                                mappings: payloadMappings
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Error desconocido al procesar la transicion.');
                        }

                        alert('Transicion ejecutada con exito.');
                        window.location.href = '{{ route("admin.ciclo-escolar.index") }}';

                    } catch (error) {
                        this.errorMessage = error.message;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>