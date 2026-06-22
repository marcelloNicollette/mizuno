<x-layout-user title="Mizuno - Blog">
    <style>
        .blog-detail-page {}

        .blog-detail-scroll {
            height: calc(100vh - 85px);
        }

        .blog-detail-shell {
            max-width: 100%;
        }

        .blog-detail-back {
            font-size: 14px;
            color: rgba(0, 0, 0, 0.55);
        }

        .blog-hero-panel {}

        .blog-hero-copy {
            padding: 40px 20px;
        }

        .blog-hero-meta {
            font-size: 12px;
            color: rgba(0, 0, 0, 0.38);
        }

        .blog-hero-title,
        .blog-content h1,
        .blog-content h2,
        .blog-content h3,
        .blog-content h4,
        .blog-related-title {

            color: #021489;
            leading-trim: both;
            text-edge: cap;
            font-family: 'AktivGrotesk', Arial, sans-serif;

            /* 100% */
            letter-spacing: -1.6px;
        }

        .blog-hero-title {
            font-size: 40px;
            font-style: normal;
            font-weight: 600;
            line-height: 40px;
        }

        .blog-hero-subtitle {
            font-size: 12px;
            line-height: 1.1;
            color: rgba(0, 0, 0, 0.72);
        }

        .blog-hero-description {
            font-size: 12px;
            line-height: 1.55;
            color: rgba(0, 0, 0, 0.46);
        }

        .blog-hero-author {
            font-size: 11px;
            letter-spacing: 0.18em;
            color: rgba(0, 0, 0, 0.34);
            text-transform: uppercase;
        }

        .blog-hero-image {
            min-height: 420px;
        }

        .blog-body {
            max-width: 50%;
            padding-left: 20px;
        }

        .blog-content {
            color: rgba(0, 0, 0, 0.72);
            font-size: 15px;
            line-height: 1.75;
        }

        .blog-content>*+* {
            margin-top: 1.5rem;
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3,
        .blog-content h4 {
            color: #000;
            font-size: clamp(1.5rem, 3vw, 2.8rem);
            line-height: 0.98;
            letter-spacing: -0.04em;
            margin-top: 2rem;
            margin-bottom: 0.8rem;
        }

        .blog-content p,
        .blog-content li,
        .blog-content blockquote {
            color: rgba(0, 0, 0, 0.62);
        }

        .blog-content a {
            color: #000;
            text-decoration: underline;
        }

        .blog-content img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            display: block;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .blog-content figure {
            margin: 0;
        }

        .blog-content ul,
        .blog-content ol {
            padding-left: 1.25rem;
        }

        .blog-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .blog-content td,
        .blog-content th {
            border: 1px solid rgba(0, 0, 0, 0.12);
            padding: 0.75rem;
        }

        .blog-related-label {
            font-size: 12px;
            letter-spacing: 0.08em;
            color: rgba(0, 0, 0, 0.45);
            text-transform: uppercase;
        }

        .blog-related-title {
            letter-spacing: -0.03em;
            font-size: 2rem;
            line-height: 0.92;
        }

        .blog-related-text {
            font-size: 13px;
            line-height: 1.45;
            color: rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 1024px) {
            .blog-hero-copy {
                padding: 28px 24px 24px;
            }

            .blog-hero-image {
                min-height: 290px;
            }
        }

        @media (max-width: 768px) {
            .blog-detail-shell {
                padding-left: 14px;
                padding-right: 14px;
            }

            .blog-hero-panel {
                border-radius: 4px;
            }

            .blog-hero-grid {
                grid-template-columns: 50% 50%;
                align-items: start;
            }

            .blog-hero-image {
                min-height: 150px;
                height: 100%;
            }

            .blog-hero-copy {
                padding: 14px 14px 16px;
            }

            .blog-hero-title {
                font-size: 2rem;
                line-height: 0.93;
            }

            .blog-hero-subtitle {
                font-size: 13px;
                line-height: 1.15;
                margin-top: 10px;
            }

            .blog-hero-description {
                font-size: 12px;
                line-height: 1.42;
                margin-top: 10px;
            }

            .blog-body {
                max-width: 100%;
                padding-top: 22px;
                padding-bottom: 28px;
            }

            .blog-content {
                font-size: 12px;
                line-height: 1.58;
            }

            .blog-content h1,
            .blog-content h2,
            .blog-content h3,
            .blog-content h4 {
                font-size: 1.65rem;
                line-height: 0.98;
                margin-top: 1.5rem;
            }

            .blog-content img {
                border-radius: 6px;
                margin-top: 1rem;
                margin-bottom: 1rem;
            }

            .blog-related-title {
                font-size: 1.5rem;
            }
        }
    </style>

    <main class="lg:flex flex-1 blog-detail-page">

        <section class="flex-1 overflow-hidden">
            <div class="blog-detail-scroll overflow-y-auto">
                <article class="blog-detail-shell">

                    <div class="blog-hero-panel px-[20px] overflow-hidden">
                        <div class="blog-hero-grid grid grid-cols-1 lg:grid-cols-2 gap-0">
                            <div class="blog-hero-image min-h-[260px] md:min-h-[420px]">
                                @php
                                    $heroImage = $blog->cover_image ?: $blog->thumb_image;
                                @endphp

                                @if ($heroImage)
                                    <img src="{{ asset('storage/' . $heroImage) }}" alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full min-h-[260px] md:min-h-[420px] flex items-center justify-center text-sm text-black/45">
                                        Sem imagem
                                    </div>
                                @endif
                            </div>

                            <div class="blog-hero-copy self-center">
                                <p class="blog-hero-meta mb-3 md:mb-4">
                                    {{ \App\Http\Controllers\User\BlogController::materialDateLabel($blog) }}
                                </p>

                                <h1 class="blog-hero-title">
                                    {{ $blog->title }}
                                </h1>

                                @if ($blog->subtitle)
                                    <!--<p class="blog-hero-subtitle mt-4">
                                        {{ $blog->subtitle }}
                                    </p>-->
                                @endif

                                @if ($blog->description)
                                    <div class="blog-hero-description mt-5 md:mt-6">
                                        {{ strip_tags($blog->description) }}
                                    </div>
                                @endif

                                @if ($blog->author)
                                    <p class="blog-hero-author mt-5 md:mt-6">
                                        {{ $blog->author }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="blog-body ml-auto py-8 md:py-12">
                        @if ($blog->content)
                            <div class="blog-content">
                                {!! $blog->content !!}
                            </div>
                        @elseif ($blog->description)
                            <div class="blog-content">
                                <p>{{ strip_tags($blog->description) }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($relatedBlogs->isNotEmpty())
                        <div class="border-t border-black/10 pt-8 pb-4">
                            <p class="blog-related-label mb-5">Leia tambem</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                                @foreach ($relatedBlogs as $relatedBlog)
                                    @php
                                        $relatedImage = $relatedBlog->thumb_image ?: $relatedBlog->cover_image;
                                        $relatedExcerpt = trim(
                                            strip_tags(
                                                $relatedBlog->description ?:
                                                $relatedBlog->subtitle ?:
                                                $relatedBlog->content,
                                            ),
                                        );
                                    @endphp

                                    <a href="{{ route('user.blog.show', ['slug' => $slug, 'blog' => $relatedBlog->id]) }}"
                                        class="group rounded-[10px] overflow-hidden bg-white/60 hover:bg-white transition-colors">
                                        <div class="bg-[#D9D9D9] aspect-[4/3]">
                                            @if ($relatedImage)
                                                <img src="{{ asset('storage/' . $relatedImage) }}"
                                                    alt="{{ $relatedBlog->title }}"
                                                    class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform">
                                            @endif
                                        </div>

                                        <div class="p-4">
                                            <p class="text-xs text-black/40 mb-2">
                                                {{ \App\Http\Controllers\User\BlogController::materialDateLabel($relatedBlog) }}
                                            </p>
                                            <h2 class="blog-related-title">
                                                {{ \Illuminate\Support\Str::limit($relatedBlog->title, 58) }}
                                            </h2>
                                            @if ($relatedExcerpt !== '')
                                                <p class="blog-related-text mt-3">
                                                    {{ \Illuminate\Support\Str::limit($relatedExcerpt, 90) }}
                                                </p>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            </div>
        </section>
    </main>
</x-layout-user>
