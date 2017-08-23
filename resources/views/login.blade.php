@extends('layouts.layout')
@section('title','Test App')

@section('content')
<div class="container">
	<div class="text-center">
		<p class="text-info">Please login to use our Facebook Post</p>
		<p><a href="{{$loginUrl}}" class="btn btn-lg btn-primary"><i class="fa fa-lg fa-fw fa-facebook-square"></i> Login to the Facebook</a></p>
	</div>
</div>
@endsection