@extends('layouts.layout')
@section('title','Test App')

@section('content')
<div class="container">
	<h1>Welcome {{$user['name']}}</h1>
	<ul>
		<li>email:  {{$user['email']}}</li>
	</ul>
	<div class="row">
		<div class="col-md-6">
			@include('common.errors')
			<form action="{{url('fb-post')}}" method="post">
				<h2>Post a message on FB</h2>
				{{ csrf_field() }}
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-edit"></i></div>
						<textarea name="message" rows="10" class="form-control"></textarea>
					</div>
				</div>
				<div class="form-group">
					<button type="submit" name="submit_fb_post" class="btn btn-lg btn-primary"><i class="fa fa-lg fa-facebook-square"></i> Post on Facebook</button>
				</div>
			</form>
		</div>
		<div class="col-md-6">
			<h2>Post Result</h2>
			@if(!empty($error))
				<div class="alert alert-danger">
					{{$error}}
				</div>
			@endif
			@if(!empty($posts))
			<ul class="list-group">
				@foreach($posts as $post)
					<li class="list-group-item">
						@isset($post['response']['message'])
							<p class="text-info">{{$post['response']['message']}}</p>
						@endisset
						@if ($post['response']['status'] == 200)
							Successful post:
						@else
							Faile post:
						@endif
						{{$post['post_id']}}<br />
						posted on {{$post['issued']}}
					</li>
				@endforeach
			</ul>
			@endif
		</div>
	</div>
</div>
@endsection