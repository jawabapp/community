<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
@stack('styles')

    <!-- Styles -->
    <link href="{{ asset(mix('app.css', 'vendor/community')) }}" rel="stylesheet" type="text/css">

</head>
<body>

<div id="community">
    <div class="container-fluid mb-5">
        <div class="d-flex align-items-center py-4 header">

            <a href="/">
                <h4 class="mb-0 ml-3"><strong>JawabApp</strong> Community</h4>
            </a>

        </div>

        <div class="row mt-4">
            <div class="col-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a active-class="active" href="{{ route('community.posts.index') }}" class="nav-link  d-flex align-items-center pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M3 6c0-1.1.9-2 2-2h8l4-4h2v16h-2l-4-4H5a2 2 0 0 1-2-2H1V6h2zm8 9v5H8l-1.67-5H5v-2h8v2h-2z"></path>
                            </svg>
                            <span>
                                {{ __('Posts') }}
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a active-class="active" href="{{ route('community.tag-groups.index') }}" class="nav-link  d-flex align-items-center pt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M3 6c0-1.1.9-2 2-2h8l4-4h2v16h-2l-4-4H5a2 2 0 0 1-2-2H1V6h2zm8 9v5H8l-1.67-5H5v-2h8v2h-2z"></path>
                            </svg>
                            <span>
                                {{ __('Tag Groups') }}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-10">
                @yield('content')
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset(mix('app.js', 'vendor/community')) }}"></script>

@stack('scripts')
</body>
</html>
