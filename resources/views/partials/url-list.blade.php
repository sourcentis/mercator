{{-- Renders a UrlList value: web URLs, Markdown links and network paths --}}
@foreach(\App\Rules\UrlList::entries($value) as $entry)
    @php
        $markdownLink = \App\Rules\UrlList::parseMarkdownLink($entry);
    @endphp
    @if($markdownLink)
        {{-- Markdown link --}}
        <a href="{{ $markdownLink['url'] }}"
           target="_blank"
           rel="noopener noreferrer">{{ $markdownLink['label'] }}</a>
    @elseif(\App\Rules\UrlList::isWebUrl($entry))
        {{-- Standard web URL --}}
        <a href="{{ $entry }}"
           target="_blank"
           rel="noopener noreferrer">{{ $entry }}</a>
    @elseif(\App\Rules\UrlList::isNetworkPath($entry))
        {{-- Windows network path --}}
        <span class="text-nowrap">{{ $entry }}</span>
        <button type="button"
                class="btn btn-link btn-sm p-0 align-baseline copy-path-button"
                data-path="{{ $entry }}"
                title="{{ trans('global.copyPath') }}">
            <i class="bi bi-clipboard"></i>
        </button>
    @else
        {{ $entry }}
    @endif
    @if(!$loop->last), @endif
@endforeach
