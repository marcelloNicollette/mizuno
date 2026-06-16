<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mizuno</title>
    <!-- Favicon -->
    <link rel="icon" href="/images/Favicon_Mizuno.svg" type="image/svg+xml">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        [type=text]:focus,
        input:where(:not([type])):focus,
        [type=email]:focus,
        [type=url]:focus,
        [type=password]:focus,
        [type=number]:focus,
        [type=date]:focus,
        [type=datetime-local]:focus,
        [type=month]:focus,
        [type=search]:focus,
        [type=tel]:focus,
        [type=time]:focus,
        [type=week]:focus,
        [multiple]:focus,
        textarea:focus,
        select:focus {

            border-color: #FFF;
        }

        /* Customização do autocomplete */
        input[type="email"]:-webkit-autofill,
        input[type="password"]:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #021489 inset !important;
            -webkit-text-fill-color: white !important;
            border-bottom: 1px solid white;
        }

        .input-style {
            padding: 20px 0;
        }
    </style>
</head>

<body class="items-center lg:justify-center bg-[#021489]">
    <!-- Layout Mobile/Tablet: Mensagem de aplicação não disponível -->
    <div class="lg:hidden flex flex-col min-h-screen p-2">
        <!-- Seção azul com mensagem -->
        <div class="bg-[#021489] flex flex-col justify-between items-center p-4 flex-1">
            <!-- Logo no topo -->
            <div class="flex justify-center w-full pt-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="207" height="32" viewBox="0 0 207 32"
                    fill="none">
                    <path
                        d="M146.825 19.0785C147.377 18.4223 147.694 17.5554 147.694 16.6481V13.2942H130.54V17.0208H140.018L131.717 26.1914C131.165 26.8476 130.792 27.6334 130.792 28.557V31.9676H147.937V28.241H138.525L146.825 19.0785Z"
                        fill="white" />
                    <path
                        d="M198.001 17.0289C199.032 17.0289 199.868 17.8714 199.868 18.9003V26.3777C199.868 27.4066 199.032 28.2491 197.993 28.2491H195.17C194.131 28.2491 193.295 27.4066 193.295 26.3777V18.9003C193.295 17.8714 194.131 17.0289 195.17 17.0289H197.993M189.92 13.3023C187.851 13.3023 186.147 14.9792 186.147 17.037V28.2329C186.147 30.2987 187.851 31.9757 189.92 31.9757H203.276C205.345 31.9757 207 30.3068 207 28.2329V17.037C207 14.9711 205.345 13.3023 203.276 13.3023H189.92Z"
                        fill="white" />
                    <path
                        d="M121.177 13.3023H128.325V28.241C128.325 30.3068 126.67 31.9757 124.601 31.9757H121.177V13.3023Z"
                        fill="white" />
                    <path
                        d="M153.942 31.9757C151.872 31.9757 150.177 30.3068 150.177 28.241V13.3104H157.325V26.3777C157.325 27.4066 158.185 28.2572 159.216 28.2572H161.512C162.543 28.2572 163.362 27.4147 163.362 26.3858V13.3104H179.972C182.041 13.3104 183.704 14.9873 183.704 17.0451V31.9757H176.556V18.8922C176.556 17.8552 175.712 17.0289 174.681 17.0289H170.511V28.2329C170.511 30.2987 168.855 31.9676 166.786 31.9676H153.942"
                        fill="white" />
                    <path
                        d="M110.669 6.87797C107.991 6.87797 105.589 8.09317 104.015 10.0051L85.7828 31.9676H96.1283L109.963 15.3195C111.002 14.0881 112.559 13.2942 114.304 13.2942H117.955L102.514 31.9676H113.614C116.195 31.9676 118.28 29.8775 118.28 27.2932V6.87797H110.677"
                        fill="white" />
                    <path
                        d="M87.2352 10.0132L69.0027 31.9757H79.3482L93.1829 15.3276C94.2215 14.0962 95.7794 13.3023 97.5239 13.3023H101.289V6.87797H93.8969C91.2192 6.87797 88.8256 8.09317 87.2433 10.0051"
                        fill="white" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M33.4546 18.5843C37.106 18.7139 39.9054 18.4304 44.9929 17.3205C42.5912 19.1109 40.4734 22.3676 40.2381 24.8547C38.9722 22.9671 36.043 20.0263 33.4546 18.5924M47.5489 32C46.5346 28.7595 45.0416 21.5008 51.468 17.0046C56.0525 13.7884 69.9845 9.98886 77.4414 7.25873L82.8049 0C77.693 2.61671 71.0475 5.30633 65.0024 7.15342C51.5086 11.2851 43.435 13.7316 33.0408 13.9423C25.5596 14.0881 25.4541 10.556 32.7325 4.23696C24.5534 7.40456 13.1206 11.1554 0 14.0962C11.7087 14.4284 20.5937 16.802 25.9328 19.8967C33.5358 24.3038 35.3858 28.9377 36.2459 32H47.5489Z"
                        fill="white" />
                </svg>
            </div>

            <!-- Conteúdo central -->
            <div class="flex flex-col items-center justify-center flex-1 w-full px-6">
                <!-- Ícone (opcional - você pode remover se não quiser) -->
                <!--<div class="mb-6">
                    <svg class="w-20 h-20 text-white opacity-80" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4"></path>
                    </svg>
                </div>-->

                <!-- Título -->
                <h2 class=" text-white text-center mb-4">
                    Disponível apenas para desktop. <br>Versões para mobile e tablet chegam em breve.
                </h2>

                <!-- Mensagem -->
                <p class="text-white text-center text-base opacity-90 mb-8 max-w-sm">
                    Para acessar o catálogo digital da Mizuno, utilize um computador ou tablet com tela maior.
                </p>

                <!-- Informação adicional (opcional) -->
                <div class="bg-white/10 rounded-lg p-4 max-w-sm">
                    <p class="text-white text-sm text-center opacity-80">
                        Para uma melhor experiência, recomendamos o acesso através de dispositivos com tela de pelo
                        menos 1024px de largura.
                    </p>
                </div>
            </div>

            <!-- Rodapé -->
            <div class="text-xs text-white text-center opacity-70 pb-4">
                <p>© {{ date('Y') }} Mizuno</p>
            </div>
        </div>

        <!-- Seção da imagem abaixo -->
        <div class="flex-1">
            <img src="{{ isset($imgLogin) && $imgLogin?->mobile_url ? $imgLogin->mobile_url : asset('images/bg-geral.jpg') }}"
                alt="Corredores" class="w-full h-full object-cover" />
        </div>
    </div>

    <!-- Layout Desktop: Lado a lado -->
    <div class="hidden lg:flex flex-row gap-2 min-h-screen p-2 h-[100vh]">

        <!-- Lado esquerdo (3/4 no desktop) -->
        <div class="lg:w-[60%] 2xl:w-[70%] 3xl:w-[80%] w-full">
            <img src="{{ isset($imgLogin) && $imgLogin?->desktop_url ? $imgLogin->desktop_url : asset('images/bg-geral.jpg') }}"
                alt="Corredores" class="w-full h-full object-cover rounded-xl" />
        </div>

        <!-- Lado direito (formulário) -->
        <div
            class="lg:w-[40%] 2xl:w-[30%] 3xl:w-[20%] w-full bg-[#021489] flex flex-col justify-between items-center p-6 lg:p-12 rounded-xl min-h-full">
            <!-- Logo no topo -->
            <div class="flex justify-center w-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="207" height="32" viewBox="0 0 207 32"
                    fill="none">
                    <path
                        d="M146.825 19.0785C147.377 18.4223 147.694 17.5554 147.694 16.6481V13.2942H130.54V17.0208H140.018L131.717 26.1914C131.165 26.8476 130.792 27.6334 130.792 28.557V31.9676H147.937V28.241H138.525L146.825 19.0785Z"
                        fill="white" />
                    <path
                        d="M198.001 17.0289C199.032 17.0289 199.868 17.8714 199.868 18.9003V26.3777C199.868 27.4066 199.032 28.2491 197.993 28.2491H195.17C194.131 28.2491 193.295 27.4066 193.295 26.3777V18.9003C193.295 17.8714 194.131 17.0289 195.17 17.0289H197.993M189.92 13.3023C187.851 13.3023 186.147 14.9792 186.147 17.037V28.2329C186.147 30.2987 187.851 31.9757 189.92 31.9757H203.276C205.345 31.9757 207 30.3068 207 28.2329V17.037C207 14.9711 205.345 13.3023 203.276 13.3023H189.92Z"
                        fill="white" />
                    <path
                        d="M121.177 13.3023H128.325V28.241C128.325 30.3068 126.67 31.9757 124.601 31.9757H121.177V13.3023Z"
                        fill="white" />
                    <path
                        d="M153.942 31.9757C151.872 31.9757 150.177 30.3068 150.177 28.241V13.3104H157.325V26.3777C157.325 27.4066 158.185 28.2572 159.216 28.2572H161.512C162.543 28.2572 163.362 27.4147 163.362 26.3858V13.3104H179.972C182.041 13.3104 183.704 14.9873 183.704 17.0451V31.9757H176.556V18.8922C176.556 17.8552 175.712 17.0289 174.681 17.0289H170.511V28.2329C170.511 30.2987 168.855 31.9676 166.786 31.9676H153.942"
                        fill="white" />
                    <path
                        d="M110.669 6.87797C107.991 6.87797 105.589 8.09317 104.015 10.0051L85.7828 31.9676H96.1283L109.963 15.3195C111.002 14.0881 112.559 13.2942 114.304 13.2942H117.955L102.514 31.9676H113.614C116.195 31.9676 118.28 29.8775 118.28 27.2932V6.87797H110.677"
                        fill="white" />
                    <path
                        d="M87.2352 10.0132L69.0027 31.9757H79.3482L93.1829 15.3276C94.2215 14.0962 95.7794 13.3023 97.5239 13.3023H101.289V6.87797H93.8969C91.2192 6.87797 88.8256 8.09317 87.2433 10.0051"
                        fill="white" />
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M33.4546 18.5843C37.106 18.7139 39.9054 18.4304 44.9929 17.3205C42.5912 19.1109 40.4734 22.3676 40.2381 24.8547C38.9722 22.9671 36.043 20.0263 33.4546 18.5924M47.5489 32C46.5346 28.7595 45.0416 21.5008 51.468 17.0046C56.0525 13.7884 69.9845 9.98886 77.4414 7.25873L82.8049 0C77.693 2.61671 71.0475 5.30633 65.0024 7.15342C51.5086 11.2851 43.435 13.7316 33.0408 13.9423C25.5596 14.0881 25.4541 10.556 32.7325 4.23696C24.5534 7.40456 13.1206 11.1554 0 14.0962C11.7087 14.4284 20.5937 16.802 25.9328 19.8967C33.5358 24.3038 35.3858 28.9377 36.2459 32H47.5489Z"
                        fill="white" />
                </svg>
            </div>

            <!-- Conteúdo central -->
            <div class="items-center justify-center w-[350px]">
                <!-- Título -->
                <h2 class="text-xl text-white text-center mb-8">Bem-vindo ao catálogo <br>digital da Mizuno.</h2>

                <!-- Formulário -->
                <form class="" method="POST" action="{{ url('/admin/login') }}">
                    @csrf
                    <input name="email" type="email" placeholder="E-mail"
                        class="w-full text-white py-5 mb-2 placeholder-white input-estilizado bg-transparent border-0 focus:ring-0 input-style" />
                    @error('email')
                        <p class="text-[#FC0] text-sm">{{ $message }}</p>
                    @enderror
                    <input name="password" type="password" placeholder="Senha"
                        class="w-full text-white py-5 mb-2 placeholder-white input-estilizado bg-transparent border-0 focus:outline-none focus:ring-0 input-style" />
                    @error('password')
                        <p class="text-[#FC0] text-sm">{{ $message }}</p>
                    @enderror
                    <div class="text-sm text-white text-center m-4 opacity-70 cursor-pointer hover:opacity-100 transition"
                        onclick="openPasswordRecoveryModal()">Esqueceu a senha?</div>

                    <button type="submit"
                        class="w-full border border-white text-white hover:opacity-80 py-2 rounded-full  transition">
                        Entrar
                    </button>
                </form>
            </div>

            <!-- Rodapé -->
            <div class="text-xs text-white text-center opacity-70">
                Precisa de um login? <a href="#" class="text-white underline cursor-pointer"
                    onclick="openAccessRequestModal()">Solicite um acesso</a>
            </div>
        </div>
    </div>

    <x-passwordRecovery-modal />

    <x-accessRequest-modal />


    <script>
        function openPasswordRecoveryModal() {
            document.getElementById('passwordRecoveryModal').classList.remove('hidden');
        }

        function closePasswordRecoveryModal() {
            document.getElementById('passwordRecoveryModal').classList.add('hidden');
        }

        function openAccessRequestModal() {
            document.getElementById('accessRequestModal').classList.remove('hidden');
        }

        function closeAccessRequestModal() {
            document.getElementById('accessRequestModal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('passwordRecoveryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordRecoveryModal();
            }
        });

        document.getElementById('accessRequestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAccessRequestModal();
            }
        });
    </script>
</body>

</html>
