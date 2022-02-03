@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">{{ __('Listes Posts') }}

                        <button type="button" class="btnNewPost btn btn-outline-secondary" data-toggle="modal" data-target="#postModal">new Post</button>
                    </div>

                    <div class="card-body">
                      <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">post ID</th>
                                <th scope="col">TYPE</th>
                                <th scope="col">text</th>
                                <th scope="col">date</th>
                                <th scope="col">file</th>
                                
                            </tr>
                            </thead>
                            <tbody>
                            
                            @foreach ($posts as $item)

                                <tr class="{{'published-'.$item->is_published}}">
                                
                                    <td>{{$item->id_page}} </td>
                                    <td>{{$item->type}}</td>
                                    <td>{{$item->message." ".$item->story}}</td>
                                    <td> {{ $item->created_time->format('d-m-Y h:m')}}</td>
                                    <td>
                                        @if($item->full_picture!=null)
                                            <a href="{{$item->full_picture}}">file</a>
                                        @endif
                                    </td>
                                    
                                </tr>
                            @endforeach


                            </tbody>
                        </table>
                    
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Modal -->
   <div class="modal fade" id="postModal" tabindex="-1" role="dialog" aria-labelledby="postModal" aria-hidden="true" width="500px">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="{{route('savepost')}}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="idpage" value="{{$idpage}}"/>
                <input type="hidden" name="tokenPage" value="{{$tokenPage}}"/>
                <div class="modal-body">

                        <div class="form-group">
                            <label for="exampleFormControlTextarea1">Post Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <div class="file-upload">
                                <div class="image-upload-wrap">
                                    <input class="file-upload-input" id="fileUpload" name="fileUpload" type='file' onchange="readURL(this);" accept="image/*"  multiple/>
                                    <div class="drag-text">
                                        <h3>file</h3>
                                    </div>
                                </div>
                                <div class="file-upload-content">
                                    <img class="file-upload-image" src="#" alt="your image" />
                                    <div class="image-title-wrap">
                                        <button type="button" onclick="removeUpload()" class="remove-image">Remove <span class="image-title">Uploaded Image</span></button>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" name="inlineCheckbox1" type="checkbox" id="inlineCheckbox1" value="true">
                            <label class="form-check-label" for="inlineCheckbox1">Schedule</label>
                        </div>

                        <div class="form-group">
                            <input name="dateSchedule" id="inputSchedule" class="form-control" type="datetime-local" placeholder="select date time" />
                        </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>

                </div>

                </form>
            </div>
        </div>
    </div>
@endsection
