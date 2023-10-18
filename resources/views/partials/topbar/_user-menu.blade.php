<!--begin::Menu-->
<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
    <!--begin::Menu item-->
    <div class="menu-item px-3">
        <div class="menu-content d-flex align-items-center px-3">
            <!--begin::Avatar-->
            <div class="symbol symbol-50px me-5">
                <img alt="Logo" src="{{ auth()->user()->avatar == '' ? auth()->user()->avatar_url : asset('storage/images/'.auth()->user()->avatar) }}"/>
            </div>
            <!--end::Avatar-->

            <!--begin::Username-->
            <div class="d-flex flex-column">
                <div class="fw-bolder d-flex align-items-center fs-5">
                    {{ auth()->user()->username }}
                    {{-- <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">Pro</span> --}}
                </div>
                {{-- <span class="badge badge-light-success fw-bolder fs-8 px-2 py-1 ms-2">{{ auth()->user()->user_group_id }}</span> --}}
                <a href="#" class="fw-bold text-muted text-hover-primary fs-7">{{ auth()->user()->email }}</a>
            </div>
            <!--end::Username-->
        </div>
    </div>
    <!--end::Menu item-->

    <!--begin::Menu separator-->
    <div class="separator my-2"></div>
    <!--end::Menu separator-->

    <!--begin::Menu item-->
    <div class="menu-item px-5">
        <a href="{{ theme()->getPageUrl('user.settings-index') }}" class="menu-link px-5">
            {{ __('Pengaturan Akun') }}
        </a>
    </div>
    <!--end::Menu item-->

    <!--begin::Menu separator-->
    <div class="separator my-2"></div>
    <!--end::Menu separator-->

    <!--begin::Menu item-->
    <div class="menu-item px-5">
        <a href="#" data-action="{{ theme()->getPageUrl('logout') }}" data-method="post" data-csrf="{{ csrf_token() }}" data-reload="true" class="button-ajax menu-link px-5">
            {{ __('Keluar') }}
        </a>
    </div>
    <!--end::Menu item-->

    @if (theme()->isDarkModeEnabled())
        <!--begin::Menu separator-->
        {{-- <div class="separator my-2"></div> --}}
        <!--end::Menu separator-->

        <!--begin::Menu item-->
        {{-- <div class="menu-item px-5">
            <div class="menu-content px-5">
                <label class="form-check form-switch form-check-custom form-check-solid pulse pulse-success" for="kt_user_menu_dark_mode_toggle">
                    <input class="form-check-input w-30px h-20px" type="checkbox" value="1" name="skin" id="kt_user_menu_dark_mode_toggle" {{ theme()->isDarkMode() ? 'checked' : '' }} data-kt-url="{{ theme()->getPageUrl('index', '', theme()->isDarkMode() ? '' : 'dark') }}"/>
                    <span class="pulse-ring ms-n1"></span>

                    <span class="form-check-label text-gray-600 fs-7">
                        {{ __('Mode Gelap') }}
                    </span>
                </label>
            </div>
        </div> --}}
        <!--end::Menu item-->
    @endif
</div>
<!--end::Menu-->
