<div class="row mb-3">
	<div class="col-xl-6">
	@if(count($errors)>0)
	        @foreach($errors->all() as $error)
	           <div class="alert alert-dange bg-danger text-white alert-dismissible fade show mb-xl-0" role="alert">
			       {{$error}} 
			    </div>
	        @endforeach
	@endif
	@if(session('success'))
	    <div class="alert alert-success bg-success text-white alert-dismissible fade show" role="alert">
	        {{session('success')}}
	    </div>
	@endif
	@if(session('error'))
	    <div class="alert alert-dange bg-danger text-white alert-dismissible fade show mb-xl-0" role="alert">
	        {{session('error')}}
	    </div>
	@endif
	</div>
</div>