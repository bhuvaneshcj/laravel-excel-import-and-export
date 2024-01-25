# Laravel 10 Excel Import & Export

This repository provides an example of a Excel export and import in Laravel 10.

## Getting Started

### 1. Create a new project

```
composer create-project laravel/laravel excel
```

### 2. Navigate to project folder

```
cd excel
```

### 3. Set your Database name, Username, and Password in the .env file

This folder will be available in your project root folder

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE= // set database name
DB_USERNAME= // set username
DB_PASSWORD= // set password
```

### 4. Install Yoeunes toast package

```
composer require yoeunes/toastr
```

### 5. Configure Excel Package
```
composer require maatwebsite/excel
```

config/app.php
```
'providers' => [
    /*
     * Package Service Providers...
     */
    Maatwebsite\Excel\ExcelServiceProvider::class,
]

'aliases' => [
    ...
    'Excel' => Maatwebsite\Excel\Facades\Excel::class,
]
```
To publish the config, run the vendor publish command:
```
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```


### 6. Create Product Migration and Model
```
php artisan make:model Product -m
```

database/migrations/your_products_table
```
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('description');
    $table->float('price');
    $table->integer('stock');
    $table->timestamps();
});
```

app/Models/Product.php
```
protected $fillable = [
    'name',
    'description',
    'price',
    'stock',
];
```

### 7. Create Product Controller
```
php artisan make:controller ProductController --model=Product
```

app/Http/Controllers/ProductController.php
```
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->paginate(10);
        return view('products.index', compact('products'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        Product::create($request->all());

        toastr()->success('Product created');
        return redirect()->route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product->update($request->all());

        toastr()->success('Product updated');
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        toastr()->success('Product deleted');
        return redirect()->route('products.index');
    }
}
```

### 8. Create Master Controller
```
php artisan make:controller MasterController
```

app/Http/Controllers/MasterController.php
```
<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MasterController extends Controller
{
    public function importProductIndex()
    {
        return view('products.import');
    }

    public function importProduct(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        Excel::import(new ProductsImport, $file);
        toastr()->success('Products imported');
        return redirect()->route('products.index');
    }

    public function exportProduct()
    {
        return Excel::download(new ProductsExport, 'products_' . now()->toDateTimeString() . '.xlsx');
    }
}
```

### 9. Create Products Import

```
php artisan make:import ProductsImport --model=Product
```
app/Imports/ProductsImport.php

```
<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'],
            'description' => $row['description'],
            'stock' => $row['stock'],
            'price' => $row['price'],
        ]);
    }
}
```

### 10. Create Products Export
```
php artisan make:export ProductsExport --model=Product
```

app/Exports/ProductsExport.php
```
<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProductsExport implements FromView
{
    public function view(): View
    {
        return view('products.export', [
            'products' => Product::all(),
        ]);
    }
}
```

### 11. Add products Route
routes/web.php

```
<?php

use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class)->except('show');

Route::get('products/import/index', [MasterController::class, 'importProductIndex'])->name('importProductIndex');
Route::post('products/import', [MasterController::class, 'importProduct'])->name('importProduct');
Route::get('products/export', [MasterController::class, 'exportProduct'])->name('exportProduct');
```

### 12. Create Resources

resources/views/layouts/app.blade.php
```
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{env('APP_NAME')}}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>

    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{url('/')}}">{{env('APP_NAME')}}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('products.index')}}">
                            Products
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>

</html>
```

resources/views/products/index.blade.php
```
@extends('layouts.app')
@section('title', 'Products')
@section('content')

<div class="container mt-5">
    <div class="row align-items-center g-3 mb-3">
        <div class="col-md-6">
            <h4 class="mb-0">Products</h4>
        </div>
        <div class="col-md-6 text-md-end">
            <a class="btn btn-success me-2" href="{{route('importProductIndex')}}">
                Import
            </a>
            <a class="btn btn-secondary me-2" href="{{route('exportProduct')}}">
                Export
            </a>
            <a class="btn btn-primary" href="{{route('products.create')}}">
                Create
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Stock</th>
                    <th scope="col">Price</th>
                    <th scope="col">Created At</th>
                    <th scope="col">Updated At</th>
                    <th class="text-center" scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <th scope="row">{{++$i}}</th>
                    <td>{{$product->name}}</td>
                    <td>{{$product->stock}}</td>
                    <td>{{$product->price}}</td>
                    <td>{{$product->created_at->format('M d, Y')}}</td>
                    <td>{{$product->updated_at->format('M d, Y')}}</td>
                    <td class="text-center">
                        <form action="{{route('products.destroy', $product->id)}}" method="post">
                            @csrf @method('delete')
                            <a class="text-success fw-medium" href="{{route('products.edit', $product->id)}}">
                                Edit
                            </a>
                            <button class="border-0 bg-transparent text-danger fw-medium" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {!! $products->links() !!}
        </div>
        @endif
    </div>
</div>

@endsection
```

resources/views/products/create.blade.php
```
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
```

resources/views/products/edit.blade.php
```
@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')

<div class="container mt-5">
    <form class="row g-3" action="{{route('products.update', $product->id)}}" method="post">
        @csrf @method('put')
        <div class="col-md-4">
            <label class="form-label" for="nameInp">Name</label>
            <input class="form-control" id="nameInp" type="text" name="name" value="{{$product->name}}">
            @error('name')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="descriptionInp">Description</label>
            <input class="form-control" id="descriptionInp" type="text" name="description"
                value="{{$product->description}}">
            @error('description')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="priceInp">Price</label>
            <input class="form-control" id="priceInp" type="number" name="price" value="{{$product->price}}">
            @error('price')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="stockInp">Stock</label>
            <input class="form-control" id="stockInp" type="number" name="stock" value="{{$product->stock}}">
            @error('stock')<small class="text-danger">{{$message}}</small>@enderror
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Update</button>
        </div>
    </form>
</div>

@endsection
```

resources/views/products/import.blade.php
```
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
```

resources/views/products/export.blade.php
```
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Stock</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{$product->name}}</td>
            <td>{{$product->description}}</td>
            <td>{{$product->stock}}</td>
            <td>{{$product->price}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### 13. Adding Custom CSS (Optional)

public/css/main.css

```
@import url("https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap");

/*--------------------------------------------------------------
# General
--------------------------------------------------------------*/
:root {
    scroll-behavior: smooth;
}

body {
    font-family: "Roboto", sans-serif;
}

a {
    cursor: pointer;
    text-decoration: none;
}

a:hover,
a:focus {
    text-decoration: none;
}

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
    transition: background-color 5000s ease-in-out 0s;
}

textarea {
    resize: none;
}

hr {
    margin: 10px 0px;
    color: darkgray;
}

::-moz-selection {
    color: #ffffff;
    background: var(--bs-primary);
}

::selection {
    color: #ffffff;
    background: var(--bs-primary);
}

::-webkit-scrollbar {
    width: 5px;
    height: 8px;
    background-color: #fff;
}

::-webkit-scrollbar-thumb {
    background-color: #aab7cf;
}

/*--------------------------------------------------------------
# Override some default Bootstrap stylings
--------------------------------------------------------------*/
*:focus {
    box-shadow: none !important;
    outline: 0px !important;
}

.form-control,
.form-select {
    border-radius: 4px;
    border: 1px solid #ced4da;
}

.form-control:focus,
.form-select:focus {
    background-color: #fdfdfd;
    border: 1.5px solid var(--bs-primary);
    outline: 0;
    box-shadow: 0 0 0.25rem 0.05rem rgba(105, 108, 255, 0.1);
}
```

resources/views/layouts/app.blade.php

> Add the below line inside your head tag

```
<link rel="stylesheet" href="{{asset('css/main.css')}}">
```

### 14. Run migration

```
php artisan migrate
```

### 15. To fix bootstrap pagination issue

app/Providers/AppServiceProvider.php

```
/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    \Illuminate\Pagination\Paginator::useBootstrap();
}
```

### 16. Run your project

```
php artisan serve
```
