@php $phrases = config("review.phrases.{$field}", []); @endphp
@if ($phrases)
    <div class="mt-2 flex flex-wrap gap-2" data-phrases-for="{{ $field }}" aria-label="Predpripravené vety">
        @foreach ($phrases as $phrase)
            <button type="button" data-phrase="{{ $phrase }}" title="Vložiť vetu do textu" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs text-slate-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-800">+ {{ \Illuminate\Support\Str::limit($phrase, 48) }}</button>
        @endforeach
    </div>
@endif
