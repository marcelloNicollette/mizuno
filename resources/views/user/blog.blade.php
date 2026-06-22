<x-layout-user title="Mizuno - Blog">
    <style>
        .options {
            width: 200px;
            max-width: none;
        }

        .check-icon {
            color: #000;
            font-weight: bold;
            font-size: 16px;
            width: 16px;
            text-align: center;
        }

        .option {
            padding: 1px 20px;
            border-bottom: 0;
        }

        .option.selected {
            padding: 1px 0px;
        }

        .blog-scroll {
            height: calc(100vh - 85px);
        }

        .blog-shell {
            max-width: 1290px;
        }

        .blog-kicker {
            font-size: 12px;
            letter-spacing: 0.08em;
            color: rgba(0, 0, 0, 0.45);
            text-transform: uppercase;
        }

        .blog-page-title,
        .blog-title {
            color: #021489;
            leading-trim: both;
            text-edge: cap;
            font-family: 'AktivGrotesk', Arial, sans-serif;
            font-size: 40px;
            font-style: normal;
            font-weight: 600;
            line-height: 40px;
            /* 100% */
            letter-spacing: -1.6px;
            /* 87.273% */
        }

        .blog-page-title {
            /*font-size: clamp(3rem, 5vw, 5.2rem);
            line-height: 0.9;*/
        }

        .blog-card+.blog-card {
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }

        .blog-image {
            min-height: 320px;
            aspect-ratio: 1.34 / 1;
        }

        .blog-meta {
            font-family: Roboto;
            font-size: 12px;
            color: rgba(0, 0, 0, 0.5);
            font-style: normal;
            font-weight: 400;
            line-height: 16px;
        }

        .blog-title {
            /*font-size: clamp(2.6rem, 4.8vw, 5.4rem);
            line-height: 0.9;
            max-width: 660px;*/
        }

        .blog-excerpt {
            font-family: Roboto;
            font-size: 12px;
            font-style: normal;
            font-weight: 400;
            line-height: 16px;
            color: rgba(0, 0, 0, 0.5);

        }

        .blog-link {
            color: #000;
            font-family: Roboto;
            font-size: 12px;
            font-style: normal;
            font-weight: 400;
            line-height: 16px;
            /* 133.333% */
            text-decoration-line: underline;
            text-decoration-style: solid;
            text-decoration-skip-ink: auto;
            text-decoration-thickness: auto;
            text-underline-offset: auto;
            text-underline-position: from-font;
        }

        .blog-link:hover {
            opacity: 0.68;
        }

        @media (max-width: 768px) {
            .blog-scroll {
                height: calc(100vh - 85px);
            }

            .blog-shell {
                padding-left: 14px;
                padding-right: 14px;
            }

            .blog-page-title {
                font-size: 2.45rem;
                line-height: 0.92;
            }

            .blog-image {
                min-height: 184px;
                aspect-ratio: 1.28 / 1;
            }

            .blog-title {
                font-size: 2.45rem;
                line-height: 0.9;
            }

            .blog-excerpt {
                font-size: 13px;
                line-height: 1.5;
            }

            .blog-link {
                font-size: 14px;
            }

            .blog-card {
                padding-top: 20px;
                padding-bottom: 20px;
            }

            .blog-copy {
                padding-top: 0.25rem;
            }
        }
    </style>

    <main class="lg:flex flex-1 blog-page">
        <x-sidebar activeItem="blog" />

        <section class="flex-1 flex flex-col md:pr-0 md:pb-0 overflow-hidden">

            @php

                $currentUrl = request()->path();
                $currentUrlComplete = request()->path();
                $currentSlug = '';

                if (strpos($currentUrl, 'user') === 0) {
                    $parts = explode('/', $currentUrl);
                    //dd($parts);
                    if (count($parts) > 1) {
                        $currentSlug = $parts[1];
                    }
                }

            @endphp
            <!-- Filtros superiores -->
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-4 pb-3 pr-4 bg-[#F1F1F1]">
                <!-- Esquerda: fitro ano -->
                <div class="flex gap-2">

                    <div class="select-container">
                        <div class="select-button p-5" id="selectButton">
                            <span id="selectedText">Ano</span>
                            <div class="" id="arrow">
                                <div class="pt-1" id="arrow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="8"
                                        viewBox="0 0 12 8" fill="none">
                                        <path d="M1 1L5.94975 5.94975L10.8995 1" stroke="black" stroke-width="1.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </div>
                            </div>
                        </div>


                        <div class="options  p-5" id="options">

                            @foreach ($anos as $ano)
                                <div class="option gap-[10px] text-[18px] font-normal opacity-50"
                                    data-value="{{ $ano }}">
                                    <span class="check-icon" style="display: none;"><svg
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
                                            <path
                                                d="M530.8 134.1C545.1 144.5 548.3 164.5 537.9 178.8L281.9 530.8C276.4 538.4 267.9 543.1 258.5 543.9C249.1 544.7 240 541.2 233.4 534.6L105.4 406.6C92.9 394.1 92.9 373.8 105.4 361.3C117.9 348.8 138.2 348.8 150.7 361.3L252.2 462.8L486.2 141.1C496.6 126.8 516.6 123.6 530.9 134z" />
                                        </svg></span>
                                    <span class="option-content text-black">
                                        {{ $ano }}
                                    </span>
                                    <span class="x-icon" style="display: none;">×</span>
                                </div>
                            @endforeach
                            <div class="option" data-categoria-id="" data-value="">
                                <span class="check-icon" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 640 640">
                                        <path
                                            d="M530.8 134.1C545.1 144.5 548.3 164.5 537.9 178.8L281.9 530.8C276.4 538.4 267.9 543.1 258.5 543.9C249.1 544.7 240 541.2 233.4 534.6L105.4 406.6C92.9 394.1 92.9 373.8 105.4 361.3C117.9 348.8 138.2 348.8 150.7 361.3L252.2 462.8L486.2 141.1C496.6 126.8 516.6 123.6 530.9 134z" />
                                    </svg></span>
                                <span class="text-[16px] text-black font-normal option-content">Todas</span>
                                <span class="x-icon" style="display: none;">×</span>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Direita: Busca -->
                <div class="flex flex-wrap gap-2 items-end justify-end mt-6">
                    <div class="flex items-center border-b border-b-black px-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-black ml-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM8 14a6 6 0 100-12 6 6 0 000 12z"
                                clip-rule="evenodd" />
                        </svg>
                        <input type="text" placeholder="Buscar"
                            class="input-estilizado blog-search-input bg-transparent border-0 focus:outline-none focus:ring-0 p-1" />
                    </div>


                </div>
            </div>

            <div class="blog-scroll bg-[#E6E6E6] overflow-y-auto">
                <div class="blog-shell p-[50px]">

                    @forelse ($blogs as $blog)
                        @php
                            $coverImage = $blog->thumb_image ?: $blog->cover_image;
                            $excerpt = trim(strip_tags($blog->description ?: $blog->subtitle ?: $blog->content));
                            $blogYear = optional($blog->material_date)->format('Y');
                            $searchText = mb_strtolower(
                                trim($blog->title . ' ' . $excerpt . ' ' . ($blog->subtitle ?? '')),
                            );
                        @endphp

                        <article class="blog-card blog-item" data-year="{{ $blogYear }}"
                            data-search="{{ $searchText }}">
                            <div
                                class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.95fr)] gap-5 md:gap-10 items-center">
                                <a href="{{ route('user.blog.show', ['slug' => $slug, 'blog' => $blog->id]) }}"
                                    class="blog-image block rounded-[10px] overflow-hidden bg-[#D9D9D9]">
                                    @if ($coverImage)
                                        <img src="{{ asset('storage/' . $coverImage) }}" alt="{{ $blog->title }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div
                                            class="w-full h-full min-h-[184px] md:min-h-[320px] flex items-center justify-center text-sm text-black/45">
                                            Sem imagem
                                        </div>
                                    @endif
                                </a>

                                <div class="blog-copy pt-1 md:pt-2">
                                    <p class="blog-meta mb-4 md:mb-5">
                                        {{ \App\Http\Controllers\User\BlogController::materialDateLabel($blog) }}
                                    </p>

                                    <h2 class="blog-title">
                                        {{ $blog->title }}
                                    </h2>

                                    @if ($excerpt !== '')
                                        <p class="blog-excerpt mt-4 md:mt-5">
                                            {{ \Illuminate\Support\Str::limit($excerpt, 240) }}
                                        </p>
                                    @endif

                                    <a href="{{ route('user.blog.show', ['slug' => $slug, 'blog' => $blog->id]) }}"
                                        class="blog-link inline-flex items-center gap-2 mt-5 md:mt-7 transition-opacity">
                                        Leia mais
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="bg-white/60 rounded-[10px] px-6 py-10 text-center text-black/60 blog-empty-state">
                            Nenhuma materia publicada no momento.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectContainer = document.querySelector('.select-container');
                const selectButton = selectContainer ? selectContainer.querySelector('.select-button') : null;
                const selectOptions = selectContainer ? selectContainer.querySelector('.options') : null;
                const selectArrow = selectContainer ? selectContainer.querySelector('#arrow') : null;
                const searchInput = document.querySelector('.blog-search-input');
                const blogItems = Array.from(document.querySelectorAll('.blog-item'));
                const emptyState = document.querySelector('.blog-empty-state');
                let selectedYear = '';

                if (!selectContainer || !selectButton || !selectOptions || !selectArrow || !searchInput) {
                    return;
                }

                function setButtonLabel(value) {
                    const label = value && value !== '' ? value : 'Todas';
                    selectButton.querySelector('span').innerHTML =
                        "Ano: <span style='font-size:18px; color:#7A7A7A;'>" + label + "</span>";
                }

                function toggleDropdown() {
                    const isOpen = selectOptions.classList.contains('show');
                    if (isOpen) {
                        selectOptions.classList.remove('show');
                        selectArrow.classList.remove('up');
                    } else {
                        selectOptions.classList.add('show');
                        selectArrow.classList.add('up');
                    }
                }

                function closeDropdown() {
                    selectOptions.classList.remove('show');
                    selectArrow.classList.remove('up');
                }

                function applyFilters() {
                    const termo = (searchInput.value || '').trim().toLowerCase();
                    let visibleCount = 0;

                    blogItems.forEach(item => {
                        const itemYear = item.getAttribute('data-year') || '';
                        const itemSearch = (item.getAttribute('data-search') || '').toLowerCase();
                        const matchesYear = selectedYear === '' || itemYear === selectedYear;
                        const matchesTermo = termo === '' || itemSearch.includes(termo);
                        const isVisible = matchesYear && matchesTermo;

                        item.style.display = isVisible ? 'block' : 'none';

                        if (isVisible) {
                            visibleCount += 1;
                        }
                    });

                    if (emptyState) {
                        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                        if (visibleCount === 0) {
                            emptyState.textContent = 'Nenhuma materia encontrada para os filtros aplicados.';
                        } else {
                            emptyState.textContent = 'Nenhuma materia publicada no momento.';
                        }
                    }
                }

                setButtonLabel('');

                selectButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown();
                });

                selectOptions.addEventListener('click', function(e) {
                    if (e.target.classList.contains('x-icon')) {
                        e.stopPropagation();
                        selectedYear = '';

                        selectOptions.querySelectorAll('.option').forEach(opt => {
                            opt.classList.remove('selected');
                            opt.classList.add('opacity-50');
                            opt.querySelector('.check-icon').style.display = 'none';
                            opt.querySelector('.x-icon').style.display = 'none';
                        });

                        setButtonLabel('');
                        closeDropdown();
                        applyFilters();
                        return;
                    }

                    let option = e.target;
                    if (!option.classList.contains('option')) {
                        option = option.closest('.option');
                    }

                    if (option && option.classList.contains('option')) {
                        e.stopPropagation();

                        selectOptions.querySelectorAll('.option').forEach(opt => {
                            opt.classList.remove('selected');
                            opt.classList.add('opacity-50');
                            opt.querySelector('.check-icon').style.display = 'none';
                            opt.querySelector('.x-icon').style.display = 'none';
                        });

                        option.classList.add('selected');
                        option.classList.remove('opacity-50');
                        option.querySelector('.check-icon').style.display = 'inline-table';
                        option.querySelector('.x-icon').style.display = 'inline-table';

                        selectedYear = option.getAttribute('data-value') || '';
                        setButtonLabel(selectedYear);
                        closeDropdown();
                        applyFilters();
                    }
                });

                document.addEventListener('click', function(e) {
                    if (!selectContainer.contains(e.target)) {
                        closeDropdown();
                    }
                });

                searchInput.addEventListener('input', applyFilters);
            });
        </script>
    @endpush
</x-layout-user>
