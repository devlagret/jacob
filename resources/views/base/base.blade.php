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
    {{-- <link rel="shortcut icon" href="{{ asset(theme()->getDemo() . '/' .theme()->getOption('assets', 'favicon')) }}"/> --}}
    <link rel="shortcut icon" href="{{ asset('img/logo/logo-v1-250x250.png')}}"/>
    <link rel="manifest" href="{{asset('manifest.json')}}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo/logo-v1-250x250.png')}}" />
    <link rel="stylesheet" type="text/css" href="{{ asset(theme()->getDemo().'/plugins/custom/datatables/datatables.bundle.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

{{-- begin::Javascript --}}
@if (theme()->hasOption('assets', 'js'))
    {{-- begin::Global Javascript Bundle(used by all pages) --}}
    @foreach (theme()->getOption('assets', 'js') as $file)
        <script src="{{ asset(theme()->getDemo() . '/' .$file) }}"></script>
    @endforeach
    {{-- end::Global Javascript Bundle --}}
@endif

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

    .show-border tr{
        border-width: 2px !important;
        border-style : solid !important;
    }
    
    input[readonly], textarea[readonly] {
        cursor: not-allowed;
    }

    input[readonly], textarea[readonly] {
        background-color : #e3e4e4 !important; 
    }
    /* #toast-container > .toast {
        background-image: none !important;
    } */
    .select2-selection__rendered{
        color: #5E6278 !important;
        font-weight: 500;
    }
    .shadow{
        box-shadow: 3px 2px 3px 1px rgba(0,0,0,0.2),0 2px 10px 0 rgba(0,0,0,0.19) !important;
    }
    .select2{
        border: 1px solid #B5B5C3 ;
        border-radius: 0.475rem;
    }
</style>
<script>
    @if (Session::has('pesan'))
        toastr.options = {
            positionClass: 'toast-top-center',
        };
        // toastr.{{Session::get('alert')}}("{{Session::get('pesan')}}").css("width","500px");
        toastr.{{Session::get('alert')}}("{{Session::get('pesan')}}");
    @endif
    @if ($errors->any())
    toastr.options = {
        "closeButton": true,
        "positionClass": "toast-top-center",
        "timeOut": "10000",
        "progressBar": true
        };
        @foreach ($errors->all() as $error)
        toastr.error("{{$error}}");
        @endforeach
    @endif

    function toRp(number) {
        var number = number.toString(), 
        rupiah = number.split('.')[0], 
        cents = (number.split('.')[1] || '') +'00';
        rupiah = rupiah.split('').reverse().join('')
            .replace(/(\d{3}(?!$))/g, '$1,')
            .split('').reverse().join('');
        return rupiah + '.' + cents.slice(0, 2);
    }

    var prevScrollpos = window.pageYOffset;
    if (window.innerHeight == document.body.scrollHeight) {
        if (document.getElementsByTagName("BODY")[0].hasAttribute('data-kt-scrollbottom') === true) {
            document.getElementsByTagName("BODY")[0].removeAttribute('data-kt-scrollbottom');
        }
    } else { 
        if (document.getElementsByTagName("BODY")[0].hasAttribute('data-kt-scrollbottom') === false) {
            document.getElementsByTagName("BODY")[0].setAttribute('data-kt-scrollbottom', 'on');
        }
    }

    window.onscroll = function() {
        if (prevScrollpos = window.pageYOffset) {
            if (document.getElementsByTagName("BODY")[0].hasAttribute('data-kt-scrollbottom') === true) {
                document.getElementsByTagName("BODY")[0].removeAttribute('data-kt-scrollbottom');
            }
        } else { 
            if (document.getElementsByTagName("BODY")[0].hasAttribute('data-kt-scrollbottom') === false) {
                document.getElementsByTagName("BODY")[0].setAttribute('data-kt-scrollbottom', 'on');
            }
        }
    }

    $(document).ready(function(){
        $('#kt_scrollbottom').click(function(){
            $('html, body').animate({
                scrollTop: document.body.scrollHeight
            }, 900, 'linear');
        })
        $('.form-select').click(function(){
            $('.select2-search__field').each(function (
                key,
                value,
            ){
                value.focus();
            })
        })
        $(document.querySelectorAll('[type=reset]')).click(function(){
            // location.reload();
            // $("#account_type_id").empty();
            // $("#account_type_id").select2("val", "");
            // if ($('#account_type_id').hasClass("select2-hidden-accessible")) {
            //     console.log('ada');
            // }else{
            //     console.log('tidak ada');
            // }
            // document.getElementsByClassName("select2-selection__clear").click();
            // var el = document.getElementsByClassName('select2-selection__clear');
            // console.log(el);
            // console.log(document.getElementById('account_type_id').value);
            // el[0].click();
            // console.log(document.getElementById('account_type_id').value);
            // $('.select2-selection__clear').each(function (
            //     key,
            //     value,
            // ){
            //     console.log(document.getElementById('account_type_id').value);
            //     value.click();
            // })
        })
    }); 
</script>

<script type="text/javascript" charset="utf8" src="{{ asset(theme()->getDemo().'/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@yield('scripts')
@yield('bladeScripts')
</body>
{{-- end::Body --}}
</html>
