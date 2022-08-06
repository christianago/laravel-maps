<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ config('app.name') }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/toastr.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
</head>
<body>
<div class="container-fluid">
    <div class="row mt-3 mb-3">
        <div class="col-3">
            <button class="btn btn-success get" type="button">Get Athens Schools</button>
        </div>
        <div class="col-9">
            <input type="text" class="form-control" value="" id="search" placeholder="Search for anything..." />
            <select class="category form-control"></select>
            <button class="btn btn-primary search" type="button">Search</button>
        </div>
    </div>
    <div class="row">
        <div id="map"></div>
    </div>
</div>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapApiKey }}&libraries=geometry"></script>
<script src="js/jquery-3.6.0.min.js"></script>
<script src="js/toastr.min.js"></script>
<script src="js/custom.js"></script>
</body>
</html>