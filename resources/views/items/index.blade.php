@extends('layouts.index')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{$device->name}} Items's</h5>
                </div>
            </div>
            <div class="col-md-4">
                <ul class="breadcrumb-title">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}"> <i class="fa fa-home"></i> </a>
                    </li>
                    <li class="breadcrumb-item"><a href="#">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item"><a href="#">Items</a>
                    </li>
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
		     @include('layouts.flash')
                <!-- Basic table card start -->
                <a href="{{route('show_devices')}}" style="right:0;" class="btn btn-secondary">Back</a>&nbsp;&nbsp;&nbsp;
		<a href="{{route('show_add_item', ['id'=> $device->id])}}" style="right:0;" class="btn btn-primary">Add New Item</a>
        <br>
                    <div class="card">
                        <div class="card-header">
                            <h5>Item</h5>
                            <div class="card-header-right">
                                <ul class="list-unstyled card-option">
                                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                                    <li><i class="fa fa-window-maximize full-card"></i></li>
                                    <li><i class="fa fa-minus minimize-card"></i></li>
                                    <li><i class="fa fa-refresh reload-card"></i></li>
                                    <li><i class="fa fa-trash close-card"></i></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-block table-border-style">
                            <div class="table-responsive">
                                <table id="data_table" class="table-sm table-striped table-bordered dt-responsive nowrap " style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Category</th>
					    <th>Measure</th>
					    <th>Unit</th>
					    <th>Code</th>
					    <th>With Quantity?</th>
					    <th>With Payer Name?</th>
					    <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
					    <?php $i_i = count($items); ?>
				        @foreach ($items as $i)
					<?php  $cat = \App\Models\Category::find($i->item_cart->category_id); ?>
					<tr>
						<th scope="row">{{$i_i}}</th>
						<td>{{$i->item_cart->name}}</td>
						<td>{{$cat->name}}</td>
						<td>{{$i->item_cart->measure}}</td>
						<td>{{$i->item_cart->unit}}</td>
						<td>{{$i->item_cart->code}}</td>
						<td>{{$i->item_cart->with_q ? 'Yes':'No'}}</td>
						<td>{{$i->item_cart->with_p ? 'Yes':'No'}}</td>
						<td>
                            <form method="POST" id="delete-form[{{$i_i}}]" action="{{route('delete_item',['id'=>$i->id])}}">
                                <a href="{{route('show_edit_item_cart', ['id'=>$i->item_cart->id])}}" class="btn btn-primary">Edit</a>
                                @csrf 
                                <a  onclick="
                                    if(confirm('Are you sure You want to Delete this Item -( {{$i->id}} )? ')){
                                        document.getElementById('delete-form[{{$i_i}}]').submit();
                                    }
                                        event.preventDefault();"
                                    class="btn btn-warning" 
                                    style="color: black; background:red;">
                                    Delete
                                </a>
                            </form>
						</td>
						<?php $i_i--?>
					</tr>
					@endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>
@endsection
