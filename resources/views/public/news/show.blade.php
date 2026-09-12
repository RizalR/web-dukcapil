@extends('layouts.public')
@section('title', $article->title)
@section('meta_description', $article->excerpt)
@section('og_type', 'article')
@section('article_published_time', $article->published_at->toIso8601String())
@if($article->image_path)
    @section('social_image', Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path))
    @section('social_image_alt', $article->image_alt)
@endif
@section('content')
<article class="container-page py-14"><div class="mx-auto max-w-3xl"><a href="{{ route('news.index') }}" class="text-sm font-bold text-ocean">← Kembali ke berita</a><p class="eyebrow mt-8">{{ $article->published_at->translatedFormat('d F Y') }}</p><h1 class="mt-3 text-4xl font-black tracking-tight text-ink sm:text-5xl">{{ $article->title }}</h1><p class="mt-5 text-lg leading-8 text-slate-600">{{ $article->excerpt }}</p><div class="mt-8 border-y border-slate-200 py-4 text-sm">Ditulis oleh <strong class="text-ink">{{ $article->author->name }}</strong></div>@if($article->image_path)<figure class="mt-8"><img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) }}" alt="{{ $article->image_alt }}" class="h-auto w-full rounded-xl" decoding="async">@if($article->image_caption)<figcaption class="mt-3 text-sm leading-6 text-slate-500">{{ $article->image_caption }}</figcaption>@endif</figure>@endif<div class="prose-public mt-9 text-base">@foreach(preg_split('/\R{2,}/', $article->body) as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div>@if($article->source_url)<div class="mt-10 rounded-xl bg-sky-soft p-5 text-sm"><strong class="text-ink">Sumber informasi:</strong> <a class="font-bold text-ocean" href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer">Buka sumber ↗</a></div>@endif</div></article>
@endsection
