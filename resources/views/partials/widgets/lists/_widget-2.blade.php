<!--begin::List Widget 2-->
<?php
    use App\Http\Controllers\SampleDataController;
?>

<div class="card {{ $class }}">
    <!--begin::Header-->
    <div class="card-header border-0">
        <h3 class="card-title fw-bolder text-dark">User</h3>

    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body pt-2">
        @foreach(SampleDataController::getDataUser() as $index => $row)
            <!--begin::Item-->
            <div class="d-flex align-items-center mb-3">
                <!--begin::Avatar-->
                <div class="symbol symbol-50px me-5">
                    <img src="{{ $row['avatar'] == '' ? auth()->user()->avatar_url : asset('storage/images/'.$row['avatar']) }}" class="" alt=""/>
                </div>
                <!--end::Avatar-->

                <!--begin::Text-->
                <div class="flex-grow-1">
                    <div class="text-dark fw-bolder text-hover-primary fs-6">{{ $row['username'] }}</div>

                    <span class="text-muted d-block fw-bold">{{ $row['user_group_name'] }}</span>
                </div>
                <div class="flex-grow-2">
                    <?php
                        if ($row['isOnline'] == true) {
                            echo "<span class='badge badge-light-success fs-8 fw-bolder'>Online</span>";
                        } else {
                            echo "<span class='badge badge-light-danger fs-8 fw-bolder'>Offline</span>";
                        }
                        ?>
                    
                    
                </div>
                <!--end::Text-->
            </div>
            <!--end::Item-->
        @endforeach
    </div>
    <!--end::Body-->
</div>
<!--end::List Widget 2-->
