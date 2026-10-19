function clientPortalApp() {
            return {
                isAuthenticated: false,
                token: localStorage.getItem('nesistema_client_token') || '',
                cliente: {},
                activeTab: 'inicio', // inicio, servicios, tramites, notificaciones, perfil
                isLoading: false,
                loginError: '',
                showPassword: false,
                rememberMe: true,
                loginForm: {
                    identificacion: localStorage.getItem('nesistema_saved_user') || '',
                    password: '',
                },
                pwdForm: {
                    actual: '',
                    nueva: '',
                },
                showPasswordModal: false,
                isRefreshing: false,
                tramites: [],
                servicios: [],
                selectedServicio: null,
                servicioCategoryFilter: 'todos',
                searchServicioQuery: '',
                notificaciones: [],
                unreadNotifCount: 0,
                stats: {
                    total: 0,
                    en_proceso: 0,
                    listos: 0,
                    en_revision: 0,
                },
                chatOpen: false,
                chatMessages: [],
                chatInputText: '',
                chatUnreadCount: 0,
                chatLoading: false,
                chatSending: false,
                statusFilter: 'todos',
                categoryFilter: 'todas',
                searchQuery: '',
                selectedTramite: null,
                timeline: [],
                pdfViewer: {
                    visible: false,
                    loading: false,
                    tramite: null,
                    title: '',
                    totalPages: 0,
                    zoom: 1.0,
                    arrayBuffer: null,
                    error: '',
                },

                init() {
                    this.fetchServicios();
                    if (this.token) {
                        this.isAuthenticated = true;
                        this.refreshAll();
                        this.fetchChatUnread();
                        // Polling cada 5 segundos para chequear si el admin completó un trámite o envió chat
                        setInterval(() => {
                            if (this.isAuthenticated && !document.hidden) {
                                this.fetchTramites(false);
                                this.fetchNotificaciones(false);
                                if (this.chatOpen) {
                                    this.fetchChatMessages(false);
                                } else {
                                    this.fetchChatUnread();
                                }
                            }
                        }, 5000);
                    }
                },

                async refreshAll() {
                    if (!this.token) return;
                    this.isRefreshing = true;
                    try {
                        await Promise.all([
                            this.fetchProfile(),
                            this.fetchTramites(false),
                            this.fetchServicios(),
                            this.fetchNotificaciones(false),
                            this.fetchChatUnread()
                        ]);
                    } catch (e) {
                        console.error('Error al actualizar datos:', e);
                    } finally {
                        setTimeout(() => { this.isRefreshing = false; }, 500);
                    }
                },

                async handleLogin() {
                    this.isLoading = true;
                    this.loginError = '';
                    try {
                        const res = await fetch('/api/v1/client/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.loginForm)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.token = data.token;
                            this.cliente = data.cliente;
                            this.isAuthenticated = true;
                            localStorage.setItem('nesistema_client_token', data.token);
                            if (this.rememberMe) {
                                localStorage.setItem('nesistema_saved_user', this.loginForm.identificacion);
                            }
                            await this.fetchTramites();
                            await this.fetchNotificaciones();
                        } else {
                            this.loginError = data.message || 'Credenciales incorrectas.';
                        }
                    } catch (e) {
                        this.loginError = 'Error de conexión con el servidor. Revisa tu red.';
                    } finally {
                        this.isLoading = false;
                    }
                },

                async fetchProfile() {
                    try {
                        const res = await fetch('/api/v1/client/profile', {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            this.cliente = data.cliente;
                        }
                    } catch (e) {}
                },

                async fetchTramites(showLoader = true) {
                    try {
                        const res = await fetch('/api/v1/client/tramites', {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            this.tramites = data.tramites;
                            this.stats = data.stats;
                        }
                    } catch (e) {
                        console.error('Error al obtener trámites:', e);
                    }
                },

                async fetchNotificaciones(showLoader = true) {
                    try {
                        const res = await fetch('/api/v1/client/notificaciones', {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        if (res.status === 401) return this.handleLogout();
                        const data = await res.json();
                        if (data.success) {
                            this.notificaciones = data.notificaciones;
                            this.unreadNotifCount = data.unread_count;
                        }
                    } catch (e) {}
                },

                async fetchServicios() {
                    try {
                        const res = await fetch('/api/v1/client/servicios', {
                            headers: {
                                'Authorization': this.token ? ('Bearer ' + this.token) : '',
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.servicios = data.servicios || [];
                        }
                    } catch (e) {
                        console.error('Error al cargar servicios:', e);
                    }
                },

                get filteredServicios() {
                    return this.servicios.filter(s => {
                        const matchesCategory = this.servicioCategoryFilter === 'todos' || s.categoria === this.servicioCategoryFilter;
                        const q = this.searchServicioQuery.toLowerCase().trim();
                        const matchesSearch = !q || 
                            s.nombre.toLowerCase().includes(q) || 
                            (s.descripcion && s.descripcion.toLowerCase().includes(q)) ||
                            (s.requisitos && s.requisitos.some(r => r.toLowerCase().includes(q)));

                        return matchesCategory && matchesSearch;
                    });
                },

                openServicioDetail(s) {
                    this.selectedServicio = s;
                },

                async solicitarServicioChat(s) {
                    if (!s) return;
                    const nombreTramite = s.nombre || 'este trámite';
                    const msg = `Hola, deseo solicitar asesoría e iniciar el trámite notarial: "${nombreTramite}". ¿Cuáles son los pasos y requisitos?`;
                    
                    // 1. Cerrar modal de servicio
                    this.selectedServicio = null;
                    
                    // 2. Abrir chat modal
                    this.chatOpen = true;
                    this.chatUnreadCount = 0;
                    this.chatInputText = msg;
                    
                    // 3. Enviar mensaje automáticamente al chat o enfocar input
                    if (this.isAuthenticated && this.token) {
                        await this.fetchChatMessages(true);
                        await this.sendChatMessage();
                    } else {
                        await this.fetchChatMessages(true);
                        this.$nextTick(() => {
                            const input = document.getElementById('clientChatInputField');
                            if (input) input.focus();
                        });
                    }
                },

                solicitarServicioWhatsApp(s) {
                    if (!s) return;
                    const phone = '17187173257';
                    const msg = encodeURIComponent(`Hola, deseo consultar y solicitar información sobre el trámite notarial: *${s.nombre}*. ¿Cuáles son los pasos a seguir?`);
                    window.open(`https://wa.me/${phone}?text=${msg}`, '_blank');
                },

                get filteredTramites() {
                    return this.tramites.filter(t => {
                        const matchesStatus = this.statusFilter === 'todos' || t.estado === this.statusFilter;
                        const matchesCategory = this.categoryFilter === 'todas' || t.categoria === this.categoryFilter;
                        const q = this.searchQuery.toLowerCase().trim();
                        const matchesSearch = !q || 
                            t.nombre.toLowerCase().includes(q) || 
                            t.descripcion.toLowerCase().includes(q) ||
                            String(t.id).includes(q);

                        return matchesStatus && matchesCategory && matchesSearch;
                    });
                },

                get listosTramites() {
                    return this.tramites.filter(t => t.estado === 'listo' || Boolean(t.documento_pdf || t.documento_pdf_url));
                },

                async openPdfPreview(t) {
                    if (!t) return;
                    this.pdfViewer.tramite = t;
                    this.pdfViewer.title = t.nombre || 'Documento Oficial';
                    this.pdfViewer.visible = true;
                    this.pdfViewer.loading = true;
                    this.pdfViewer.error = '';
                    this.pdfViewer.zoom = 1.0;
                    this.pdfViewer.totalPages = 0;

                    const container = document.getElementById('pdfCanvasContainer');
                    if (container) container.innerHTML = '';

                    const downloadUrl = `/api/v1/client/tramites/${t.categoria}/${t.id}/download?token=${encodeURIComponent(this.token)}&preview=1`;

                    try {
                        const res = await fetch(downloadUrl, {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/pdf',
                            }
                        });

                        if (!res.ok) {
                            throw new Error(`Error del servidor (${res.status}). El documento no pudo cargarse.`);
                        }

                        const buffer = await res.arrayBuffer();
                        this.pdfViewer.arrayBuffer = buffer;

                        if (window.pdfjsLib) {
                            await this.renderPdfPages(buffer);
                        } else {
                            const blob = new Blob([buffer], { type: 'application/pdf' });
                            const blobUrl = window.URL.createObjectURL(blob);
                            if (container) {
                                container.innerHTML = `<iframe src="${blobUrl}#toolbar=0" class="w-full h-[500px] rounded-xl bg-white"></iframe>`;
                            }
                        }
                    } catch (err) {
                        console.error('Error al visualizar PDF:', err);
                        this.pdfViewer.error = err.message || 'Error al procesar el archivo PDF.';
                    } finally {
                        this.pdfViewer.loading = false;
                    }
                },

                async renderPdfPages(arrayBuffer) {
                    if (!window.pdfjsLib || !arrayBuffer) return;
                    try {
                        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
                        this.pdfViewer.totalPages = pdf.numPages;
                        const container = document.getElementById('pdfCanvasContainer');
                        if (!container) return;
                        container.innerHTML = '';

                        const scrollArea = document.getElementById('pdfScrollArea');
                        const containerWidth = Math.max((scrollArea ? scrollArea.clientWidth : container.clientWidth) || 340, 320);
                        const baseWidth = containerWidth - 28;

                        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                            const page = await pdf.getPage(pageNum);
                            const initialViewport = page.getViewport({ scale: 1.0 });
                            
                            // Factor de resolución 2.0 para nitidez perfecta al hacer zoom
                            const baseScale = baseWidth / initialViewport.width;
                            const renderScale = baseScale * 2.0;
                            const viewport = page.getViewport({ scale: renderScale });

                            const canvas = document.createElement('canvas');
                            canvas.className = 'rounded-xl shadow-2xl bg-white border border-slate-700/50 mb-4 transition-[width] duration-150 ease-out origin-top';
                            canvas.dataset.baseWidth = String(baseWidth);

                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            // Ancho visual en pantalla según el nivel de zoom actual
                            const displayWidth = Math.round(baseWidth * (this.pdfViewer.zoom || 1.0));
                            canvas.style.width = displayWidth + 'px';
                            canvas.style.maxWidth = 'none';
                            canvas.style.height = 'auto';

                            const context = canvas.getContext('2d');
                            container.appendChild(canvas);

                            await page.render({
                                canvasContext: context,
                                viewport: viewport
                            }).promise;
                        }

                        this.attachPinchToZoom();
                    } catch (e) {
                        console.error('Error renderizando páginas PDF:', e);
                        throw e;
                    }
                },

                zoomPdf(delta) {
                    let currentZoom = this.pdfViewer.zoom || 1.0;
                    let newZoom = currentZoom + delta;
                    newZoom = Math.min(Math.max(0.6, Math.round(newZoom * 100) / 100), 3.0);
                    this.setZoom(newZoom);
                },

                resetZoom() {
                    this.setZoom(1.0);
                },

                setZoom(newZoom) {
                    this.pdfViewer.zoom = newZoom;
                    const container = document.getElementById('pdfCanvasContainer');
                    if (!container) return;
                    
                    const canvases = container.querySelectorAll('canvas');
                    canvases.forEach(canvas => {
                        const baseWidth = parseFloat(canvas.dataset.baseWidth) || 320;
                        const targetWidth = Math.round(baseWidth * newZoom);
                        canvas.style.width = targetWidth + 'px';
                        canvas.style.maxWidth = 'none';
                        canvas.style.height = 'auto';
                    });
                },

                attachPinchToZoom() {
                    const scrollArea = document.getElementById('pdfScrollArea');
                    if (!scrollArea || scrollArea._pinchAttached) return;
                    scrollArea._pinchAttached = true;

                    let initialDist = 0;
                    let startZoom = 1.0;

                    scrollArea.addEventListener('touchstart', (e) => {
                        if (e.touches.length === 2) {
                            initialDist = Math.hypot(
                                e.touches[0].pageX - e.touches[1].pageX,
                                e.touches[0].pageY - e.touches[1].pageY
                            );
                            startZoom = this.pdfViewer.zoom || 1.0;
                        }
                    }, { passive: true });

                    scrollArea.addEventListener('touchmove', (e) => {
                        if (e.touches.length === 2 && initialDist > 0) {
                            const currentDist = Math.hypot(
                                e.touches[0].pageX - e.touches[1].pageX,
                                e.touches[0].pageY - e.touches[1].pageY
                            );
                            const factor = currentDist / initialDist;
                            let newZoom = startZoom * factor;
                            newZoom = Math.min(Math.max(0.6, Math.round(newZoom * 100) / 100), 3.0);
                            this.setZoom(newZoom);
                        }
                    }, { passive: true });

                    scrollArea.addEventListener('touchend', (e) => {
                        if (e.touches.length < 2) {
                            initialDist = 0;
                        }
                    }, { passive: true });
                },

                closePdfViewer() {
                    this.pdfViewer.visible = false;
                    this.pdfViewer.tramite = null;
                    this.pdfViewer.arrayBuffer = null;
                    this.pdfViewer.error = '';
                    this.pdfViewer.zoom = 1.0;
                    const container = document.getElementById('pdfCanvasContainer');
                    if (container) container.innerHTML = '';
                },

                async downloadDocument(t, preview = false) {
                    if (!t) return;
                    
                    if (preview) {
                        return this.openPdfPreview(t);
                    }

                    const downloadUrl = `/api/v1/client/tramites/${t.categoria}/${t.id}/download?token=${encodeURIComponent(this.token)}`;
                    const fileName = (t.nombre ? t.nombre.replace(/[^a-zA-Z0-9_-]/g, '_') : 'Documento_Tramite') + '.pdf';

                    try {
                        this.isLoading = true;
                        let blob = null;

                        // Si el PDF ya está abierto en memoria en el visor
                        if (this.pdfViewer.visible && this.pdfViewer.arrayBuffer && this.pdfViewer.tramite && this.pdfViewer.tramite.id === t.id) {
                            blob = new Blob([this.pdfViewer.arrayBuffer], { type: 'application/pdf' });
                        } else {
                            const res = await fetch(downloadUrl, {
                                headers: {
                                    'Authorization': 'Bearer ' + this.token,
                                    'Accept': 'application/pdf',
                                }
                            });

                            if (res.ok) {
                                blob = await res.blob();
                            }
                        }

                        if (blob) {
                            // 1. Intentar compartir/guardar nativamente en Android e iOS (Web Share API con archivos)
                            try {
                                const file = new File([blob], fileName, { type: 'application/pdf' });
                                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                                    await navigator.share({
                                        files: [file],
                                        title: fileName,
                                        text: `Documento oficial del trámite: ${t.nombre || ''}`
                                    });
                                    return;
                                }
                            } catch (shareErr) {
                                if (shareErr.name === 'AbortError') return;
                                console.warn('Share API cancelado:', shareErr);
                            }

                            // 2. Descarga por Blob clásica
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = blobUrl;
                            a.download = fileName;
                            a.target = '_blank';
                            document.body.appendChild(a);
                            a.click();

                            setTimeout(() => {
                                document.body.removeChild(a);
                                window.URL.revokeObjectURL(blobUrl);
                            }, 3000);
                        } else {
                            window.open(downloadUrl, '_blank');
                        }
                    } catch (err) {
                        console.warn('Fallback al método window.open:', err);
                        window.open(downloadUrl, '_blank');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async openTramiteDetail(t) {
                    this.selectedTramite = t;
                    try {
                        const res = await fetch(`/api/v1/client/tramites/${t.categoria}/${t.id}`, {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.timeline = data.timeline;
                        }
                    } catch (e) {}
                },

                async handleNotifClick(n) {
                    // Marcar como leída
                    if (!n.leido) {
                        n.leido = true;
                        this.unreadNotifCount = Math.max(0, this.unreadNotifCount - 1);
                        fetch(`/api/v1/client/notificaciones/${n.id}/read`, {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + this.token, 'Accept': 'application/json' }
                        });
                    }

                    // Si la notificación pertenece a un trámite, abrirlo
                    if (n.tramite_tipo && n.tramite_id) {
                        const found = this.tramites.find(t => t.categoria === n.tramite_tipo && String(t.id) === String(n.tramite_id));
                        if (found) {
                            this.openTramiteDetail(found);
                        }
                    }
                },

                async markAllAsRead() {
                    this.notificaciones.forEach(n => n.leido = true);
                    this.unreadNotifCount = 0;
                    fetch('/api/v1/client/notificaciones/all/read', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + this.token, 'Accept': 'application/json' }
                    });
                },

                async handleChangePassword() {
                    if (!this.pwdForm.actual || !this.pwdForm.nueva) {
                        return alert('Por favor llena todos los campos.');
                    }
                    try {
                        const res = await fetch('/api/v1/client/update-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                password_actual: this.pwdForm.actual,
                                password_nuevo: this.pwdForm.nueva,
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            alert('¡Contraseña actualizada con éxito!');
                            this.showPasswordModal = false;
                            this.pwdForm.actual = '';
                            this.pwdForm.nueva = '';
                        } else {
                            alert(data.message || 'Error al cambiar contraseña.');
                        }
                    } catch (e) {
                        alert('Error de conexión.');
                    }
                },

                handleLogout() {
                    if (this.token) {
                        fetch('/api/v1/client/logout', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + this.token, 'Accept': 'application/json' }
                        }).catch(() => {});
                    }
                    this.token = '';
                    this.cliente = {};
                    this.tramites = [];
                    this.notificaciones = [];
                    this.unreadNotifCount = 0;
                    this.stats = { total: 0, en_proceso: 0, listos: 0, en_revision: 0 };
                    this.isAuthenticated = false;
                    this.loginForm.password = '';
                    localStorage.removeItem('nesistema_client_token');
                },

                async openChat() {
                    this.chatOpen = true;
                    this.chatUnreadCount = 0;
                    await this.fetchChatMessages(true);
                    setTimeout(() => {
                        const input = document.getElementById('clientChatInputField');
                        if (input) input.focus();
                    }, 200);
                },

                closeChat() {
                    this.chatOpen = false;
                },

                async fetchChatMessages(forceScroll = false) {
                    if (!this.token) return;
                    if (this.chatMessages.length === 0) {
                        this.chatLoading = true;
                    }
                    try {
                        const res = await fetch('/api/v1/client/chat', {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const prevLength = this.chatMessages.length;
                            this.chatMessages = data.mensajes || [];
                            this.chatUnreadCount = 0;
                            if (forceScroll || this.chatMessages.length > prevLength) {
                                this.scrollChatToBottom();
                            }
                        }
                    } catch (e) {
                        console.error('Error al cargar chat:', e);
                    } finally {
                        this.chatLoading = false;
                    }
                },

                async sendChatMessage() {
                    if (!this.token || !this.chatInputText.trim() || this.chatSending) return;
                    const text = this.chatInputText.trim();
                    this.chatInputText = '';
                    this.chatSending = true;

                    // Optimistic update
                    const tempId = 'temp_' + Date.now();
                    const now = new Date();
                    const tempMsg = {
                        id: tempId,
                        remitente_tipo: 'cliente',
                        nombre_remitente: this.cliente.nombre || 'Yo',
                        mensaje: text,
                        hora: now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }),
                        leido: false
                    };
                    this.chatMessages.push(tempMsg);
                    this.scrollChatToBottom();

                    try {
                        const res = await fetch('/api/v1/client/chat/enviar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ mensaje: text })
                        });
                        const data = await res.json();
                        if (data.success) {
                            const idx = this.chatMessages.findIndex(m => m.id === tempId);
                            if (idx !== -1) {
                                this.chatMessages[idx] = data.mensaje;
                            }
                        }
                    } catch (e) {
                        console.error('Error al enviar mensaje:', e);
                    } finally {
                        this.chatSending = false;
                    }
                },

                async fetchChatUnread() {
                    if (!this.token) return;
                    try {
                        const res = await fetch('/api/v1/client/chat/unread', {
                            headers: {
                                'Authorization': 'Bearer ' + this.token,
                                'Accept': 'application/json',
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            const prevUnread = this.chatUnreadCount;
                            this.chatUnreadCount = data.unread_count || 0;
                            if (this.chatUnreadCount > prevUnread && !this.chatOpen) {
                                if (navigator.vibrate) try { navigator.vibrate(200); } catch(e) {}
                            }
                        }
                    } catch (e) {}
                },

                insertClientQuickMessage(text) {
                    this.chatInputText = text;
                    const input = document.getElementById('clientChatInputField');
                    if (input) input.focus();
                },

                scrollChatToBottom() {
                    setTimeout(() => {
                        const stream = document.getElementById('clientChatMessagesStream');
                        if (stream) {
                            stream.scrollTop = stream.scrollHeight;
                        }
                    }, 50);
                },

                formatNumber(val) {
                    return Number(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                }
            }
        }