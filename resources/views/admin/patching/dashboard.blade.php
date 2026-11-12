@extends('layouts.admin')

@section('title')
    {{ trans('panel.menu.patching') }}
@endsection

@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-3">
        <form method="get" action="/admin/patching/dashboard" name="myform">
            <input type="hidden" name="clear"/>
            <label class="recommended" for="patching_group">{{ trans('cruds.logicalServer.fields.attributes') }}</label>
            <select name="attributes_filter[]" class="form-control select2-free"
                    id="attributes_filter"
                    multiple onchange="if (this.value.length==0) document.myform.clear.value = '1'; this.form.submit()">
                @foreach($attributes_filter as $a)
                    @if (!in_array($a, $attributes_list))
                        <option selected>{{$a}}</option>
                    @endif
                @endforeach
                @foreach($attributes_list as $a)
                    <option {{ ($attributes_filter ? in_array($a, $attributes_filter) : false) ? "selected" : "" }}>{{ $a }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        {{ trans('panel.menu.patching') }}
    </div>
    <div class="card-body" >
        <div class="row">
            <div class="col-md-4">
                <canvas id="doughnut_chart1_div"></canvas>
            </div>
            <div class="col-md-8">
                <canvas id="bar_chart2_div"></canvas>
            </div>
        </div>
            <div class="chart-container" style="position: relative; height: {{ count($active_attributes_list) * 50 + 50}}px; border:1px solid">
                <canvas id="bar_chart3_div"></canvas>
            </div>
    </div>
</div>
@endsection

@section('scripts')
<script @nonce>
document.addEventListener("DOMContentLoaded", function () {

  const ctx1 = document.getElementById('doughnut_chart1_div').getContext('2d');

  const cfg1 = {
    type: 'doughnut',
    data: {
        labels : ['made','late','undefined'],
        datasets: [
            {
            label: 'Dataset 1',
            data: [
                @php
                    $made = 0;
                    $late = 0;
                    $undef = 0;
                    foreach($patches as $patch) {
                        if ($patch->next_update==null)
                            $undef++;
                        elseif (\Carbon\Carbon::parse($patch->next_update)->isPast())
                            $late++;
                        else
                            $made++;
                    }
                    echo $made;
                    echo ',';
                    echo $late;
                    echo ',';
                    echo $undef;
                @endphp
            ],
            backgroundColor: ['#59A14F','#E15759','#e4e5e6'],
            }
        ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
      datalabels: {
        display: false
      }
      },
    },
};

  // --------------------------------------------------------------------
  // https://www.chartjs.org/docs/latest/samples/bar/stacked.html
  const ctx2 = document.getElementById('bar_chart2_div').getContext('2d');

  const cfg2 = {
    type: 'bar',
    data: {
        labels: [
            <?php
            for($i=-12; $i<=12; $i++) {
                echo "'";
                echo Carbon\Carbon::today()->addMonth($i)->format('M y');
                echo "', ";
                }
            ?>
        ],
        datasets: [
        {
            label: 'made',
            data: [
            <?php
            for($i=-12; $i<=12; $i++) {
                $count = 0;
                $year = today()->addMonth($i)->year;
                $month = today()->addMonth($i)->month;
                foreach($patches as $patch) {
                    if (
                        ($patch->update_date!==null) &&
                        (Carbon\Carbon::parse($patch->update_date)->month===$month) &&
                        (Carbon\Carbon::parse($patch->update_date)->year===$year)
                    )
                        $count++;
                    }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#59A14F',
        },
        {
            label: 'late',
            data: [
            <?php
            for($i=-12; $i<=12; $i++) {
                $count = 0;
                $year = today()->addMonth($i)->year;
                $month = today()->addMonth($i)->month;
                foreach($patches as $patch) {
                    if (
                        ($patch->next_update!==null) &&
                        (Carbon\Carbon::parse($patch->next_update)->month==$month) &&
                        (Carbon\Carbon::parse($patch->next_update)->year==$year) &&
                        (Carbon\Carbon::parse($patch->next_update)->lt(today())
                        )
                    ) {
                        $count++;
                        }
                    }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#E15759',
        },
        {
            label: 'plan',
            data: [
            <?php
            for($i=-12; $i<=12; $i++) {
                $count = 0;
                $year = today()->addMonth($i)->year;
                $month = today()->addMonth($i)->month;
                foreach($patches as $patch) {
                    if (
                        ($patch->next_update!==null) &&
                        (Carbon\Carbon::parse($patch->next_update)->month==$month) &&
                        (Carbon\Carbon::parse($patch->next_update)->year==$year) &&
                        (Carbon\Carbon::parse($patch->next_update)->gt(today()))
                    )
                        $count++;
                    }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#e4e5e6',
        },
        ]
    },

    options: {
        responsive: true,
        plugins: {
          legend: {
            display: false,
          },
      datalabels: {
        display: false
      }
    },
       scales: {
            x: {
                stacked: true
            },
            y: {
                stacked: true
            }
        },
    }
  };

  // --------------------------------------------------------------------

  const ctx3 = document.getElementById('bar_chart3_div').getContext('2d');

  const cfg3 = {
    type: 'bar',
    data: {
        labels: [
            <?php
            foreach($active_attributes_list as $attribute) {
                echo "'";
                echo $attribute;
                echo "', ";
                }
            ?>
        ],
        datasets: [
        {
            data: [
            <?php
            $year = today()->year;
            $month = today()->month;
            foreach($active_attributes_list as $attribute) {
                $count = 0;
                foreach($patches as $patch) {
                    if (in_array($attribute, explode(' ', $patch->attributes ?? ''), true)) {
                        if (
                            ($patch->next_update!==null) &&
                            (!Carbon\Carbon::parse($patch->next_update)->lt(today()))
                        )
                            $count++;
                        }
                    }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#59A14F',
        },
        {
            data: [
            <?php
            $year = today()->year;
            $month = today()->month;
            foreach($active_attributes_list as $attribute) {
                $count = 0;
                foreach($patches as $patch) {
                    if (in_array($attribute, explode(' ', $patch->attributes ?? ''), true)) {
                        if (
                        ($patch->next_update!==null) &&
                        (Carbon\Carbon::parse($patch->next_update)->lt(today()))
                        )
                    {
                        $count++;
                        }
                    }
                }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#E15759',
        },
        {
            data: [
            <?php
            $year = today()->year;
            $month = today()->month;
            foreach($active_attributes_list as $attribute) {
                $count = 0;
                foreach($patches as $patch) {
                    if (in_array($attribute, explode(' ', $patch->attributes ?? ''), true)) {
                        if (
                            ($patch->next_update===null)
                        )
                            $count++;
                        }
                    }
                echo $count;
                echo ", ";
                }
            ?>
            ],
            backgroundColor: '#e4e5e6',
        },
    ]},
    options: {
    indexAxis: 'y', 
   responsive: true,
   maintainAspectRatio: false,

  plugins: {
    tooltip: {
      enabled: false
    },
    legend: {
      display: false
    },
      datalabels: {
        display: false
      }
  },

  scales: {
    x: {
      ticks: {
        beginAtZero: true,
        font: {
          family: "'Open Sans Bold', sans-serif",
          size: 11
        }
      },
      stacked: true
    },
    y: {
      ticks: {
        font: {
          family: "'Open Sans Bold', sans-serif",
          size: 11
        }
      },
      stacked: true,
      grid: {
        display: false,
        color: "#fff",
        zeroLineColor: "#fff",
        zeroLineWidth: 0
      }
    }
  },
    }

};

// --------------------------------------------------------------------

  window.onload = function() {
      new Chart(ctx1, cfg1);
      new Chart(ctx2, cfg2);
      new Chart(ctx3, cfg3);
    };

    // --------------------------------------------------------------------

});

</script>
@vite(['resources/charts/chart-patching.js'])
@endsection
