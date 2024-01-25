@extends('layouts.app')
@section('title', 'Import Product')
@section('content')

<div class="container mt-5">
    <form class="row g-3" action="{{route('importProduct')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="col-md-4">
            <label class="form-label" for="fileInp">File</label>
            <input class="form-control" id="fileInp" type="file" name="file">
            @error('file')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Import</button>
        </div>
    </form>
</div>

@endsection