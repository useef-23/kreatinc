@extends('layouts.app')

@section('content')




<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4">
                <div class=" image d-flex flex-column justify-content-center align-items-center"> <button class="btn btn-secondary"> <img src="{{Auth::user()->picture}}" height="100" width="100" /></button> <span class="name mt-3">{{Auth::user()->name}}</span> <span class="idd">{{Auth::user()->email}}</span>
                    
                    

                    
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Listes Pages') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">Logo</th>
                                <th scope="col">Id page</th>
                                <th scope="col">Name page</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                           
                            @foreach ($pages as $item)
                                <tr>
                                    <th scope="row"><img class="imgPage" src="{{$item->image}}"/></th>
                                    <td>{{$item->id}}</td>
                                    <td>{{$item->name}}</td>
                                    <td><a href="{{route('Postes',['id'=>$item->id,'token'=>$item->access_token])}}" class="badge badge-light">Posts</a></td>
                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
