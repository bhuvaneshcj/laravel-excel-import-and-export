@extends('layouts.app')
@section('title', 'New Product')
@section('content')

<div class="container mt-5">
    <form class="row g-3" action="{{route('products.store')}}" method="post">
        @csrf
        <div class="col-md-4">
            <label class="form-label" for="nameInp">Name</label>
            <input class="form-control" id="nameInp" type="text" name="name" value="{{old('name')}}">
            @error('name')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="descriptionInp">Description</label>
            <input class="form-control" id="descriptionInp" type="text" name="description"
                value="{{old('description')}}">
            @error('description')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="priceInp">Price</label>
            <input class="form-control" id="priceInp" type="number" name="price" value="{{old('price')}}">
            @error('price')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stockInp">Stock</label>
            <input class="form-control" id="stockInp" type="number" name="stock" value="{{old('stock')}}">
            @error('stock')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Create</button>
        </div>
    </form>
</div>

@endsection