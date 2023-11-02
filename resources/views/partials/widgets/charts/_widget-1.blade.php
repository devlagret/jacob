@inject('data','App\Http\Controllers\SampleDataController' )

@section('scripts')
    <script>
        var element = document.getElementById('kt_apexcharts_1');

        var height = parseInt(KTUtil.css(element, 'height'));
        var labelColor = KTUtil.getCssVariableValue('--bs-gray-500');
        var borderColor = KTUtil.getCssVariableValue('--bs-gray-200');
        var baseColor = KTUtil.getCssVariableValue('--bs-primary');
        var secondaryColor = KTUtil.getCssVariableValue('--bs-gray-300');

        var options = {
            series: [
                {
                    name: 'Outstanding',
                    data: [
                        <?php foreach ($data->getDataGrafikMonth() as $row) { ?>
                            <?php echo $row['expenses'].',' ?>
                        <?php } ?>
                    ],
                },
                {
                    name: 'Pencairan',
                    data: [
                    <?php foreach ($data->getDataGrafikMonth() as $row) { ?>
                            <?php echo $row['income'].',' ?>
                        <?php } ?>
                    ],
                },
            ],
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: height,
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: ['70%'],
                    borderRadius: 4
                },
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [
                    <?php foreach ($data->getDataGrafikMonth() as $row) { ?>
                            <?php echo $row['day'].',' ?>
                    <?php } ?>
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '10px'
                    }
                },
                tickPlacement: 'on'
            },
            yaxis: {
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '10px'
                    },
                    formatter: function (val) {
                        return toRp(val)
                    }
                }
            },
            fill: {
                opacity: 1
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return 'Rp. ' + toRp(val)
                    }
                }
            },
            colors: [baseColor, secondaryColor],
            grid: {
                borderColor: borderColor,
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };
        var chart = new ApexCharts(element, options);
        chart.render();

        var element2 = document.getElementById('kt_apexcharts_2');

        var height2 = parseInt(KTUtil.css(element2, 'height'));
        var labelColor2 = KTUtil.getCssVariableValue('--bs-gray-500');
        var borderColor2 = KTUtil.getCssVariableValue('--bs-gray-200');
        var baseColor2 = KTUtil.getCssVariableValue('--bs-warning');
        var secondaryColor2 = KTUtil.getCssVariableValue('--bs-gray-300');
        var series = [
                // {
                //     name: 'Kolektibilitas 1',
                //     data: [
                //         //  <?php /* foreach ($data->getDataKolektibilitas() as $row) {
                //         //      echo $row['total1'].','
                //         // }*/  ?>
                //     ],
                // },
            ]
        var options2 = {
            series:[
              @php
              $count = collect($data->getDataKolektibilitas())->count();
                  for($c = 1; $c <= $count;$c++){
                    $arr =array();
                    for($d = 1; $d <= $count;$d++){array_push($arr,$data->getDataKolektibilitas()[$d-1][$c]);}
                    echo ("{name: 'Kolektibilitas ".$c."',data: [");
                    $l = 1;
                    foreach($arr as $val){echo ( $val.',');}
                    echo ("], }, ");
                  }
              @endphp
            ],
            chart: {
                fontFamily: 'inherit',
                type: 'bar',
                height: height2,
                toolbar: {
                    show: true
                },
                zoom: {
                    enabled: true
                },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '70%',
                    borderRadius: 4,
                    hideZeroBarsWhenGrouped: true,
                    endingShape: 'rounded'
                },
            },
            legend: {
                show: false
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: [
                    <?php foreach ($data->getDataKolektibilitas() as $row) { ?>
                            <?php echo '"'.$row['minggu'].'"',',' ?>
                    <?php } ?>
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: labelColor2,
                        fontSize: '10px'
                    }
                },
                tickPlacement: 'on',
                min:0,max:5
            },
            yaxis: {
                labels: {
                    style: {
                        colors: labelColor2,
                        fontSize: '10px'
                    },
                    formatter: function (val) {
                        return toRp(val)
                    }
                }
            },
            fill: {
                opacity: 1
            },
            states: {
                normal: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                hover: {
                    filter: {
                        type: 'none',
                        value: 0
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'none',
                        value: 0
                    }
                }
            },
            tooltip: {
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (val) {
                        return 'Rp. ' + toRp(val)
                    }
                }
            },
            colors: ['#2E93fA', '#66DA26',  '#FF9800','#E91E63'],
            grid: {
                borderColor: borderColor2,
                strokeDashArray: 1,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };
        // colors: [baseColor2, secondaryColor2],

        var chart2 = new ApexCharts(element2, options2);
        chart2.render();
    </script>
@endsection

<!--begin::Charts Widget 1-->
<div class="card {{ $class ?? '' }}">
    <!--begin::Header-->
    <div class="card-header border-0 pt-5">
        <!--begin::Title-->
        <h3 class="card-title align-items-start flex-column">
			<span class="card-label fw-bolder fs-3 mb-1">Grafik Bulan {{ $data->getMonth() }}</span>

			{{-- <span class="text-muted fw-bold fs-7">More than 400 new members</span> --}}
		</h3>
        <!--end::Title-->
    </div>
    <!--end::Header-->

    <!--begin::Body-->
    <div class="card-body">
        <!--begin::Chart-->
        <div id="kt_apexcharts_1" style="height: 175px"></div>
        <!--end::Chart-->
    </div>
    <!--end::Body-->
</div>
<!--end::Charts Widget 1-->
