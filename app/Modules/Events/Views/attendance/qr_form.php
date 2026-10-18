<?php
/**
 * Vista: Formulario de Registro por QR (Público)
 */
?>
<div class="min-h-screen bg-[#0B0F19] flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-gray-900 border border-gray-800 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        
        <!-- Decoration -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-purple-500"></div>
        
        <div class="text-center mb-8">
            <?php if (!empty($church['logo'])): ?>
                <img src="<?= url('uploads/' . e($church['logo'])) ?>" alt="<?= e($church['name']) ?>" class="h-16 mx-auto mb-4 rounded-xl object-contain">
            <?php else: ?>
                <div class="w-16 h-16 bg-brand-500/20 text-brand-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            <?php endif; ?>
            <h1 class="text-2xl font-bold text-white mb-2"><?= e($church['name']) ?></h1>
            <p class="text-gray-400 text-sm">Registro de Asistencia</p>
        </div>

        <?php
        // Registro recordado en el teléfono: una clave por iglesia, válida solo para el día de hoy
        $storageKey = 'ekkles_asistencia_' . $uuid;
        $today      = date('Y-m-d');
        $registered = \App\Core\Session::getFlash('qr_registered');
        ?>
        <!-- Confirmación (se muestra tras registrar, o al reabrir el QR si ya se registró hoy) -->
        <div id="registered-panel" class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-6 text-center text-emerald-400 mb-6 <?= isset($_GET['success']) ? '' : 'hidden' ?>">
            <svg class="w-16 h-16 mx-auto mb-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h2 class="text-xl font-bold mb-2">¡Asistencia Registrada!</h2>
            <?php if (isset($_GET['duplicado'])): ?>
            <p class="text-sm text-amber-300 mb-2">Esta asistencia ya estaba registrada hoy, no se volvió a contar.</p>
            <?php endif; ?>
            <p id="registered-detail" class="text-sm text-gray-300 mb-2"></p>
            <p class="text-sm">Gracias por tu registro. Que tengas un servicio bendecido.</p>
            <a href="/qr/asistencia/<?= e($uuid) ?>?nueva=1" class="mt-6 inline-block text-brand-400 text-sm font-semibold hover:text-brand-300">Registrar otra familia</a>
        </div>

        <script>
            (function () {
                const key = <?= json_encode($storageKey) ?>;
                const today = <?= json_encode($today) ?>;
                const fromServer = <?= json_encode($registered) ?>;
                const params = new URLSearchParams(location.search);
                const store = {
                    get() { try { return JSON.parse(localStorage.getItem(key)); } catch (e) { return null; } },
                    set(v) { try { localStorage.setItem(key, JSON.stringify(v)); } catch (e) {} },
                    clear() { try { localStorage.removeItem(key); } catch (e) {} },
                };

                if (params.has('nueva')) store.clear();
                if (fromServer) store.set(Object.assign({ date: today }, fromServer));

                const saved = store.get();
                const isToday = saved && saved.date === today;
                if (saved && !isToday) store.clear();

                window.ekklesShowRegistered = function () {
                    if (!isToday && !params.has('success')) return false;
                    if (isToday) {
                        const parts = [saved.adults + ' adulto(s)', saved.children + ' niño(s)', saved.guests + ' invitado(s)'];
                        document.getElementById('registered-detail').textContent =
                            (saved.name ? saved.name + ': ' : '') + parts.join(' · ');
                    }
                    document.getElementById('registered-panel').classList.remove('hidden');
                    return true;
                };
            })();
        </script>

        <?php if (isset($_GET['success'])): ?>
            <script>window.ekklesShowRegistered();</script>
        <?php else: ?>
        <div id="qr-form-wrapper">
            
            <?php $error = \App\Core\Session::getFlash('error'); if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6 text-center">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form action="/qr/asistencia/registrar" method="POST" id="qrForm" class="space-y-6">
                <input type="hidden" name="uuid" value="<?= e($uuid) ?>">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token ?? '') ?>">
                <input type="hidden" name="member_id" id="member_id" value="">

                <!-- Buscador de Asistente -->
                <div class="relative z-50">
                    <label for="search_name" class="block text-sm font-medium text-gray-300 mb-1">Nombre del Asistente</label>
                    <input type="text" id="search_name" name="registered_name" autocomplete="off" placeholder="Busca tu nombre..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors">
                    
                    <!-- Dropdown de resultados -->
                    <div id="search_results" class="absolute w-full mt-1 bg-gray-800 border border-gray-700 rounded-xl shadow-xl overflow-hidden hidden z-50 max-h-60 overflow-y-auto">
                        <!-- Resultados inyectados por JS -->
                    </div>
                    
                    <div class="mt-2 text-right">
                        <a href="/login" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">¿No estás en la lista? Registrar Miembro</a>
                    </div>
                </div>

                <!-- Adultos -->
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-medium">Adultos</h3>
                        <p class="text-xs text-gray-400">Mayores de 12 años</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="updateCount('adults', -1)" class="w-10 h-10 rounded-full bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="number" name="adults" id="adults" value="1" min="0" max="20" class="w-12 text-center bg-transparent text-xl font-bold text-white border-none focus:ring-0 p-0" readonly>
                        <button type="button" onclick="updateCount('adults', 1)" class="w-10 h-10 rounded-full bg-brand-600 hover:bg-brand-500 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Niños -->
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-medium">Niños</h3>
                        <p class="text-xs text-gray-400">Escuela Dominical</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="updateCount('children', -1)" class="w-10 h-10 rounded-full bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="number" name="children" id="children" value="0" min="0" max="20" class="w-12 text-center bg-transparent text-xl font-bold text-white border-none focus:ring-0 p-0" readonly>
                        <button type="button" onclick="updateCount('children', 1)" class="w-10 h-10 rounded-full bg-brand-600 hover:bg-brand-500 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Invitados -->
                <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-white font-medium">Invitados</h3>
                        <p class="text-xs text-gray-400">Vienen por 1ra vez</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="updateCount('guests', -1)" class="w-10 h-10 rounded-full bg-gray-700 hover:bg-gray-600 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="number" name="guests" id="guests" value="0" min="0" max="20" class="w-12 text-center bg-transparent text-xl font-bold text-white border-none focus:ring-0 p-0" readonly>
                        <button type="button" onclick="updateCount('guests', 1)" class="w-10 h-10 rounded-full bg-brand-600 hover:bg-brand-500 text-white flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-500 text-white font-bold text-lg py-4 px-6 rounded-2xl transition-all transform hover:scale-[1.02] shadow-lg shadow-brand-500/25 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Registrar Asistencia
                    </button>
                </div>
            </form>

            <script>
                function updateCount(id, change) {
                    const input = document.getElementById(id);
                    let val = parseInt(input.value) || 0;
                    val += change;
                    if (val < 0) val = 0;
                    if (val > 20) val = 20;
                    input.value = val;
                }

                // Autocomplete Logic
                const searchInput = document.getElementById('search_name');
                const searchResults = document.getElementById('search_results');
                const memberIdInput = document.getElementById('member_id');
                let timeoutId = null;

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const q = this.value.trim();
                        memberIdInput.value = ''; // Reset ID if user types

                        clearTimeout(timeoutId);
                        
                        if (q.length < 2) {
                            searchResults.classList.add('hidden');
                            return;
                        }

                        timeoutId = setTimeout(() => {
                            fetch(`/qr/asistencia/<?= e($uuid) ?>/buscar?q=${encodeURIComponent(q)}`)
                                .then(res => res.json())
                                .then(data => {
                                    searchResults.innerHTML = '';
                                    if (data.length > 0) {
                                        data.forEach(item => {
                                            const div = document.createElement('div');
                                            div.className = 'px-4 py-3 hover:bg-gray-700 cursor-pointer text-gray-200 border-b border-gray-700/50 last:border-0 transition-colors';
                                            div.textContent = item.name;
                                            div.addEventListener('click', () => {
                                                searchInput.value = item.name;
                                                memberIdInput.value = item.id;
                                                searchResults.classList.add('hidden');
                                            });
                                            searchResults.appendChild(div);
                                        });
                                        searchResults.classList.remove('hidden');
                                    } else {
                                        searchResults.classList.add('hidden');
                                    }
                                })
                                .catch(() => searchResults.classList.add('hidden'));
                        }, 300);
                    });

                    // Hide dropdown when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                            searchResults.classList.add('hidden');
                        }
                    });
                }

                // Si este teléfono ya registró asistencia hoy, mostrar la confirmación en lugar del formulario
                if (window.ekklesShowRegistered()) {
                    document.getElementById('qr-form-wrapper').classList.add('hidden');
                }
            </script>
        </div>
        <?php endif; ?>
    </div>
</div>
