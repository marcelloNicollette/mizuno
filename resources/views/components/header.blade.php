@props(['user' => null, 'type' => ''])

<style>
    .space-x-2> :not([hidden])~ :not([hidden]) {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
</style>

@php
    $segmentacoes = \App\Models\Segmentacao::where('active', 1)->get();
    $segmentacoesClienteDisponiveis =
        $user && $user->segmentacoesCliente->count() > 0
            ? $user->segmentacoesCliente->where('active', true)->values()
            : \App\Models\SegmentacaoCliente::where('active', true)->get();
    $currentUrl = request()->path();
    $currentSlug = '';
    $parts = [];

    if (strpos($currentUrl, 'user/') === 0) {
        $parts = explode('/', $currentUrl);
        if (count($parts) > 1) {
            $currentSlug = $parts[1];
        }
    }

    $backColecoesUrl = null;
    if (count($parts) >= 4 && ($parts[2] ?? null) === 'colecoes') {
        $backColecoesUrl = url('/user/' . $parts[1] . '/colecoes');
    }
    $currentSegmentacao = $segmentacoes->firstWhere('slug', $currentSlug);
    $currentSegmentoNome = optional($currentSegmentacao)->segmento ?? '';
    $currentSegmentoId = optional($currentSegmentacao)->id;
@endphp

@if ($type === '')
    <div x-data="{ mobileOpen: false }">
        <header class="flex items-center justify-between gap-4 p-5">
            <div class="flex items-center space-x-2 py-2">

                @if (\Illuminate\Support\Str::contains(request()->path(), 'colecoes') && count($parts) == 3)
                    <span class="text-base font-semibold lg:hidden">Coleções</span>
                @elseif (count($parts) == 4)
                    <span class="text-base font-semibold lg:hidden">Produtos</span>
                @endif



                @if (count($parts) == 2)
                    <a class="block"
                        href="{{ request()->route('slug') == null ? route('user.segmentacao') : route('user.slug', request()->route('slug')) }}"><svg
                            xmlns="http://www.w3.org/2000/svg" width="162" height="25" viewBox="0 0 162 25"
                            fill="none">
                            <path
                                d="M114.907 14.9051C115.339 14.3924 115.586 13.7152 115.586 13.0063V10.3861H102.162V13.2975H109.579L103.083 20.462C102.651 20.9747 102.359 21.5886 102.359 22.3101V24.9747H115.777V22.0633H108.411L114.907 14.9051Z"
                                fill="#021489" />
                            <path
                                d="M154.958 13.3038C155.764 13.3038 156.418 13.962 156.418 14.7658V20.6076C156.418 21.4114 155.764 22.0696 154.951 22.0696H152.741C151.929 22.0696 151.275 21.4114 151.275 20.6076V14.7658C151.275 13.962 151.929 13.3038 152.741 13.3038H154.951M148.633 10.3924C147.014 10.3924 145.68 11.7025 145.68 13.3101V22.057C145.68 23.6709 147.014 24.981 148.633 24.981H159.085C160.705 24.981 162 23.6772 162 22.057V13.3101C162 11.6962 160.705 10.3924 159.085 10.3924H148.633Z"
                                fill="#021489" />
                            <path
                                d="M94.8339 10.3924H100.428V22.0633C100.428 23.6772 99.133 24.981 97.5137 24.981H94.8339V10.3924Z"
                                fill="#021489" />
                            <path
                                d="M120.476 24.981C118.857 24.981 117.53 23.6772 117.53 22.0633V10.3987H123.124V20.6076C123.124 21.4114 123.797 22.076 124.604 22.076H126.401C127.207 22.076 127.849 21.4177 127.849 20.6139V10.3987H140.847C142.467 10.3987 143.769 11.7089 143.769 13.3165V24.981H138.174V14.7595C138.174 13.9494 137.514 13.3038 136.707 13.3038H133.443V22.057C133.443 23.6709 132.148 24.9747 130.528 24.9747H120.476"
                                fill="#021489" />
                            <path
                                d="M86.6104 5.37342C84.5148 5.37342 82.6352 6.32279 81.4032 7.81646L67.1343 24.9747H75.2308L86.0579 11.9684C86.8708 11.0063 88.09 10.3861 89.4553 10.3861H92.3129L80.2284 24.9747H88.9155C90.9349 24.9747 92.5669 23.3418 92.5669 21.3228V5.37342H86.6167"
                                fill="#021489" />
                            <path
                                d="M68.271 7.82278L54.0021 24.981H62.0986L72.9257 11.9747C73.7385 11.0127 74.9578 10.3924 76.3231 10.3924H79.2696V5.37342H73.4845C71.389 5.37342 69.5157 6.32279 68.2774 7.81646"
                                fill="#021489" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M26.1819 14.519C29.0395 14.6203 31.2303 14.3987 35.2119 13.5316C33.3322 14.9304 31.6748 17.4747 31.4907 19.4177C30.5 17.943 28.2076 15.6456 26.1819 14.5253M37.2122 25C36.4184 22.4684 35.25 16.7975 40.2793 13.2848C43.8672 10.7722 54.7705 7.8038 60.6063 5.67089L64.8038 0C60.8032 2.0443 55.6024 4.14557 50.8715 5.58861C40.3111 8.81646 33.9926 10.7278 25.858 10.8924C20.0031 11.0063 19.9206 8.24684 25.6167 3.31013C19.2157 5.78481 10.2683 8.71519 0 11.0127C9.16334 11.2722 16.1168 13.1266 20.2952 15.5443C26.2454 18.9873 27.6932 22.6076 28.3664 25H37.2122Z"
                                fill="#021489" />
                        </svg></a>
                @else
                    <a class="hidden lg:block"
                        href="{{ request()->route('slug') == null ? route('user.segmentacao') : route('user.slug', request()->route('slug')) }}"><svg
                            xmlns="http://www.w3.org/2000/svg" width="162" height="25" viewBox="0 0 162 25"
                            fill="none">
                            <path
                                d="M114.907 14.9051C115.339 14.3924 115.586 13.7152 115.586 13.0063V10.3861H102.162V13.2975H109.579L103.083 20.462C102.651 20.9747 102.359 21.5886 102.359 22.3101V24.9747H115.777V22.0633H108.411L114.907 14.9051Z"
                                fill="#021489" />
                            <path
                                d="M154.958 13.3038C155.764 13.3038 156.418 13.962 156.418 14.7658V20.6076C156.418 21.4114 155.764 22.0696 154.951 22.0696H152.741C151.929 22.0696 151.275 21.4114 151.275 20.6076V14.7658C151.275 13.962 151.929 13.3038 152.741 13.3038H154.951M148.633 10.3924C147.014 10.3924 145.68 11.7025 145.68 13.3101V22.057C145.68 23.6709 147.014 24.981 148.633 24.981H159.085C160.705 24.981 162 23.6772 162 22.057V13.3101C162 11.6962 160.705 10.3924 159.085 10.3924H148.633Z"
                                fill="#021489" />
                            <path
                                d="M94.8339 10.3924H100.428V22.0633C100.428 23.6772 99.133 24.981 97.5137 24.981H94.8339V10.3924Z"
                                fill="#021489" />
                            <path
                                d="M120.476 24.981C118.857 24.981 117.53 23.6772 117.53 22.0633V10.3987H123.124V20.6076C123.124 21.4114 123.797 22.076 124.604 22.076H126.401C127.207 22.076 127.849 21.4177 127.849 20.6139V10.3987H140.847C142.467 10.3987 143.769 11.7089 143.769 13.3165V24.981H138.174V14.7595C138.174 13.9494 137.514 13.3038 136.707 13.3038H133.443V22.057C133.443 23.6709 132.148 24.9747 130.528 24.9747H120.476"
                                fill="#021489" />
                            <path
                                d="M86.6104 5.37342C84.5148 5.37342 82.6352 6.32279 81.4032 7.81646L67.1343 24.9747H75.2308L86.0579 11.9684C86.8708 11.0063 88.09 10.3861 89.4553 10.3861H92.3129L80.2284 24.9747H88.9155C90.9349 24.9747 92.5669 23.3418 92.5669 21.3228V5.37342H86.6167"
                                fill="#021489" />
                            <path
                                d="M68.271 7.82278L54.0021 24.981H62.0986L72.9257 11.9747C73.7385 11.0127 74.9578 10.3924 76.3231 10.3924H79.2696V5.37342H73.4845C71.389 5.37342 69.5157 6.32279 68.2774 7.81646"
                                fill="#021489" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M26.1819 14.519C29.0395 14.6203 31.2303 14.3987 35.2119 13.5316C33.3322 14.9304 31.6748 17.4747 31.4907 19.4177C30.5 17.943 28.2076 15.6456 26.1819 14.5253M37.2122 25C36.4184 22.4684 35.25 16.7975 40.2793 13.2848C43.8672 10.7722 54.7705 7.8038 60.6063 5.67089L64.8038 0C60.8032 2.0443 55.6024 4.14557 50.8715 5.58861C40.3111 8.81646 33.9926 10.7278 25.858 10.8924C20.0031 11.0063 19.9206 8.24684 25.6167 3.31013C19.2157 5.78481 10.2683 8.71519 0 11.0127C9.16334 11.2722 16.1168 13.1266 20.2952 15.5443C26.2454 18.9873 27.6932 22.6076 28.3664 25H37.2122Z"
                                fill="#021489" />
                        </svg></a>
                @endif
            </div>
            <div class="hidden lg:flex items-center min-h-[40px]">


                <div class="relative inline-block text-left">
                    @if (count($parts) != 4 && count($parts) != 5 && count($parts) != 6)
                        @if ($currentSlug != 'segmentacao')
                            @foreach ($segmentacoes as $segmentacao)
                                @if ($currentSlug == $segmentacao->slug)
                                    <div
                                        class="block w-full bg-[#021489] text-white border-none px-5 py-[10px] rounded-full shadow  focus:outline-none focus:shadow-outline font-normal cursor-pointer hover:bg-[#021489] transition-colors duration-200 text-center">
                                        <a href="{{ route('user.segmentacao') }}" class="flex text-sm ">
                                            {{ $segmentacao->segmento }}

                                            <img src="/images/icones/setas.svg" class="float-right pl-[10px]"
                                                alt="Coleções" />
                                        </a>



                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endif
                </div>



                @if ($user)
                    @if ($currentSlug != 'segmentacao')
                        @if (count($parts) != 6 && (!isset($parts[3]) || $parts[2] != 'blog'))
                            @if ($user->segmentacoesCliente->count() != 1)
                                <!-- CTA Segmentações Cliente -->
                                <div x-data="{
                                    showModal: false,
                                    selectedSegmentacoes: [],
                                    searchSegmentacao: '',
                                    segmentoAtualId: @js($currentSegmentoId),
                                    linhasSelecionadas: [],
                                    linhasFiltroAtivas: [],
                                    todosLinhaAtivo: false,
                                    segmentacoesBase: @js($segmentacoesClienteDisponiveis),
                                    normalizeContexto(value) {
                                        return (value || '')
                                            .toString()
                                            .normalize('NFD')
                                            .replace(/[\u0300-\u036f]/g, '')
                                            .toUpperCase()
                                            .replace(/[^A-Z0-9]+/g, '_')
                                            .replace(/^_+|_+$/g, '');
                                    },
                                    parseLinhas(value) {
                                        return (value || '')
                                            .toString()
                                            .split(/[,;|\/\n]+/)
                                            .map(item => this.normalizeContexto(item))
                                            .filter(Boolean);
                                    },
                                    hasVinculoComSegmentacao(segmentacao) {
                                        if (!this.segmentoAtualId) {
                                            return false;
                                        }
                                
                                        return String(segmentacao.produtos_segmentos || '') === String(this.segmentoAtualId);
                                    },
                                    hasVinculoComLinha(segmentacao) {
                                        if (!Array.isArray(this.linhasSelecionadas) || this.linhasSelecionadas.length === 0) {
                                            return true;
                                        }
                                
                                        const linhasSegmentacao = this.parseLinhas(segmentacao.linha);
                                        if (linhasSegmentacao.length === 0) {
                                            return false;
                                        }
                                
                                        return this.linhasSelecionadas.some(linha => linhasSegmentacao.includes(linha));
                                    },
                                    isSegmentacaoDisponivel(segmentacao) {
                                        return this.hasVinculoComSegmentacao(segmentacao) && this.hasVinculoComLinha(segmentacao);
                                    },
                                    get segmentacoesContexto() {
                                        return (this.segmentacoesBase || []).filter(segmentacao => this.isSegmentacaoDisponivel(segmentacao));
                                    },
                                    get linhasFiltroDisponiveis() {
                                        return [...new Set(this.segmentacoesContexto
                                            .flatMap(segmentacao => this.parseLinhas(segmentacao.linha))
                                            .filter(Boolean))];
                                    },
                                    matchesLinhaFiltro(segmentacao) {
                                        if (this.linhasFiltroAtivas.length === 0) {
                                            return true;
                                        }
                                
                                        const linhasSegmentacao = this.parseLinhas(segmentacao.linha);
                                        return this.linhasFiltroAtivas.some(linha => linhasSegmentacao.includes(linha));
                                    },
                                    toggleLinhaFiltro(linha, checked) {
                                        this.todosLinhaAtivo = false;
                                        const linhaNormalizada = this.normalizeContexto(linha);
                                
                                        if (!linhaNormalizada) {
                                            this.linhasFiltroAtivas = [];
                                        } else if (checked) {
                                            if (!this.linhasFiltroAtivas.includes(linhaNormalizada)) {
                                                this.linhasFiltroAtivas.push(linhaNormalizada);
                                            }
                                        } else {
                                            this.linhasFiltroAtivas = this.linhasFiltroAtivas.filter(item => item !== linhaNormalizada);
                                        }
                                
                                        if (checked && linhaNormalizada) {
                                            const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                            this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                        } else if (!checked && linhaNormalizada) {
                                            const idsParaRemover = (this.segmentacoesContexto || [])
                                                .filter(segmentacao => this.parseLinhas(segmentacao.linha).includes(linhaNormalizada))
                                                .map(segmentacao => segmentacao.id);
                                            this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => !idsParaRemover.includes(id));
                                        }
                                
                                        const idsPermitidos = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                        this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => idsPermitidos.includes(id));
                                        this.persistSelected();
                                    },
                                    toggleTodosLinha(checked) {
                                        this.todosLinhaAtivo = checked;
                                        this.linhasFiltroAtivas = [];
                                
                                        if (checked) {
                                            const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                            this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                        } else {
                                            this.selectedSegmentacoes = [];
                                        }
                                
                                        this.persistSelected();
                                    },
                                    syncLinhaFiltroAtual() {
                                        const linhasPreferenciais = this.linhasSelecionadas.filter(linha => this.linhasFiltroDisponiveis.includes(linha));
                                        this.linhasFiltroAtivas = linhasPreferenciais;
                                        this.todosLinhaAtivo = false;
                                        if (this.linhasFiltroAtivas.length > 0) {
                                            const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                            this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                            this.persistSelected();
                                        }
                                    },
                                    get segmentacoesDisponiveis() {
                                        const termoBusca = (this.searchSegmentacao || '').toLowerCase().trim();
                                
                                        return this.segmentacoesContexto.filter(segmentacao => {
                                            if (!this.matchesLinhaFiltro(segmentacao)) {
                                                return false;
                                            }
                                
                                            if (!termoBusca) {
                                                return true;
                                            }
                                
                                            return (segmentacao.nome || '').toLowerCase().includes(termoBusca);
                                        });
                                    },
                                    get totalSelecionadasVisiveis() {
                                        const idsVisiveis = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                        return this.selectedSegmentacoes.filter(id => idsVisiveis.includes(id)).length;
                                    },
                                    atualizarLinhasSelecionadas(linhas) {
                                        this.linhasSelecionadas = (Array.isArray(linhas) ? linhas : [])
                                            .map(item => this.normalizeContexto(item))
                                            .filter(Boolean);
                                        this.syncLinhaFiltroAtual();
                                        const idsPermitidos = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                        this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => idsPermitidos.includes(id));
                                        this.persistSelected();
                                    },
                                    toggleSegmentacao(id) {
                                        const index = this.selectedSegmentacoes.indexOf(id);
                                        if (index > -1) {
                                            this.selectedSegmentacoes.splice(index, 1);
                                        } else {
                                            this.selectedSegmentacoes.push(id);
                                        }
                                        this.persistSelected();
                                    },
                                    toggleSelectAll(checked) {
                                        if (checked) {
                                            this.selectedSegmentacoes = this.segmentacoesDisponiveis.map(s => s.id);
                                        } else {
                                            this.selectedSegmentacoes = [];
                                        }
                                        this.persistSelected();
                                    },
                                    persistSelected() {
                                        try {
                                            localStorage.setItem('selectedSegmentacoes', JSON.stringify(this.selectedSegmentacoes));
                                        } catch (e) {
                                            console.error('Erro ao salvar segmentações selecionadas:', e);
                                        }
                                    },
                                    init() {
                                        const saved = localStorage.getItem('selectedSegmentacoes');
                                        if (saved) {
                                            try {
                                                this.selectedSegmentacoes = JSON.parse(saved);
                                            } catch (e) {
                                                this.selectedSegmentacoes = [];
                                            }
                                        }
                                        const linhasSalvas = JSON.parse(localStorage.getItem('selectedSegmentacaoLinhas') || '[]');
                                        this.atualizarLinhasSelecionadas(linhasSalvas);
                                        window.addEventListener('segmentacao-category-changed', (event) => {
                                            this.atualizarLinhasSelecionadas(event.detail?.linhas || []);
                                        });
                                    }
                                }" class="relative pl-[10px]">
                                    <!-- Botão CTA -->
                                    <button @click="showModal = true"
                                        class="flex items-center space-x-2 px-5 py-[9px] text-black  rounded-full hover:opacity-80 transition-colors border border-black">

                                        <div class="flex flex-row items-start">
                                            <span class="text-sm ">Segmentos: </span>
                                            <div class="text-sm max-w-[200px] truncate px-[5px] py-0">
                                                <span class="text-[#7A7A7A]"
                                                    x-text="selectedSegmentacoes.length === 0 ? 'Todos' : selectedSegmentacoes.length === 1 ? '1 seleção' : selectedSegmentacoes.length + ' seleções'"></span>
                                            </div>
                                        </div>

                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="8"
                                            class="ml-0 mr-0" viewBox="0 0 12 8" fill="none">
                                            <path d="M1.10938 1.5L6.05912 6.44975L11.0089 1.5" stroke="black"
                                                stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                    </button>

                                    <!-- Modal -->
                                    <div x-show="showModal" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click.away="showModal = false"
                                        @keydown.escape.window="showModal = false"
                                        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

                                        <!-- Overlay -->
                                        <div class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>

                                        <!-- Modal Content -->
                                        <div class="flex items-center justify-center min-h-screen px-4 py-6">
                                            <div
                                                class="relative z-50 bg-white rounded-lg max-w-xl w-full mx-4 p-6 max-h-[60vh] overflow-hidden flex flex-col">
                                                <div class="flex justify-center items-center mb-6">
                                                    <h2 class="text-2xl font-semibold text-gray-900">Selecionar
                                                        Segmentos
                                                    </h2>
                                                </div>

                                                <!-- Header com controles -->
                                                <div class="mb-4">
                                                    <div class="text-xs text-gray-500 mb-2">Linha</div>
                                                    <div class="flex items-center gap-2 flex-wrap mb-3">
                                                        <label class="flex items-center">
                                                            <input type="checkbox" :checked="todosLinhaAtivo"
                                                                @change="toggleTodosLinha($event.target.checked)"
                                                                class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-2">
                                                            <span>Todos</span>
                                                        </label>
                                                        <template x-for="linha in linhasFiltroDisponiveis"
                                                            :key="linha">
                                                            <label class="inline-flex items-center">
                                                                <input type="checkbox"
                                                                    :checked="linhasFiltroAtivas.includes(linha)"
                                                                    @change="toggleLinhaFiltro(linha, $event.target.checked)"
                                                                    class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-2">
                                                                <span class="text-base"
                                                                    x-text="linha.replaceAll('_', ' ')"></span>
                                                            </label>
                                                        </template>
                                                    </div>
                                                    <div class="flex justify-between items-center gap-4">
                                                        <div class="flex items-center gap-4">
                                                            <span class="text-xs opacity-50">Selecionados: <span
                                                                    x-text="totalSelecionadasVisiveis"></span></span>
                                                            <span class="text-xs opacity-50">Total: <span
                                                                    x-text="segmentacoesDisponiveis.length"></span></span>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="flex items-center border-b border-b-black px-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="h-4 w-4 text-black ml-1"
                                                                    viewBox="0 0 20 20" fill="currentColor">
                                                                    <path fill-rule="evenodd"
                                                                        d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                                                        clip-rule="evenodd" />
                                                                </svg>
                                                                <input type="text" x-model="searchSegmentacao"
                                                                    placeholder="Buscar"
                                                                    class="input-estilizado bg-transparent border-0 focus:outline-none focus:ring-0 p-1" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Cabeçalho da tabela -->
                                                <div class="py-1 px-4 grid grid-cols-12 gap-4 text-xs">
                                                    <div class="col-span-12">Segmentação</div>
                                                </div>

                                                <!-- Lista de segmentações -->
                                                <div class="flex-1 overflow-y-auto" id="segmentacoesList">
                                                    <table class="min-w-full bg-white" id="segmentacaoTable">
                                                        <tbody id="segmentacaoTableBody">
                                                            <template x-for="segmentacao in segmentacoesDisponiveis"
                                                                :key="segmentacao.id">
                                                                <tr class="segmentacao-row">
                                                                    <td class="py-[10px] px-4 text-sm">
                                                                        <label
                                                                            class="inline-flex items-center gap-1 cursor-pointer">
                                                                            <input type="checkbox"
                                                                                :checked="selectedSegmentacoes.includes(
                                                                                    segmentacao
                                                                                    .id)"
                                                                                @change="toggleSegmentacao(segmentacao.id)"
                                                                                class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-3">
                                                                            <span x-text="segmentacao.nome"
                                                                                class="text-sm text-gray-900"></span>
                                                                        </label>
                                                                    </td>
                                                                </tr>
                                                            </template>
                                                            <tr x-show="segmentacoesDisponiveis.length === 0">
                                                                <td class="py-[10px] px-4 text-sm text-gray-500">
                                                                    Nenhum segmento disponivel para a
                                                                    categoria/segmentacao atual.
                                                                    selecionada.
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>


                                                <div class="pt-4 mt-4">
                                                    <div class="flex justify-center gap-4">
                                                        <button @click="showModal = false"
                                                            class="w-full bg-black text-white font-normal text-base py-3 px-4 rounded-full hover:bg-gray-800 transition-colors">
                                                            Salvar
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Footer com botões -->
                                                <div class="pt-4 mt-4">
                                                    <div class="flex justify-center gap-4">
                                                        <button @click="showModal = false" type="button"
                                                            class="flex items-center border border-black rounded-full px-6 py-3 text-sm hover:bg-gray-200 transition">
                                                            Voltar
                                                            <img src="/images/icon-voltar.png" alt=""
                                                                class="ml-2 w-4 h-4" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endif
                @endif


                @if ($user)
                    @if (count($parts) != 4 && count($parts) != 5 && count($parts) != 6)
                        <a href="/user/conta" rel="noopener noreferrer" class="text-gray-700 font-normal pl-[20px]">
                            {{ $user->name ?? 'Usuário' }}
                        </a>
                        <a href="/user/conta" class="px-5" rel="noopener noreferrer">
                            <img src="/images/icones/user.svg" alt="User" />
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                            @csrf
                            <button
                                class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-150"
                                onclick="localStorage.removeItem('selectedSegmentacoes'); localStorage.removeItem('selectedSegmentacaoCategoria'); localStorage.removeItem('selectedSegmentacaoLinhas');">
                                <img src="/images/icones/logout.svg" alt="Logout" />
                            </button>
                        </form>
                    @else
                        <div class="pl-4">
                            <a href="{{ $backColecoesUrl ?? url()->previous() }}"
                                class="flex items-center border border-black rounded-full px-3 py-2 text-md bg-gray-100 hover:bg-gray-200 transition text-[14px]">
                                Voltar
                                <img src="/images/icon-voltar.png" alt="" class="px-1" />
                            </a>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="flex items-center space-x-2">
                        <i class="fa-regular fa-user text-gray-700 hover:text-blue-600 text-lg"></i>
                        <span class="text-gray-700 font-semibold hover:text-blue-600">Login</span>
                    </a>
                @endif
            </div>

            <div class="flex items-center lg:hidden">
                @if (\Illuminate\Support\Str::contains(request()->path(), 'colecoes'))
                    <a href="{{ $backColecoesUrl ?? url()->previous() }}"
                        class="flex items-center border border-black rounded-full px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 transition">
                        Voltar
                        <img src="/images/icon-voltar.png" alt="" class="px-1" />
                    </a>
                @else
                    <button @click="mobileOpen = !mobileOpen"
                        class="flex items-center justify-center w-10 h-10  text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                @endif
            </div>
        </header>

        <div x-show="mobileOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 lg:hidden" style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileOpen = false"></div>
            <div class="absolute top-0 right-0 w-64 max-w-full h-full bg-white shadow-lg flex flex-col p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="font-semibold text-gray-800">Menu</span>
                    <button @click="mobileOpen = false"
                        class="p-2 rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto space-y-4">

                    @if (isset($segmentacoes) && $segmentacoes->count())
                        @if ($currentSlug != 'segmentacao')
                            @foreach ($segmentacoes as $segmentacao)
                                @if ($currentSlug == $segmentacao->slug)
                                    <a href="{{ route('user.segmentacao') }}"
                                        class="block w-full bg-black text-white px-4 py-2 rounded-full text-sm text-center">
                                        {{ $segmentacao->segmento }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endif
                    @if ($user)
                        @if ($currentSlug != 'segmentacao')
                            @if (count($parts) != 6)
                                @if ($user->segmentacoesCliente->count() != 1)
                                    <div x-data="{
                                        showModal: false,
                                        selectedSegmentacoes: [],
                                        searchSegmentacao: '',
                                        segmentoAtualId: @js($currentSegmentoId),
                                        linhasSelecionadas: [],
                                        linhasFiltroAtivas: [],
                                        todosLinhaAtivo: false,
                                        segmentacoesBase: @js($segmentacoesClienteDisponiveis),
                                        normalizeContexto(value) {
                                            return (value || '')
                                                .toString()
                                                .normalize('NFD')
                                                .replace(/[\u0300-\u036f]/g, '')
                                                .toUpperCase()
                                                .replace(/[^A-Z0-9]+/g, '_')
                                                .replace(/^_+|_+$/g, '');
                                        },
                                        parseLinhas(value) {
                                            return (value || '')
                                                .toString()
                                                .split(/[,;|\/\n]+/)
                                                .map(item => this.normalizeContexto(item))
                                                .filter(Boolean);
                                        },
                                        hasVinculoComSegmentacao(segmentacao) {
                                            if (!this.segmentoAtualId) {
                                                return false;
                                            }
                                    
                                            return String(segmentacao.produtos_segmentos || '') === String(this.segmentoAtualId);
                                        },
                                        hasVinculoComLinha(segmentacao) {
                                            if (!Array.isArray(this.linhasSelecionadas) || this.linhasSelecionadas.length === 0) {
                                                return true;
                                            }
                                    
                                            const linhasSegmentacao = this.parseLinhas(segmentacao.linha);
                                            if (linhasSegmentacao.length === 0) {
                                                return false;
                                            }
                                    
                                            return this.linhasSelecionadas.some(linha => linhasSegmentacao.includes(linha));
                                        },
                                        isSegmentacaoDisponivel(segmentacao) {
                                            return this.hasVinculoComSegmentacao(segmentacao) && this.hasVinculoComLinha(segmentacao);
                                        },
                                        get segmentacoesContexto() {
                                            return (this.segmentacoesBase || []).filter(segmentacao => this.isSegmentacaoDisponivel(segmentacao));
                                        },
                                        get linhasFiltroDisponiveis() {
                                            return [...new Set(this.segmentacoesContexto
                                                .flatMap(segmentacao => this.parseLinhas(segmentacao.linha))
                                                .filter(Boolean))];
                                        },
                                        matchesLinhaFiltro(segmentacao) {
                                            if (this.linhasFiltroAtivas.length === 0) {
                                                return true;
                                            }
                                    
                                            const linhasSegmentacao = this.parseLinhas(segmentacao.linha);
                                            return this.linhasFiltroAtivas.some(linha => linhasSegmentacao.includes(linha));
                                        },
                                        toggleLinhaFiltro(linha, checked) {
                                            this.todosLinhaAtivo = false;
                                            const linhaNormalizada = this.normalizeContexto(linha);
                                    
                                            if (!linhaNormalizada) {
                                                this.linhasFiltroAtivas = [];
                                            } else if (checked) {
                                                if (!this.linhasFiltroAtivas.includes(linhaNormalizada)) {
                                                    this.linhasFiltroAtivas.push(linhaNormalizada);
                                                }
                                            } else {
                                                this.linhasFiltroAtivas = this.linhasFiltroAtivas.filter(item => item !== linhaNormalizada);
                                            }
                                    
                                            if (checked && linhaNormalizada) {
                                                const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                                this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                            } else if (!checked && linhaNormalizada) {
                                                const idsParaRemover = (this.segmentacoesContexto || [])
                                                    .filter(segmentacao => this.parseLinhas(segmentacao.linha).includes(linhaNormalizada))
                                                    .map(segmentacao => segmentacao.id);
                                                this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => !idsParaRemover.includes(id));
                                            }
                                    
                                            const idsPermitidos = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                            this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => idsPermitidos.includes(id));
                                            this.persistSelected();
                                        },
                                        toggleTodosLinha(checked) {
                                            this.todosLinhaAtivo = checked;
                                            this.linhasFiltroAtivas = [];
                                    
                                            if (checked) {
                                                const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                                this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                            } else {
                                                this.selectedSegmentacoes = [];
                                            }
                                    
                                            this.persistSelected();
                                        },
                                        syncLinhaFiltroAtual() {
                                            const linhasPreferenciais = this.linhasSelecionadas.filter(linha => this.linhasFiltroDisponiveis.includes(linha));
                                            this.linhasFiltroAtivas = linhasPreferenciais;
                                            this.todosLinhaAtivo = false;
                                            if (this.linhasFiltroAtivas.length > 0) {
                                                const idsVisiveis = this.segmentacoesDisponiveis.map(s => s.id);
                                                this.selectedSegmentacoes = Array.from(new Set([...this.selectedSegmentacoes, ...idsVisiveis]));
                                                this.persistSelected();
                                            }
                                        },
                                        get segmentacoesDisponiveis() {
                                            const termoBusca = (this.searchSegmentacao || '').toLowerCase().trim();
                                    
                                            return this.segmentacoesContexto.filter(segmentacao => {
                                                if (!this.matchesLinhaFiltro(segmentacao)) {
                                                    return false;
                                                }
                                    
                                                if (!termoBusca) {
                                                    return true;
                                                }
                                    
                                                return (segmentacao.nome || '').toLowerCase().includes(termoBusca);
                                            });
                                        },
                                        get totalSelecionadasVisiveis() {
                                            const idsVisiveis = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                            return this.selectedSegmentacoes.filter(id => idsVisiveis.includes(id)).length;
                                        },
                                        atualizarLinhasSelecionadas(linhas) {
                                            this.linhasSelecionadas = (Array.isArray(linhas) ? linhas : [])
                                                .map(item => this.normalizeContexto(item))
                                                .filter(Boolean);
                                            this.syncLinhaFiltroAtual();
                                            const idsPermitidos = this.segmentacoesDisponiveis.map(segmentacao => segmentacao.id);
                                            this.selectedSegmentacoes = this.selectedSegmentacoes.filter(id => idsPermitidos.includes(id));
                                            this.persistSelected();
                                        },
                                        toggleSegmentacao(id) {
                                            const index = this.selectedSegmentacoes.indexOf(id);
                                            if (index > -1) {
                                                this.selectedSegmentacoes.splice(index, 1);
                                            } else {
                                                this.selectedSegmentacoes.push(id);
                                            }
                                            this.persistSelected();
                                        },
                                        toggleSelectAll(checked) {
                                            if (checked) {
                                                this.selectedSegmentacoes = this.segmentacoesDisponiveis.map(s => s.id);
                                            } else {
                                                this.selectedSegmentacoes = [];
                                            }
                                            this.persistSelected();
                                        },
                                        persistSelected() {
                                            try {
                                                localStorage.setItem('selectedSegmentacoes', JSON.stringify(this.selectedSegmentacoes));
                                            } catch (e) {}
                                        },
                                        init() {
                                            const saved = localStorage.getItem('selectedSegmentacoes');
                                            if (saved) {
                                                try {
                                                    this.selectedSegmentacoes = JSON.parse(saved);
                                                } catch (e) {
                                                    this.selectedSegmentacoes = [];
                                                }
                                            }
                                            const linhasSalvas = JSON.parse(localStorage.getItem('selectedSegmentacaoLinhas') || '[]');
                                            this.atualizarLinhasSelecionadas(linhasSalvas);
                                            window.addEventListener('segmentacao-category-changed', (event) => {
                                                this.atualizarLinhasSelecionadas(event.detail?.linhas || []);
                                            });
                                        }
                                    }">
                                        <button @click="showModal = true"
                                            class="flex items-center justify-between space-x-2 px-4 py-2 text-black rounded-full hover:opacity-80 transition-colors border border-black w-full">
                                            <div class="flex flex-row items-start">
                                                <span class="text-sm">Segmentos:</span>
                                                <div class="text-sm max-w-[160px] truncate px-[5px]">
                                                    <span class="text-[#7A7A7A]"
                                                        x-text="selectedSegmentacoes.length === 0 ? 'Todos' : selectedSegmentacoes.length === 1 ? '1 seleção' : selectedSegmentacoes.length + ' seleções'"></span>
                                                </div>
                                            </div>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="8"
                                                class="ml-0 mr-0" viewBox="0 0 12 8" fill="none">
                                                <path d="M1.10938 1.5L6.05912 6.44975L11.0089 1.5" stroke="black"
                                                    stroke-width="1.5" stroke-linecap="round" />
                                            </svg>
                                        </button>

                                        <div x-show="showModal" x-transition:enter="ease-out duration-300"
                                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                            x-transition:leave="ease-in duration-200"
                                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            @click.away="showModal = false" @keydown.escape.window="showModal = false"
                                            class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                                            <div class="fixed inset-0 bg-black bg-opacity-50 z-40"></div>
                                            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                                                <div
                                                    class="relative z-50 bg-white rounded-lg max-w-xl w-full mx-4 p-6 max-h-[60vh] overflow-hidden flex flex-col">
                                                    <div class="flex justify-center items-center mb-6">
                                                        <h2 class="text-2xl font-semibold text-gray-900">Selecionar
                                                            Segmentos</h2>
                                                    </div>
                                                    <div class="mb-4">
                                                        <div class="text-xs text-gray-500 mb-2">Linha</div>
                                                        <div class="flex items-center gap-2 flex-wrap mb-3">
                                                            <label class="flex items-center">
                                                                <input type="checkbox" :checked="todosLinhaAtivo"
                                                                    @change="toggleTodosLinha($event.target.checked)"
                                                                    class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-2">
                                                                <span>Todos</span>
                                                            </label>
                                                            <template x-for="linha in linhasFiltroDisponiveis"
                                                                :key="linha">
                                                                <label class="inline-flex items-center">
                                                                    <input type="checkbox"
                                                                        :checked="linhasFiltroAtivas.includes(linha)"
                                                                        @change="toggleLinhaFiltro(linha, $event.target.checked)"
                                                                        class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-2">
                                                                    <span class="text-base"
                                                                        x-text="linha.replaceAll('_', ' ')"></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                        <div class="flex justify-between items-center gap-4">
                                                            <div class="flex items-center gap-4">
                                                                <span class="text-xs opacity-50">Selecionados: <span
                                                                        x-text="totalSelecionadasVisiveis"></span></span>
                                                                <span class="text-xs opacity-50">Total: <span
                                                                        x-text="segmentacoesDisponiveis.length"></span></span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div
                                                                    class="flex items-center border-b border-b-black px-2">
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        class="h-4 w-4 text-black ml-1"
                                                                        viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd"
                                                                            d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                                                            clip-rule="evenodd" />
                                                                    </svg>
                                                                    <input type="text" x-model="searchSegmentacao"
                                                                        placeholder="Buscar"
                                                                        class="input-estilizado bg-transparent border-0 focus:outline-none focus:ring-0 p-1" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="py-1 px-4 grid grid-cols-12 gap-4 text-xs">
                                                        <div class="col-span-12">Segmentação</div>
                                                    </div>
                                                    <div class="flex-1 overflow-y-auto" id="segmentacoesListMobile">
                                                        <table class="min-w-full bg-white"
                                                            id="segmentacaoTableMobile">
                                                            <tbody id="segmentacaoTableBodyMobile">
                                                                <template
                                                                    x-for="segmentacao in segmentacoesDisponiveis"
                                                                    :key="segmentacao.id">
                                                                    <tr class="segmentacao-row-mobile">
                                                                        <td class="py-[10px] px-4 text-sm">
                                                                            <label
                                                                                class="inline-flex items-center gap-1 cursor-pointer">
                                                                                <input type="checkbox"
                                                                                    :checked="selectedSegmentacoes.includes(
                                                                                        segmentacao.id)"
                                                                                    @change="toggleSegmentacao(segmentacao.id)"
                                                                                    class="w-[15px] h-[15px] rounded border-2 border-[#7A7A7A] bg-white checked:bg-white checked:border-[#7A7A7A] focus:ring-0 cursor-pointer relative mr-3">
                                                                                <span x-text="segmentacao.nome"
                                                                                    class="text-sm text-gray-900"></span>
                                                                            </label>
                                                                        </td>
                                                                    </tr>
                                                                </template>
                                                                <tr x-show="segmentacoesDisponiveis.length === 0">
                                                                    <td class="py-[10px] px-4 text-sm text-gray-500">
                                                                        Nenhum segmento disponivel para a
                                                                        categoria/segmentacao atual.
                                                                        selecionada.
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="pt-4 mt-4">
                                                        <div class="flex justify-center gap-4">
                                                            <button @click="showModal = false"
                                                                class="w-full bg-black text-white font-normal text-base py-3 px-4 rounded-full hover:bg-gray-800 transition-colors">
                                                                Salvar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="pt-4 mt-4">
                                                        <div class="flex justify-center gap-4">
                                                            <button @click="showModal = false" type="button"
                                                                class="flex items-center border border-black rounded-full px-6 py-3 text-sm hover:bg-gray-200 transition">
                                                                Voltar
                                                                <img src="/images/icon-voltar.png" alt=""
                                                                    class="ml-2 w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif
                    @endif


                    @if ($user)
                        @if (count($parts) != 4 && count($parts) != 5 && count($parts) != 6)
                            <a href="/user/conta"
                                class="flex items-center justify-between py-2 border-b border-gray-200 text-gray-700">
                                <span>{{ $user->name ?? 'Usuário' }}</span>
                                <img src="/images/icones/user.svg" alt="User" class="w-5 h-5" />
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="pt-2">
                                @csrf
                                <button
                                    class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-150"
                                    onclick="localStorage.removeItem('selectedSegmentacoes'); localStorage.removeItem('selectedSegmentacaoCategoria'); localStorage.removeItem('selectedSegmentacaoLinhas');">
                                    <img src="/images/icones/logout.svg" alt="Logout" class="w-4 h-4 mr-2" />
                                    <span>Sair</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ url()->previous() }}"
                                class="flex items-center border border-black rounded-full px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 transition">
                                Voltar
                                <img src="/images/icon-voltar.png" alt="" class="px-1" />
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="flex items-center space-x-2 py-2 border-b border-gray-200">
                            <i class="fa-regular fa-user text-gray-700 text-lg"></i>
                            <span class="text-gray-700 font-semibold">Login</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@elseif ($type === 'produto')
@endif


<script>
    function normalizeSegmentacaoCategoria(value) {
        return (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toUpperCase()
            .replace(/[^A-Z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function syncSegmentacaoCategoriaSelecionada(categoria) {
        const categoriaNormalizada = normalizeSegmentacaoCategoria(categoria);

        try {
            if (categoriaNormalizada) {
                localStorage.setItem('selectedSegmentacaoCategoria', categoriaNormalizada);
            } else {
                localStorage.removeItem('selectedSegmentacaoCategoria');
            }
        } catch (e) {
            console.error('Erro ao salvar categoria de segmentacao:', e);
        }

        window.dispatchEvent(new CustomEvent('segmentacao-category-changed', {
            detail: {
                categoria: categoriaNormalizada,
                linhas: getSegmentacaoLinhasSelecionadas()
            }
        }));
    }

    function getSegmentacaoLinhasSelecionadas() {
        try {
            const saved = JSON.parse(localStorage.getItem('selectedSegmentacaoLinhas') || '[]');
            return Array.isArray(saved) ? saved : [];
        } catch (e) {
            return [];
        }
    }

    function syncSegmentacaoLinhasSelecionadas(linhas) {
        const linhasNormalizadas = (Array.isArray(linhas) ? linhas : [])
            .map(normalizeSegmentacaoCategoria)
            .filter(Boolean);

        try {
            if (linhasNormalizadas.length > 0) {
                localStorage.setItem('selectedSegmentacaoLinhas', JSON.stringify(linhasNormalizadas));
            } else {
                localStorage.removeItem('selectedSegmentacaoLinhas');
            }
        } catch (e) {
            console.error('Erro ao salvar linhas de segmentacao:', e);
        }

        window.dispatchEvent(new CustomEvent('segmentacao-category-changed', {
            detail: {
                categoria: localStorage.getItem('selectedSegmentacaoCategoria') || '',
                linhas: linhasNormalizadas
            }
        }));
    }

    // Interceptar navegações para páginas de produtos e adicionar segmentações selecionadas
    document.addEventListener('DOMContentLoaded', function() {

        // Função para adicionar parâmetros de segmentação aos links de produtos
        function addSegmentacaoParams(url) {
            const selectedSegmentacoes = localStorage.getItem('selectedSegmentacoes');

            if (selectedSegmentacoes) {
                try {
                    const segmentacoes = JSON.parse(selectedSegmentacoes);
                    if (segmentacoes && segmentacoes.length > 0) {
                        const urlObj = new URL(url, window.location.origin);
                        segmentacoes.forEach((id, index) => {
                            //urlObj.searchParams.append(`selected_segmentacoes[${index}]`, id);
                        });
                        return urlObj.toString();
                    }
                } catch (e) {
                    console.error('Erro ao processar segmentações selecionadas:', e);
                }
            }
            return url;
        }

        // Interceptar cliques em links que levam para páginas de produtos
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href) {
                const href = link.getAttribute('href');
                // Verificar se é um link para página de produtos (contém padrão /user/slug/colecao)
                if (href && href.match(/\/user\/[^/]+\/[^/]+$/)) {
                    e.preventDefault();
                    const newUrl = addSegmentacaoParams(href);
                    window.location.href = newUrl;
                }
            }
        });

        // Interceptar submissões de formulários que redirecionam para produtos
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.action) {
                const action = form.getAttribute('action');
                if (action && action.match(/\/user\/[^/]+\/[^/]+$/)) {
                    const selectedSegmentacoes = localStorage.getItem('selectedSegmentacoes');

                    if (selectedSegmentacoes) {
                        try {
                            const segmentacoes = JSON.parse(selectedSegmentacoes);
                            if (segmentacoes && segmentacoes.length > 0) {
                                // Adicionar campos hidden para as segmentações
                                segmentacoes.forEach((id, index) => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = `selected_segmentacoes[${index}]`;
                                    input.value = id;
                                    form.appendChild(input);
                                });
                            }
                        } catch (e) {
                            console.error('Erro ao processar segmentações selecionadas:', e);
                        }
                    }
                }
            }
        });
    });
</script>
