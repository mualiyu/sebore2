@extends('layouts.index')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h5 class="m-b-10">Create User</h5>
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
		    @include('layouts.flash')

                <div class="row">
                    <div class="col-sm-12">
			    <div class="card">
                              <div class="card-header">
                                  <h5>Create User</h5>
                                  <!--<span>Add class of <code>.form-control</code> with <code>&lt;input&gt;</code> tag</span>-->
                              </div>
                              <div class="card-block">
                                  <form class="form-material" method="POST" action="{{route('create_user')}}">
					@csrf
                                      <div class="form-group form-default">
                                          <input type="text" name="name" value="{{old('name')}}" class="form-control" required="">
                                          <span class="form-bar"></span>
                                          <label class="float-label">Name</label>
                                          @error('name')
                                                <Span style="color: red;">{{$message}}</Span>
                                          @enderror
                                      </div>
                                      <div class="form-group form-default">
                                          <input type="email" name="email" value="{{old('email')}}" class="form-control" required="">
                                          <span class="form-bar"></span>
                                          <label class="float-label">Email (exa@gmail.com)</label>
                                          @error('email')
                                                <Span style="color: red;">{{$message}}</Span>
                                          @enderror
                                      </div>
				      <div class="form-group form-default">
                                          <input type="number" name="phone" value="{{old('phone')}}" class="form-control" required="">
                                          <span class="form-bar"></span>
                                          <label class="float-label">Phone</label>
                                          @error('phone')
                                                <Span style="color: red;">{{$message}}</Span>
                                          @enderror
                                      </div>
				      <div class="row">
					<div class="col-sm-6">
						      <div class="form-group form-default">
							  <input type="password" name="password" class="form-control" required>
							  <span class="form-bar"></span>
							  <label class="float-label">Password</label>
                              @error('password')
                                                <Span style="color: red;">{{$message}}</Span>
                                          @enderror
						      </div>
					</div>
					<div class="col-sm-6">
					    <div class="form-group form-default">
					  	<input type="password" name="password_confirmation" class="form-control" required>
					  	<span class="form-bar"></span>
					  	<label class="float-label">Confirm Password</label>
					    </div>
					</div>
				      </div>

                                      <div class="form-group form-default">
                                          <select type="text" name="role" class="form-control" value="{{old('role')}}" required="" maxlength="6">
                                            <?php $roles = \App\Models\AdminRole::all(); ?>
                                            @foreach ($roles as $r)
                                            <option value="{{$r->id}}">{{$r->name}}</option>
                                            @endforeach
                                            {{-- <option value="admin">Admin</option>
                                            <option value="supervisor">Suppervisor</option>
                                            <option value="user">User</option> --}}
                                        </select>
                                          <span class="form-bar"></span>
                                          <label class="float-label">Role</label>
                                          @error('role')
                                                <Span style="color: red;">{{$message}}</Span>
                                          @enderror
                                      </div>

                                      <div class="form-group form-default">
                                          <input type="submit" class="btn btn-submit" value="Register" id="">
                                      </div>
                                  </form>
                              </div>
                            </div>
		    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
