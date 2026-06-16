@props(['user' => null])

<header class="fixed top-0 left-0 right-0 flex items-center justify-between gap-4 p-5 bg-[#F1F1F1] z-50">
    <div class="flex items-center space-x-2 py-1">
        <a
            href="{{ request()->route('slug') == null ? route('user.segmentacao') : route('user.slug', request()->route('slug')) }}"><svg
                xmlns="http://www.w3.org/2000/svg" width="162" height="25" viewBox="0 0 162 25" fill="none">
                <path
                    d="M114.907 14.9051C115.339 14.3924 115.586 13.7152 115.586 13.0063V10.3861H102.162V13.2975H109.579L103.083 20.462C102.651 20.9747 102.359 21.5886 102.359 22.3101V24.9747H115.777V22.0633H108.411L114.907 14.9051Z"
                    fill="#021489" />
                <path
                    d="M154.958 13.3038C155.764 13.3038 156.418 13.962 156.418 14.7658V20.6076C156.418 21.4114 155.764 22.0696 154.951 22.0696H152.741C151.929 22.0696 151.275 21.4114 151.275 20.6076V14.7658C151.275 13.962 151.929 13.3038 152.741 13.3038H154.951M148.633 10.3924C147.014 10.3924 145.68 11.7025 145.68 13.3101V22.057C145.68 23.6709 147.014 24.981 148.633 24.981H159.085C160.705 24.981 162 23.6772 162 22.057V13.3101C162 11.6962 160.705 10.3924 159.085 10.3924H148.633Z"
                    fill="#021489" />
                <path d="M94.8339 10.3924H100.428V22.0633C100.428 23.6772 99.133 24.981 97.5137 24.981H94.8339V10.3924Z"
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
    </div>
    <div class="flex items-center space-x-4">
        @php
            $segmentacoes = \App\Models\Segmentacao::where('active', 1)->get();
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
        @endphp

        <style>
            #segmentacao-select {
                min-width: 120px;
                max-width: 123px;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                text-align: center;
                font-weight: 400;
                letter-spacing: 0.01em;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
                padding-left: 15px;
                padding-right: 25px;
                font-size: 14px;
            }

            #segmentacao-select::-ms-expand {
                display: none;
            }

            #segmentacao-select option {
                background-color: white;
                color: black;
                padding: 8px;
                text-align: left;
            }
        </style>
        <div class="relative inline-block text-left">
            @if (count($parts) != 4 && count($parts) != 5 && count($parts) != 6)
                @if ($currentSlug != 'segmentacao')
                    @foreach ($segmentacoes as $segmentacao)
                        @if ($currentSlug == $segmentacao->slug)
                            <div
                                class="block w-full bg-black text-white border-none px-5 py-2 pr-5 rounded-full shadow leading-tight focus:outline-none focus:shadow-outline font-normal text-sm cursor-pointer hover:bg-gray-900 transition-colors duration-200 text-center">
                                <a href="{{ route('user.segmentacao') }}" class="">
                                    {{ $segmentacao->segmento }}

                                    <img src="/images/icones/setas.svg" class="float-right pl-[0.5rem] pt-1"
                                        alt="Coleções" />
                                </a>



                            </div>
                        @endif
                    @endforeach
                @endif




            @endif
        </div>

        @if ($user)
            @if (count($parts) != 4 && count($parts) != 5 && count($parts) != 2 && count($parts) != 6)
                <button class="text-gray-700 font-normal hover:text-gray-400">
                    {{ $user->name ?? 'Usuário' }}
                </button>
                <a href="/user/conta" rel="noopener noreferrer">
                    <img src="/images/icones/user.svg" alt="User" />
                </a>
                <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                    @csrf
                    <button
                        class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-150"
                        onclick="localStorage.removeItem('selectedSegmentacoes');">
                        <img src="/images/icones/logout.svg" alt="Logout" />
                    </button>
                </form>
            @else
                <a href="javascript:history.back();"
                    class="flex items-center border border-black rounded-full px-3 py-2 text-md bg-gray-100 hover:bg-gray-200 transition text-[14px]">
                    Voltar
                    <img src="/images/icon-voltar.png" alt="" class="px-1" />
                </a>
            @endif
        @else
            <a href="{{ route('login') }}" class="flex items-center space-x-2">
                <i class="fa-regular fa-user text-gray-700 hover:text-blue-600 text-lg"></i>
                <span class="text-gray-700 font-semibold hover:text-blue-600">Login</span>
            </a>
        @endif
    </div>
</header>
