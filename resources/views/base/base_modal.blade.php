<!DOCTYPE html>
{{--
Product Name: {{ theme()->getOption('product', 'description') }}
Author: KeenThemes
Purchase: {{ theme()->getOption('product', 'purchase') }}
Website: http://www.keenthemes.com/
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: {{ theme()->getOption('product', 'license') }}
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    {!! theme()->printHtmlAttributes('html') !!} {{ theme()->printHtmlClasses('html') }}>
{{-- begin::Head --}}
<head>
    <meta charset="utf-8"/>
    <title>{{ ucfirst(theme()->getOption('meta', 'title')) }}</title>
    <meta name="description" content="{{ ucfirst(theme()->getOption('meta', 'description')) }}"/>
    <meta name="keywords" content="{{ theme()->getOption('meta', 'keywords') }}"/>
    <link rel="canonical" href="{{ ucfirst(theme()->getOption('meta', 'canonical')) }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="shortcut icon" href="{{ asset(theme()->getDemo() . '/' .theme()->getOption('assets', 'favicon')) }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset(theme()->getDemo().'/plugins/custom/datatables/datatables.bundle.css') }}">

    {{-- begin::Fonts --}}
    {{ theme()->includeFonts() }}
    {{-- end::Fonts --}}

    @if (theme()->hasOption('page', 'assets/vendors/css'))
        {{-- begin::Page Vendor Stylesheets(used by this page) --}}
        @foreach (theme()->getOption('page', 'assets/vendors/css') as $file)
            <link href="{{ assetCustom($file) }}" rel="stylesheet" type="text/css"/>
        @endforeach
        {{-- end::Page Vendor Stylesheets --}}
    @endif

    @if (theme()->hasOption('page', 'assets/custom/css'))
        {{-- begin::Page Custom Stylesheets(used by this page) --}}
        @foreach (theme()->getOption('page', 'assets/custom/css') as $file)
            <link href="{{ assetCustom($file) }}" rel="stylesheet" type="text/css"/>
        @endforeach
        {{-- end::Page Custom Stylesheets --}}
    @endif

    @if (theme()->hasOption('assets', 'css'))
        {{-- begin::Global Stylesheets Bundle(used by all pages) --}}
        @foreach (theme()->getOption('assets', 'css') as $file)
            <link href="{{ assetCustom($file) }}" rel="stylesheet" type="text/css"/>
        @endforeach
        {{-- end::Global Stylesheets Bundle --}}
    @endif

    @if (theme()->getViewMode() === 'preview')
        {{ theme()->getView('partials/trackers/_ga-general') }}
        {{ theme()->getView('partials/trackers/_ga-tag-manager-for-head') }}
    @endif

    @yield('styles')

</head>
{{-- end::Head --}}

{{-- begin::Body --}}
<body {!! theme()->printHtmlAttributes('body') !!} {!! theme()->printHtmlClasses('body') !!} {!! theme()->printCssVariables('body') !!}>

@if (theme()->getOption('layout', 'loader/display') === true)
    {{ theme()->getView('layout/_loader') }}
@endif

@yield('content')   

@if (theme()->hasOption('page', 'assets/vendors/js'))
    {{-- begin::Page Vendors Javascript(used by this page) --}}
    @foreach (theme()->getOption('page', 'assets/vendors/js') as $file)
        <script src="{{ asset(theme()->getDemo() . '/' .$file) }}"></script>
    @endforeach
    {{-- end::Page Vendors Javascript --}}
@endif

@if (theme()->hasOption('page', 'assets/custom/js'))
    {{-- begin::Page Custom Javascript(used by this page) --}}
    @foreach (theme()->getOption('page', 'assets/custom/js') as $file)
        <script src="{{ asset(theme()->getDemo() . '/' .$file) }}"></script>
    @endforeach
    {{-- end::Page Custom Javascript --}}
@endif
{{-- end::Javascript --}}

@if (theme()->getViewMode() === 'preview')
    {{ theme()->getView('partials/trackers/_ga-tag-manager-for-body') }}
@endif

<style>
    .toast{
        opacity: 1 !important;
        width: 50%;
    }
    /* #toast-container > .toast {
        background-image: none !important;
    } */
</style>
<script>
    toastr.options = {
        positionClass: 'toast-top-center',
    };
    @if (Session::has('pesan'))
        // toastr.{{Session::get('alert')}}("{{Session::get('pesan')}}").css("width","500px");
        toastr.{{Session::get('alert')}}("{{Session::get('pesan')}}");
    @endif
    
    $(".date").daterangepicker({
        autoApply: true,
        singleDatePicker: true,
        showDropdowns: true,
        minYear: 1901,
        maxYear: parseInt(moment().format("YYYY"),12),
        locale: {
            format: 'DD-MM-YYYY',
        }
    });

    function toRp(number) {
        var number = number.toString(), 
        rupiah = number.split('.')[0], 
        cents = (number.split('.')[1] || '') +'00';
        rupiah = rupiah.split('').reverse().join('')
            .replace(/(\d{3}(?!$))/g, '$1,')
            .split('').reverse().join('');
        return rupiah + '.' + cents.slice(0, 2);
    }
</script>

<script type="text/javascript" charset="utf8" src="{{ asset(theme()->getDemo().'/plugins/custom/datatables/datatables.bundle.js') }}"></script>

@yield('scripts')
</body>
{{-- end::Body --}}
</html>
