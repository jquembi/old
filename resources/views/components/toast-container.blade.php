{{-- ════════════════════════════════════════════════════════════════
     Toast Container + Validation Errors + Confirm Dialog
     Escuta: `toast`, `validation-errors`, `confirmar`
     API: window.toastr.success|error|warning|info(mensagem, titulo)
═══════════════════════════════════════════════════════════════════ --}}
@php
    $mapaEstado = [
        'verification-link-sent' => 'Enviámos um novo link de verificação para o seu email.',
        'profile-updated' => 'Perfil actualizado.',
        'password-updated' => 'Palavra-passe actualizada.',
    ];
    $toastsIniciais = [];
    foreach (['success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $chave => $tipo) {
        $mensagem = session($chave);
        if (is_string($mensagem) && trim($mensagem) !== '') {
            $toastsIniciais[] = ['type' => $tipo, 'message' => $mensagem];
        }
    }
    $estado = session('status');
    if (is_string($estado) && trim($estado) !== '') {
        if (isset($mapaEstado[$estado])) {
            $toastsIniciais[] = ['type' => 'success', 'message' => $mapaEstado[$estado]];
        } elseif (! preg_match('/^[a-z0-9-]+$/', $estado)) {
            $toastsIniciais[] = ['type' => 'success', 'message' => $estado];
        }
    }
@endphp
<script>
    (function () {
        if (window.toastr && window.toastr._pronto) return
        function raiz() {
            return document.querySelector('[data-toast-root]')
        }
        function enviar(type, message, title) {
            const item = { type: type, message: String(message ?? ''), title: title ? String(title) : '' }
            const el = raiz()
            const dados = el && window.Alpine ? window.Alpine.$data(el) : null
            if (dados && typeof dados.addToast === 'function') dados.addToast(item)
            else (window.toastr._q = window.toastr._q || []).push(item)
        }
        window.toastr = {
            _q: (window.toastr && window.toastr._q) || [],
            _pronto: true,
            success: function (message, title) { enviar('success', message, title) },
            error: function (message, title) { enviar('error', message, title) },
            warning: function (message, title) { enviar('warning', message, title) },
            info: function (message, title) { enviar('info', message, title) },
        }
        window.alert = function (message) {
            window.toastr.warning(String(message ?? ''), 'Aviso')
        }
    })()
</script>

{{-- ─── TOASTS ────────────────────────────────────────────── --}}
<div
    x-data="{
        toasts: [],
        maxToasts: 4,
        counter: 0,

        init() {
            const self = this
            ;(window.toastr?._q || []).splice(0).forEach((item) => self.addToast(item))

            const ligarValidacao = () => {
                if (!window.Livewire || self._validacaoLigada) return
                self._validacaoLigada = true
                Livewire.hook('commit', ({ component, succeed }) => {
                    succeed(({ snapshot }) => {
                        const errors = snapshot?.memo?.errors || {}
                        const messages = Object.values(errors).flat().filter(Boolean)
                        self.showValidation(component.id, messages)
                    })
                })
            }
            ligarValidacao()
            document.addEventListener('livewire:init', ligarValidacao)

            const iniciais = @js($toastsIniciais ?? []);
            iniciais.forEach((item) => this.addToast(item))
            @if (isset($errors) && $errors->any())
                this.showValidation('pagina', @js($errors->all()));
            @endif
        },

        showValidation(key, messages) {
            const unicos = [...new Set(messages.map((m) => String(m).trim()).filter(Boolean))]
            const idChave = 'validacao:' + key
            if (unicos.length === 0) {
                const existente = this.toasts.find((t) => t.key === idChave)
                if (existente) this.removeToast(existente.id)
                return
            }
            const extra = unicos.length > 3 ? `\n… e mais ${unicos.length - 3}` : ''
            this.addToast({
                key: idChave,
                type: 'error',
                title: unicos.length === 1 ? 'Não foi possível concluir' : unicos.length + ' pontos por corrigir',
                message: unicos.slice(0, 3).join('\n') + extra,
                silencioso: true,
            })
        },

        humanizar(texto) {
            const mapa = {
                'auth.failed': 'Email ou palavra-passe incorrectos.',
                'auth.password': 'A palavra-passe indicada está incorrecta.',
                'auth.throttle': 'Demasiadas tentativas. Aguarde um momento e tente de novo.',
            }
            return String(texto || '').split('\n').map((linha) => mapa[linha.trim()] || linha).join('\n')
        },

        addToast(detail) {
            const type = detail.type || 'info'
            const message = this.humanizar(String(detail.message || detail[1] || '').trim())
            const title = detail.title ? String(detail.title) : ''
            if (!message && !title) return

            const key = detail.key || (type + '|' + title + '|' + message)
            const existente = this.toasts.find((t) => t.key === key)
            if (existente) {
                const mudou = existente.message !== message || (title && existente.title !== title)
                if (detail.silencioso) {
                    if (mudou) {
                        existente.title = title || existente.title
                        existente.message = message
                        existente.duration = this.durationFor(existente)
                        this.armTimer(existente)
                    }
                    existente.visible = true
                    return
                }
                existente.count += 1
                existente.title = title || existente.title
                existente.message = message || existente.message
                existente.duration = this.durationFor(existente)
                existente.visible = true
                this.armTimer(existente)
                return
            }

            const toast = {
                id: ++this.counter,
                key,
                type,
                title,
                message,
                count: 1,
                visible: false,
                progress: 100,
                paused: false,
                elapsed: 0,
                duration: 0,
                timer: null,
            }
            toast.duration = this.durationFor(toast)
            this.toasts.push(toast)

            if (this.toasts.length > this.maxToasts) {
                this.toasts.splice(0, this.toasts.length - this.maxToasts).forEach((antigo) => {
                    if (antigo.timer) clearInterval(antigo.timer)
                })
            }

            this.$nextTick(() => {
                const t = this.toasts.find((item) => item.id === toast.id)
                if (t) t.visible = true
            })
            this.armTimer(toast)
        },

        durationFor(toast) {
            const tamanho = (toast.title + toast.message).length
            const base = toast.type === 'error' ? 5200 : 3600
            return Math.min(11000, base + tamanho * 28)
        },

        armTimer(toast) {
            if (toast.timer) clearInterval(toast.timer)
            toast.elapsed = 0
            toast.progress = 100
            toast.paused = false
            const passo = 50
            toast.timer = setInterval(() => {
                const actual = this.toasts.find((item) => item.id === toast.id)
                if (!actual) { clearInterval(toast.timer); return }
                if (actual.paused) return
                actual.elapsed += passo
                actual.progress = Math.max(0, 100 - (actual.elapsed / actual.duration) * 100)
                if (actual.elapsed >= actual.duration) {
                    clearInterval(actual.timer)
                    this.removeToast(actual.id)
                }
            }, passo)
        },

        pause(id) {
            const toast = this.toasts.find((item) => item.id === id)
            if (toast) toast.paused = true
        },

        resume(id) {
            const toast = this.toasts.find((item) => item.id === id)
            if (toast) toast.paused = false
        },

        removeToast(id) {
            const toast = this.toasts.find((item) => item.id === id)
            if (!toast) return
            if (toast.timer) clearInterval(toast.timer)
            toast.visible = false
            setTimeout(() => {
                this.toasts = this.toasts.filter((item) => item.id !== id)
            }, 280)
        },

        iconMarkup(type) {
            const icons = {
                success: `<svg class='h-5 w-5 text-emerald-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>`,
                error: `<svg class='h-5 w-5 text-red-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>`,
                warning: `<svg class='h-5 w-5 text-amber-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'/></svg>`,
                info: `<svg class='h-5 w-5 text-blue-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>`,
            }
            return icons[type] || icons.info
        },
    }"
    @toast.window="addToast($event.detail)"
    @validation-errors.window="showValidation('evento', $event.detail.errors || $event.detail[0] || [])"
    @action-message.window="addToast({ type: $event.detail.type || 'success', message: $event.detail.message || '' })"
    @profile-updated.window="addToast({ type: 'success', message: 'Alterações guardadas.', title: 'Perfil' })"
    @password-updated.window="addToast({ type: 'success', message: 'Palavra-passe actualizada.', title: 'Segurança' })"
    data-toast-root
    class="fixed top-4 right-4 z-[9999] flex flex-col gap-2.5 pointer-events-none"
    style="max-width: min(24rem, calc(100vw - 2rem));"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            :data-toast="toast.id"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            @mouseenter="pause(toast.id)"
            @mouseleave="resume(toast.id)"
            class="pointer-events-auto relative overflow-hidden rounded-xl shadow-2xl ring-1 backdrop-blur-xl"
            :class="{
                'bg-zinc-900/90 ring-emerald-500/20': toast.type === 'success',
                'bg-zinc-900/90 ring-red-500/20': toast.type === 'error',
                'bg-zinc-900/90 ring-amber-500/20': toast.type === 'warning',
                'bg-zinc-900/90 ring-blue-500/20': toast.type === 'info',
            }"
        >
            {{-- Glow accent no topo --}}
            <div
                class="absolute inset-x-0 top-0 h-px"
                :class="{
                    'bg-gradient-to-r from-transparent via-emerald-400 to-transparent': toast.type === 'success',
                    'bg-gradient-to-r from-transparent via-red-400 to-transparent': toast.type === 'error',
                    'bg-gradient-to-r from-transparent via-amber-400 to-transparent': toast.type === 'warning',
                    'bg-gradient-to-r from-transparent via-blue-400 to-transparent': toast.type === 'info',
                }"
            ></div>

            {{-- Conteúdo --}}
            <div class="flex items-start gap-3 px-4 py-3.5">
                <div class="shrink-0 mt-0.5" x-html="iconMarkup(toast.type)"></div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white" x-show="toast.title" x-text="toast.title"></p>
                    <p class="text-sm leading-snug text-zinc-100 whitespace-pre-line" :class="toast.title ? 'mt-0.5 text-zinc-300' : 'font-medium'" x-text="toast.message"></p>
                </div>
                <span x-show="toast.count > 1" class="shrink-0 rounded-full bg-white/10 px-1.5 py-0.5 text-[11px] font-bold text-white" x-text="toast.count"></span>
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    aria-label="Fechar aviso"
                    class="shrink-0 -mt-0.5 -mr-1 p-1 rounded-lg text-zinc-500 hover:text-zinc-300 hover:bg-zinc-800/50 transition-all duration-150"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Barra de progresso --}}
            <div class="absolute inset-x-0 bottom-0 h-[2px] bg-zinc-800/50">
                <div
                    class="h-full transition-all duration-100 ease-linear rounded-full"
                    :class="{
                        'bg-emerald-400/60': toast.type === 'success',
                        'bg-red-400/60': toast.type === 'error',
                        'bg-amber-400/60': toast.type === 'warning',
                        'bg-blue-400/60': toast.type === 'info',
                    }"
                    :style="`width: ${toast.progress}%`"
                ></div>
            </div>
        </div>
    </template>
</div>

{{-- ─── CONFIRM DIALOG ───────────────────────────────────── --}}
<div
    x-data="{
        show: false,
        title: '',
        message: '',
        confirmLabel: 'Confirmar',
        cancelLabel: 'Cancelar',
        variant: 'danger',
        pendingAction: null,
        pendingComponent: null,
        pendingParams: [],

        openConfirm(detail) {
            this.title = detail.title || detail[0] || 'Confirmação'
            this.message = detail.message || detail[1] || 'Tem certeza que deseja continuar?'
            this.confirmLabel = detail.confirmLabel || detail[2] || 'Confirmar'
            this.cancelLabel = detail.cancelLabel || 'Cancelar'
            this.variant = detail.variant || detail[3] || 'danger'
            this.pendingAction = detail.action || null
            const component = detail.component
            this.pendingComponent = (typeof component === 'string' && component && component !== 'undefined' && component !== 'null')
                ? component
                : null
            this.pendingParams = detail.params || []
            this.show = true
        },

        confirm() {
            this.show = false
            if (this.pendingAction && this.pendingComponent) {
                try {
                    const comp = Livewire.find(this.pendingComponent)
                    if (comp) {
                        comp.call(this.pendingAction, ...this.pendingParams)
                    }
                } catch (_) {
                    // Ignora IDs inválidos para evitar quebrar a UI; segue via evento global.
                    window.dispatchEvent(new CustomEvent('confirm-accepted', { detail: { action: this.pendingAction, params: this.pendingParams } }))
                }
            } else if (this.pendingAction) {
                window.dispatchEvent(new CustomEvent('confirm-accepted', { detail: { action: this.pendingAction, params: this.pendingParams } }))
            }
            this.reset()
        },

        cancel() {
            this.show = false
            this.reset()
        },

        reset() {
            setTimeout(() => {
                this.pendingAction = null
                this.pendingComponent = null
                this.pendingParams = []
            }, 200)
        },

        variantClasses() {
            return {
                danger: {
                    icon: 'text-red-400',
                    iconBg: 'bg-red-500/10',
                    btn: 'bg-red-600 hover:bg-red-500 focus:ring-red-500/40',
                    glow: 'via-red-500',
                },
                warning: {
                    icon: 'text-amber-400',
                    iconBg: 'bg-amber-500/10',
                    btn: 'bg-amber-600 hover:bg-amber-500 focus:ring-amber-500/40',
                    glow: 'via-amber-500',
                },
                info: {
                    icon: 'text-blue-400',
                    iconBg: 'bg-blue-500/10',
                    btn: 'bg-blue-600 hover:bg-blue-500 focus:ring-blue-500/40',
                    glow: 'via-blue-500',
                },
            }[this.variant] || {
                icon: 'text-red-400',
                iconBg: 'bg-red-500/10',
                btn: 'bg-red-600 hover:bg-red-500 focus:ring-red-500/40',
                glow: 'via-red-500',
            }
        }
    }"
    @confirmar.window="openConfirm($event.detail)"
    @keydown.escape.window="if(show) cancel()"
    x-cloak
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm"
        @click="cancel()"
    ></div>

    {{-- Dialog --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-90 translate-y-4"
        class="fixed inset-0 z-[10001] flex items-center justify-center p-4"
    >
        <div class="relative w-full max-w-sm overflow-hidden rounded-2xl bg-zinc-900/95 shadow-2xl ring-1 ring-white/10 backdrop-blur-xl" @click.stop>
            {{-- Glow accent --}}
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent to-transparent" :class="variantClasses().glow"></div>

            <div class="px-6 pt-6 pb-5">
                {{-- Ícone --}}
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl" :class="variantClasses().iconBg">
                    <template x-if="variant === 'danger'">
                        <svg class="h-6 w-6" :class="variantClasses().icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </template>
                    <template x-if="variant === 'warning'">
                        <svg class="h-6 w-6" :class="variantClasses().icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    </template>
                    <template x-if="variant === 'info'">
                        <svg class="h-6 w-6" :class="variantClasses().icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                        </svg>
                    </template>
                </div>

                {{-- Texto --}}
                <div class="mt-4 text-center">
                    <h3 class="text-base font-semibold text-zinc-100" x-text="title"></h3>
                    <p class="mt-2 text-sm text-zinc-400 leading-relaxed" x-text="message"></p>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex gap-3 px-6 pb-6">
                <button
                    @click="cancel()"
                    class="flex-1 rounded-xl px-4 py-2.5 text-sm font-medium text-zinc-300 bg-zinc-800 hover:bg-zinc-700 ring-1 ring-zinc-700 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-zinc-500"
                    x-text="cancelLabel"
                ></button>
                <button
                    @click="confirm()"
                    class="flex-1 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition-all duration-150 focus:outline-none focus:ring-2"
                    :class="variantClasses().btn"
                    x-text="confirmLabel"
                ></button>
            </div>
        </div>
    </div>
</div>
