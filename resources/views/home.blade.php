@extends('layouts.index')

@section('content')
<!-- Page-header start -->
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Dashboard</h5>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}"> <i class="fa fa-home"></i> </a>
                    </li>
                    <li class="breadcrumb-item"><a href="#!">Dashboard</a>
                    </li>
                    {{-- <li class="breadcrumb-item"><a href="#!">Sample Page</a>
                    </li> --}}
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Page-header end -->


<div class="pcoded-inner-content">
    <div class="main-body">
        <div class="page-wrapper">
            <div class="page-body">
	            {{-- <div class="row">
                    <div class="alert alert-info alert-block" style="width: 100%;">
                        <strong>Transaction Record</strong>
                    </div>
                </div> --}}
                @if (!empty($transactions))    
                <div class="card shadow" style="width:100%;">
                  <div class="card-body">  
			        <div class="row">
                      <div class="col-sm-3">
                        <h5 class="mb-0" style="float: right;">Transaction Summary</h5>
                      </div>
                      <div class="col-sm-9 text-secondary">
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-sm-3">
                        <h6 class="mb-0" style="float: right;">Count</h6>
                      </div>
                      <div class="col-sm-9 text-secondary">
                        {{count($transactions ?? '')}}
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-3">
                        <h6 class="mb-0" style="float: right;">Date range </h6>
                      </div>
                      <div class="col-sm-9 text-secondary">
                          <?php $f = explode('-', $from); $from = $f[2]. ' '.$months[(int)$f[1]].', '.$f[0]; ?>
                          <?php $t = explode('-', $to); $to = $t[2].' '.$months[(int)$t[1]].', '.$t[0]; ?>
                        from {{$from}} to {{$to}}
                        <div id="small"></div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-3">
                        <h6 class="mb-0" style="float: right;">Total Quantities</h6>
                      </div>
                      <?php
                      $t_amount = 0;
                      $t_q = 0;
                        foreach ($transactions ?? '' as $t) {
                            $t_amount = $t_amount + $t->amount;
                            $t_q = $t_q + $t->quantity;
                        }
                      ?>
                      <div class="col-sm-9 text-secondary">
                        {{$t_q}} Liters
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-sm-3">
                        <h6 class="mb-0" style="float: right;">Total Amount of All transactions is</h6>
                      </div>
                      <div class="col-sm-9 text-secondary">
                        NGN {{$t_amount}}
                      </div>
                    </div><br>
                  </div>
                </div>
                @else
                <div class="row">
                    <div class="alert alert-info alert-block" style="width: 100%;">
                        <strong>No Transaction History For Today!</strong>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-xl-4 col-md-6">
                        <?php $users = \App\Models\User::where('organization_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(126, 170, 57);">{{count($users)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Users</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="fa fa-user f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(126, 170, 57);;">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <?php $agents = \App\Models\Agent::where('org_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(124,155,76);">{{count($agents)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Agents</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="fa fa-users f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(124,155,76);">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <?php $devices = \App\Models\Device::where('org_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(139,170,91));">{{count($devices)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Device</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="ti-mobile f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(139,170,91);">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <?php $categories = \App\Models\Category::where('org_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(126, 170, 57);">{{count($categories)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Categories</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="fa fa-table f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(126, 170, 57);">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <?php $customers = \App\Models\Customer::where('org_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(124,155,76);">{{count($customers)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Customers</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="fa fa-users f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(124,155,76);">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <?php $items = \App\Models\Item::where('org_id', '=', Auth::user()->organization_id)->get(); ?>
                        <div class="card">
                            <div class="card-block">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c" style="color: rgb(139,170,91);">{{count($items)}}</h4>
                                        <h6 class="text-muted m-b-0">No of Items</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <i class="ti-layout-grid2-alt f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer" style="background: rgb(139,170,91);">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">% Open</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="fa fa-line-chart text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Hello card</h5>
                                <span>lorem ipsum dolor sit amet, consectetur adipisicing elit</span>
                                <div class="card-header-right">
                                    <ul class="list-unstyled card-option">
                                        <li><i class="fa fa-chevron-left"></i></li>
                                        <li><i class="fa fa-window-maximize full-card"></i></li>
                                        <li><i class="fa fa-minus minimize-card"></i></li>
                                        <li><i class="fa fa-refresh reload-card"></i></li>
                                        <li><i class="fa fa-times close-card"></i></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-block">
                                <p>
                                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
                                    in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
                                </p>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
