<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div style="width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);">
                    <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <div>
                    <h2 class="font-black text-xl text-slate-800 leading-tight">
                        {{ __('Chat en Vivo con Clientes') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">Atención en tiempo real y respuestas directas a los clientes de la App</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span id="liveStatusIndicator" style="padding: 6px 14px; border-radius: 9999px; font-size: 11px; font-weight: 800; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 9999px; background-color: #10b981; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></span>
                    <span>Conexión en Vivo Activa</span>
                </span>
            </div>
        </div>
    </x-slot>

    <style>
        /* Estilos autocontenidos e independientes de Tailwind para garantizar funcionamiento 100% en Hostinger */
        .chat-main-wrapper {
            display: flex;
            flex-direction: row;
            background-color: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            height: 78vh;
            min-height: 580px;
            position: relative;
        }

        .chat-sidebar-col {
            width: 360px;
            min-width: 320px;
            background-color: #090d16;
            border-right: 1px solid #1e293b;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            z-index: 10;
        }

        .chat-thread-col {
            flex: 1;
            background-color: #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .chat-conv-card {
            padding: 14px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-bottom: 1px solid rgba(30, 41, 59, 0.6);
            background-color: transparent;
            user-select: none;
        }

        .chat-conv-card:hover {
            background-color: rgba(30, 41, 59, 0.7);
        }

        .chat-conv-card.active {
            background-color: #1e293b !important;
            border-left: 4px solid #6366f1 !important;
        }

        .chat-avatar-badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            font-weight: 900;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .chat-scrollable {
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        .chat-scrollable::-webkit-scrollbar {
            width: 5px;
        }

        .chat-scrollable::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }

        .chat-quick-btn {
            padding: 6px 12px;
            background-color: #1e293b;
            color: #cbd5e1;
            border: 1px solid #334155;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .chat-quick-btn:hover {
            background-color: #334155;
            color: #ffffff;
            border-color: #6366f1;
        }

        @media (max-width: 768px) {
            .chat-main-wrapper {
                flex-direction: column;
                height: 82vh;
                min-height: 520px;
            }
            .chat-sidebar-col {
                width: 100% !important;
                min-width: 100% !important;
            }
            .chat-thread-col {
                width: 100% !important;
            }
        }
    </style>

    <div class="py-3">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Contenedor Principal de Chat -->
            <div class="chat-main-wrapper" id="chatAppContainer">
                
                <!-- ============================================== -->
                <!-- COLUMNA BANDEJA DE CONVERSACIONES (IZQUIERDA)  -->
                <!-- ============================================== -->
                <div id="chatSidebarPanel" class="chat-sidebar-col">
                    
                    <!-- Header Buscador & Filtros -->
                    <div style="padding: 16px; border-bottom: 1px solid #1e293b; background-color: #090d16;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                            <h3 style="margin: 0; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8;">Bandeja de Mensajes</h3>
                            <span id="unreadTotalBadge" style="display: none; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 900; background-color: #4f46e5; color: #ffffff;"></span>
                        </div>

                        <!-- Input Búsqueda -->
                        <div style="position: relative;">
                            <input type="text" id="chatSearchInput" placeholder="Buscar cliente o cédula..." 
                                   style="width: 100%; padding: 8px 12px 8px 34px; background-color: #1e293b; border: 1px solid #334155; border-radius: 12px; font-size: 12px; font-weight: 600; color: #ffffff; outline: none; box-sizing: border-box;">
                            <div style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #64748b;">
                                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Filtros Rápidos -->
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px;">
                            <button type="button" onclick="setFilter('todas')" id="btnFilterTodas" 
                                    style="padding: 4px 10px; background-color: #4f46e5; color: #ffffff; font-size: 11px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer;">
                                Todas
                            </button>
                            <button type="button" onclick="setFilter('no_leidas')" id="btnFilterNoLeidas" 
                                    style="padding: 4px 10px; background-color: #1e293b; color: #94a3b8; font-size: 11px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer;">
                                No leídas
                            </button>
                        </div>
                    </div>

                    <!-- Lista con Scroll de Conversaciones -->
                    <div class="chat-scrollable" id="conversacionesList" style="flex: 1; background-color: #090d16;">
                        <div style="padding: 32px 16px; text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">
                            Cargando conversaciones...
                        </div>
                    </div>

                    <!-- Footer Info Izquierda -->
                    <div style="padding: 12px 16px; background-color: #060910; border-top: 1px solid #1e293b; font-size: 11px; color: #64748b; display: flex; align-items: center; justify-content: space-between;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #10b981;"></span>
                            <span>Auto-sincronizado</span>
                        </span>
                        <button type="button" onclick="loadConversaciones(true)" style="background: none; border: none; color: #818cf8; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                            <span>Refrescar</span>
                            <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                    </div>

                </div>


                <!-- ============================================== -->
                <!-- COLUMNA HILO DE CHAT Y RESPUESTAS (DERECHA)    -->
                <!-- ============================================== -->
                <div id="chatThreadPanel" class="chat-thread-col">
                    
                    <!-- Estado Inicial Sin Conversación Seleccionada -->
                    <div id="noChatSelectedState" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px; text-align: center; margin: auto;">
                        <div style="width: 72px; height: 72px; border-radius: 20px; background-color: #1e293b; border: 1px solid #334155; display: flex; align-items: center; justify-content: center; color: #818cf8; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); margin-bottom: 16px;">
                            <svg style="width: 36px; height: 36px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff;">Selecciona una conversación</h3>
                            <p style="margin: 6px 0 0 0; font-size: 12px; color: #94a3b8; max-width: 360px;">Elige un cliente de la lista de la izquierda para responder en vivo o dar seguimiento a sus trámites.</p>
                        </div>
                    </div>

                    <!-- Contenedor del Chat Activo -->
                    <div id="activeChatContent" style="display: none; flex-direction: column; height: 100%; justify-content: space-between; overflow: hidden;">
                        
                        <!-- Header del Chat Activo -->
                        <div style="padding: 14px 20px; background-color: #090d16; border-bottom: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                            <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                <!-- Botón Volver para Móviles -->
                                <button type="button" onclick="closeChatMobile()" id="btnBackMobile" style="padding: 6px 10px; color: #94a3b8; background-color: #1e293b; border: 1px solid #334155; border-radius: 10px; cursor: pointer; display: none;" title="Volver a la lista">
                                    <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                                </button>

                                <div id="activeClientAvatar" class="chat-avatar-badge">
                                    C
                                </div>
                                <div style="min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 id="activeClientName" style="margin: 0; font-size: 14px; font-weight: 900; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Cliente</h3>
                                        <span id="activeClientOficina" style="display: none; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; background-color: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3);"></span>
                                    </div>
                                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                                        <span id="activeClientCi">C.I: --</span>
                                        <span>•</span>
                                        <span id="activeClientPhone">Tel: --</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Acciones Rápidas del Cliente -->
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a id="activeClientWhatsAppBtn" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background-color: #059669; color: #ffffff; font-size: 11px; font-weight: 800; border-radius: 10px; text-decoration: none;">
                                    <span>WhatsApp</span>
                                </a>
                                <a id="activeClientTramitesBtn" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background-color: #1e293b; color: #cbd5e1; border: 1px solid #334155; font-size: 11px; font-weight: 800; border-radius: 10px; text-decoration: none;">
                                    <span>Ver Perfil</span>
                                </a>
                            </div>
                        </div>

                        <!-- Hilo de Mensajes con Scroll -->
                        <div id="messagesStream" class="chat-scrollable" style="flex: 1; padding: 20px; background-color: rgba(15, 23, 42, 0.8);">
                            <!-- Los mensajes se renderizan dinámicamente -->
                        </div>

                        <!-- Respuestas Rápidas Predefinidas -->
                        <div style="padding: 10px 16px; background-color: #090d16; border-top: 1px solid #1e293b; display: flex; align-items: center; gap: 8px; overflow-x: auto; flex-shrink: 0;">
                            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; white-space: nowrap; margin-right: 4px;">Respuestas Rápidas:</span>
                            <button type="button" class="chat-quick-btn" onclick="insertQuickReply('¡Hola! Con mucho gusto le atendemos. ¿En qué podemos ayudarle el día de hoy?')">
                                👋 Saludo inicial
                            </button>
                            <button type="button" class="chat-quick-btn" onclick="insertQuickReply('Su trámite se encuentra en proceso de revisión notarial. Le notificaremos tan pronto esté listo.')">
                                ⏳ En revisión
                            </button>
                            <button type="button" class="chat-quick-btn" style="color: #6ee7b7; border-color: rgba(16, 185, 129, 0.4);" onclick="insertQuickReply('🎉 ¡Excelentes noticias! Su trámite está listo para entrega. Ya puede descargar el documento oficial directamente desde su app.')">
                                🟢 Trámite listo
                            </button>
                            <button type="button" class="chat-quick-btn" onclick="insertQuickReply('Por favor, envíenos una fotografía o copia legible de su documento de identidad para continuar con el proceso.')">
                                📄 Solicitar cédula
                            </button>
                            <button type="button" class="chat-quick-btn" style="color: #a5b4fc; border-color: rgba(99, 102, 241, 0.4);" onclick="insertQuickReply('Estimado cliente, puede acercarse a nuestras oficinas para retirar su documento original firmado.')">
                                🏢 Retiro en oficina
                            </button>
                        </div>

                        <!-- Caja de Entrada de Texto y Botón de Envío -->
                        <div style="padding: 14px 18px; background-color: #090d16; border-top: 1px solid #1e293b; flex-shrink: 0;">
                            <form id="chatSendForm" onsubmit="handleSendMessage(event)" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                                <div style="position: relative; flex: 1;">
                                    <input type="text" id="chatMessageInput" 
                                           placeholder="Escribe una respuesta para el cliente... (Presiona Enter para enviar)" 
                                           autocomplete="off"
                                           onkeydown="if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); handleSendMessage(event); }"
                                           style="width: 100%; padding: 12px 16px; background-color: #1e293b; border: 1px solid #334155; border-radius: 14px; font-size: 13px; font-weight: 600; color: #ffffff; outline: none; box-sizing: border-box;">
                                </div>
                                <button type="submit" id="btnSendMessage" 
                                        style="padding: 12px 20px; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; border: none; border-radius: 14px; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4); flex-shrink: 0;">
                                    <span>Enviar</span>
                                    <svg style="width: 16px; height: 16px; transform: rotate(90deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </button>
                            </form>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Script de Chat en Vivo para el Panel Administrativo -->
    <script>
        let currentConversationId = {{ $initialConversationId ?? 'null' }};
        let currentFilter = 'todas';
        let searchQuery = '';
        let lastMessageCount = 0;
        let isPolling = false;

        document.addEventListener('DOMContentLoaded', () => {
            console.log('Chat view loaded. Initial Conversation ID:', currentConversationId);
            
            // Chequear si es móvil para ajustar botones
            checkResponsiveLayout();
            window.addEventListener('resize', checkResponsiveLayout);

            loadConversaciones(true);

            // Buscador con debounce
            const searchInput = document.getElementById('chatSearchInput');
            let searchTimeout = null;
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    searchQuery = e.target.value;
                    searchTimeout = setTimeout(() => {
                        loadConversaciones(false);
                    }, 300);
                });
            }

            // Polling en tiempo real cada 3 segundos
            setInterval(() => {
                if (!document.hidden) {
                    loadConversaciones(false);
                    if (currentConversationId) {
                        loadMessages(currentConversationId, false);
                    }
                }
            }, 3000);
        });

        function checkResponsiveLayout() {
            const btnBack = document.getElementById('btnBackMobile');
            if (btnBack) {
                if (window.innerWidth < 768) {
                    btnBack.style.display = 'inline-flex';
                } else {
                    btnBack.style.display = 'none';
                    // Restaurar visibilidad de ambos paneles en desktop
                    const sidebar = document.getElementById('chatSidebarPanel');
                    const thread = document.getElementById('chatThreadPanel');
                    if (sidebar) sidebar.style.display = 'flex';
                    if (thread) thread.style.display = 'flex';
                }
            }
        }

        function setFilter(filter) {
            currentFilter = filter;
            const btnTodas = document.getElementById('btnFilterTodas');
            const btnNoLeidas = document.getElementById('btnFilterNoLeidas');

            if (filter === 'todas') {
                btnTodas.style.backgroundColor = '#4f46e5';
                btnTodas.style.color = '#ffffff';
                btnNoLeidas.style.backgroundColor = '#1e293b';
                btnNoLeidas.style.color = '#94a3b8';
            } else {
                btnTodas.style.backgroundColor = '#1e293b';
                btnTodas.style.color = '#94a3b8';
                btnNoLeidas.style.backgroundColor = '#4f46e5';
                btnNoLeidas.style.color = '#ffffff';
            }
            loadConversaciones(false);
        }

        async function loadConversaciones(showLoading = false) {
            const listEl = document.getElementById('conversacionesList');
            if (showLoading && listEl && (!listEl.children || listEl.children.length === 0 || listEl.innerText.includes('Cargando'))) {
                listEl.innerHTML = '<div style="padding: 32px 16px; text-align: center; color: #64748b; font-size: 12px; font-weight: 600;">Cargando conversaciones...</div>';
            }

            try {
                const url = `{{ url('chat/conversaciones') }}?filtro=${encodeURIComponent(currentFilter)}&q=${encodeURIComponent(searchQuery)}`;
                const res = await fetch(url, { 
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    } 
                });
                const data = await res.json();

                if (data.success) {
                    renderConversacionesList(data.conversaciones);
                    updateTotalUnreadBadge(data.total_unread);

                    // Si NO hay conversación seleccionada y hay conversaciones, seleccionar automáticamente la primera
                    if (!currentConversationId && data.conversaciones.length > 0) {
                        const targetConv = data.conversaciones.find(c => c.unread_admin > 0) || data.conversaciones[0];
                        selectConversation(targetConv.id);
                    } else if (currentConversationId) {
                        const activeChat = document.getElementById('activeChatContent');
                        if (activeChat && activeChat.style.display !== 'flex') {
                            selectConversation(currentConversationId);
                        }
                    }
                }
            } catch (e) {
                console.error('Error cargando conversaciones:', e);
            }
        }

        function updateTotalUnreadBadge(count) {
            const badge = document.getElementById('unreadTotalBadge');
            if (badge) {
                if (count > 0) {
                    badge.innerText = `${count} pendientes`;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        function renderConversacionesList(conversaciones) {
            const listEl = document.getElementById('conversacionesList');
            if (!listEl) return;

            if (conversaciones.length === 0) {
                listEl.innerHTML = `
                    <div style="padding: 40px 16px; text-align: center; color: #64748b;">
                        <div style="width: 44px; height: 44px; border-radius: 14px; background-color: #1e293b; color: #818cf8; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px auto; font-size: 20px;">💬</div>
                        <p style="margin: 0; font-size: 13px; font-weight: 800; color: #cbd5e1;">No hay conversaciones</p>
                        <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">Los clientes que envíen mensajes desde la app aparecerán aquí.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            conversaciones.forEach(c => {
                const isSelected = currentConversationId === c.id;
                const hasUnread = c.unread_admin > 0;

                html += `
                    <div onclick="selectConversation(${c.id})" 
                         class="chat-conv-card ${isSelected ? 'active' : ''}"
                         data-id="${c.id}">
                        
                        <div class="chat-avatar-badge" style="${isSelected ? 'background: #4f46e5; box-shadow: 0 0 0 2px #818cf8;' : ''}">
                            ${escapeHtml(c.cliente_inicial)}
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-bottom: 2px;">
                                <h4 style="margin: 0; font-size: 12px; font-weight: 900; color: ${hasUnread ? '#a5b4fc' : '#ffffff'}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${escapeHtml(c.cliente_nombre)}
                                </h4>
                                <span style="font-size: 10px; font-weight: 600; color: #64748b; flex-shrink: 0;">
                                    ${c.ultimo_mensaje_hora || c.ultimo_mensaje_at}
                                </span>
                            </div>

                            <p style="margin: 0; font-size: 11px; color: ${hasUnread ? '#f1f5f9' : '#94a3b8'}; font-weight: ${hasUnread ? '700' : '500'}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${escapeHtml(c.ultimo_mensaje)}
                            </p>

                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-top: 4px;">
                                <span style="font-size: 10px; color: #64748b; font-weight: 600;">
                                    CI: ${escapeHtml(c.cliente_identificacion || 'N/A')}
                                </span>

                                ${hasUnread ? `
                                    <span style="padding: 2px 6px; border-radius: 9999px; font-size: 9px; font-weight: 900; background-color: #10b981; color: #022c22;">
                                        ${c.unread_admin} nuevo(s)
                                    </span>
                                ` : ''}
                            </div>
                        </div>

                    </div>
                `;
            });

            listEl.innerHTML = html;
        }

        async function selectConversation(id) {
            console.log('selectConversation called with ID:', id);
            currentConversationId = parseInt(id, 10);

            // Responsive: en móvil ocultar sidebar y mostrar hilo
            if (window.innerWidth < 768) {
                const sidebar = document.getElementById('chatSidebarPanel');
                const thread = document.getElementById('chatThreadPanel');
                if (sidebar) sidebar.style.display = 'none';
                if (thread) thread.style.display = 'flex';
            }

            // Ocultar placeholder y mostrar chat activo
            const noChat = document.getElementById('noChatSelectedState');
            if (noChat) noChat.style.display = 'none';

            const activeChat = document.getElementById('activeChatContent');
            if (activeChat) activeChat.style.display = 'flex';

            // Actualizar clase activa en la lista lateral
            document.querySelectorAll('.chat-conv-card').forEach(el => {
                if (el.getAttribute('data-id') == id) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });

            // Cargar y renderizar mensajes
            await loadMessages(id, true);

            // Focus en la caja de respuesta
            setTimeout(() => {
                const input = document.getElementById('chatMessageInput');
                if (input) input.focus();
            }, 100);
        }

        // Exportar a global para que cualquier onclick funcione sin fallos
        window.selectConversation = selectConversation;

        function closeChatMobile() {
            if (window.innerWidth < 768) {
                const sidebar = document.getElementById('chatSidebarPanel');
                const thread = document.getElementById('chatThreadPanel');
                if (sidebar) sidebar.style.display = 'flex';
                if (thread) thread.style.display = 'none';
            }
        }
        window.closeChatMobile = closeChatMobile;

        async function loadMessages(id, scrollToBottom = false) {
            try {
                const url = `{{ url('chat/mensajes') }}/${id}`;
                const res = await fetch(url, { 
                    headers: { 
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    } 
                });
                const data = await res.json();

                if (data.success) {
                    // Actualizar Header del Cliente
                    const c = data.conversacion;
                    const nameEl = document.getElementById('activeClientName');
                    const avatarEl = document.getElementById('activeClientAvatar');
                    const ciEl = document.getElementById('activeClientCi');
                    const phoneEl = document.getElementById('activeClientPhone');
                    const ofEl = document.getElementById('activeClientOficina');
                    const waBtn = document.getElementById('activeClientWhatsAppBtn');
                    const trBtn = document.getElementById('activeClientTramitesBtn');

                    if (nameEl) nameEl.innerText = c.cliente_nombre || 'Cliente';
                    if (avatarEl) avatarEl.innerText = c.cliente_inicial || 'C';
                    if (ciEl) ciEl.innerText = `C.I: ${c.cliente_identificacion || 'N/A'}`;
                    if (phoneEl) phoneEl.innerText = `Tel: ${c.cliente_telefono || 'N/A'}`;

                    if (ofEl) {
                        if (c.cliente_oficina) {
                            ofEl.innerText = `Sede ${c.cliente_oficina}`;
                            ofEl.style.display = 'inline-block';
                        } else {
                            ofEl.style.display = 'none';
                        }
                    }

                    if (waBtn) {
                        if (c.cliente_telefono) {
                            const cleanPhone = String(c.cliente_telefono).replace(/[^0-9]/g, '');
                            waBtn.href = `https://wa.me/${cleanPhone}?text=${encodeURIComponent('Hola ' + c.cliente_nombre + ', le saludamos de la Notaría NESISTEMA.')}`;
                            waBtn.style.display = 'inline-flex';
                        } else {
                            waBtn.style.display = 'none';
                        }
                    }

                    if (trBtn) {
                        trBtn.href = `{{ url('clientes') }}/${c.cliente_id}`;
                    }

                    renderMessages(data.mensajes, scrollToBottom);
                }
            } catch (e) {
                console.error('Error cargando mensajes:', e);
            }
        }

        function renderMessages(mensajes, forceScroll = false) {
            const stream = document.getElementById('messagesStream');
            if (!stream) return;

            const shouldScroll = forceScroll || (stream.scrollHeight - stream.scrollTop <= stream.clientHeight + 120) || (mensajes.length !== lastMessageCount);
            lastMessageCount = mensajes.length;

            if (mensajes.length === 0) {
                stream.innerHTML = `
                    <div style="text-align: center; padding: 40px 16px; color: #64748b;">
                        <span style="font-size: 28px;">🤝</span>
                        <p style="margin: 8px 0 0 0; font-size: 13px; font-weight: 800; color: #cbd5e1;">Inicio de la conversación</p>
                        <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">Envía una respuesta o selecciona un botón rápido para atender al cliente.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            let lastDate = '';

            mensajes.forEach(m => {
                if (m.fecha && m.fecha !== lastDate) {
                    lastDate = m.fecha;
                    html += `
                        <div style="display: flex; justify-content: center; margin: 16px 0;">
                            <span style="padding: 2px 10px; border-radius: 9999px; font-size: 10px; font-weight: 700; background-color: #1e293b; color: #94a3b8; border: 1px solid #334155;">
                                ${m.fecha}
                            </span>
                        </div>
                    `;
                }

                const isAdmin = m.remitente_tipo === 'admin';

                if (isAdmin) {
                    // Mensaje Notaría (Derecha)
                    html += `
                        <div style="display: flex; flex-direction: column; align-items: flex-end; margin-bottom: 12px;">
                            <div style="max-width: 80%; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; padding: 12px 16px; border-radius: 18px; border-top-right-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);">
                                <p style="margin: 0; font-size: 13px; font-weight: 500; line-height: 1.5; word-break: break-word; white-space: pre-wrap;">${escapeHtml(m.mensaje)}</p>
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 10px; color: rgba(224, 231, 255, 0.9); margin-top: 4px; font-weight: 700;">
                                    <span>${escapeHtml(m.nombre_remitente || 'Notaría')}</span>
                                    <span>•</span>
                                    <span>${m.hora}</span>
                                    <span title="${m.leido ? 'Leído por cliente' : 'Enviado'}">${m.leido ? '✓✓' : '✓'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Mensaje Cliente (Izquierda)
                    html += `
                        <div style="display: flex; flex-direction: column; align-items: flex-start; margin-bottom: 12px;">
                            <div style="max-width: 80%; background-color: #1e293b; border: 1px solid #334155; color: #f8fafc; padding: 12px 16px; border-radius: 18px; border-top-left-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);">
                                <div style="display: flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 900; color: #818cf8; margin-bottom: 2px;">
                                    <span>👤 ${escapeHtml(m.nombre_remitente || 'Cliente')}</span>
                                </div>
                                <p style="margin: 0; font-size: 13px; font-weight: 500; color: #e2e8f0; line-height: 1.5; word-break: break-word; white-space: pre-wrap;">${escapeHtml(m.mensaje)}</p>
                                <div style="display: flex; align-items: center; justify-content: flex-end; font-size: 10px; color: #64748b; margin-top: 4px; font-weight: 700;">
                                    <span>${m.hora}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });

            stream.innerHTML = html;

            if (shouldScroll) {
                stream.scrollTop = stream.scrollHeight;
            }
        }

        async function handleSendMessage(e) {
            if (e) e.preventDefault();
            if (!currentConversationId) {
                alert('Por favor selecciona una conversación primero.');
                return;
            }

            const input = document.getElementById('chatMessageInput');
            if (!input) return;
            const msgText = input.value.trim();
            if (!msgText) return;

            // Actualización optimista inmediata en la UI
            const stream = document.getElementById('messagesStream');
            const tempId = 'temp_' + Date.now();
            const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const optimisticHtml = `
                <div id="${tempId}" style="display: flex; flex-direction: column; align-items: flex-end; margin-bottom: 12px;">
                    <div style="max-width: 80%; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; padding: 12px 16px; border-radius: 18px; border-top-right-radius: 4px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);">
                        <p style="margin: 0; font-size: 13px; font-weight: 500; line-height: 1.5; word-break: break-word; white-space: pre-wrap;">${escapeHtml(msgText)}</p>
                        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px; font-size: 10px; color: rgba(224, 231, 255, 0.9); margin-top: 4px; font-weight: 700;">
                            <span>Notaría</span>
                            <span>•</span>
                            <span>${nowTime}</span>
                            <span>✓</span>
                        </div>
                    </div>
                </div>
            `;
            if (stream) {
                stream.insertAdjacentHTML('beforeend', optimisticHtml);
                stream.scrollTop = stream.scrollHeight;
            }

            input.value = '';
            const btn = document.getElementById('btnSendMessage');
            if (btn) btn.disabled = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const url = `{{ url('chat/enviar') }}/${currentConversationId}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ mensaje: msgText })
                });

                const data = await res.json();
                if (data.success) {
                    await loadMessages(currentConversationId, true);
                    loadConversaciones(false);
                } else {
                    alert(data.message || 'Error al enviar mensaje');
                }
            } catch (err) {
                console.error('Error enviando mensaje:', err);
                alert('Error de conexión al enviar mensaje.');
            } finally {
                if (btn) btn.disabled = false;
                input.focus();
            }
        }
        window.handleSendMessage = handleSendMessage;

        function insertQuickReply(text) {
            const input = document.getElementById('chatMessageInput');
            if (input) {
                input.value = text;
                input.focus();
            }
        }
        window.insertQuickReply = insertQuickReply;

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
</x-app-layout>
