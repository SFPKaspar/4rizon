@extends('layouts.app')
@section('pageTitle','In-Active Users')
@section('content')
<style>
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 11px !important;
    }
    </style>
    <div class="container-fluid">
        <div class="row column_title">
            <div class="col-md-9">
               <div class="page_title">
                  <h2>Users Lists</h2>
               </div>
            </div>
            <div class="col-md-3">
                <div style="margin-top:35px">
                <select name="users"   class="form-control users" required style="background: linear-gradient(255.43deg, rgba(50, 165, 249, 0.48) 0%, rgba(50, 165, 249, 0.16) 49.48%, rgba(27, 65, 107, 0.13) 100%) !important; color: aliceblue; ">
                    <option style = "color: aliceblue;" value="" active>Categories</option>
                 <option style = "color: black;" value = "{{route('users_list')}}">All</option>
                 <option style = "color: black;"  value="{{route('active_users')}}">Active</option>
                 <option style = "color: black;" value="{{route('inactive_users')}}">Pending</option>
                 <option style = "color: black;" value="{{route('denied_users')}}">Denied</option>
                 <option style = "color: black;" value="{{route('blocked_users')}}">Blocked</option>
               </select>
                </div>
            </div>
        </div>
         <!-- row -->
        <div class="row">
            <!-- table section -->
            <div class="col-md-12">
               <div class="white_shd full margin_bottom_30">
                  <div class = "alerti">
                     @include('flashmessages')
                 </div>
                  <div class="full graph_head">
                    
                  </div>
               <form class="container-fluid" action="{{route('multiple_delete')}}" method="POST" enctype="multipart/form-data" style="padding:30px;">
                     @csrf
                  
                     <div class="row"  style="margin-bottom:20px !important;">
                        <div class="col-md-7">

                        </div>
                        <div class="col-md-5">
                            <a href="{{route('register_new_djuser')}}" class="btn btn-inverse my-button btn-outline-primary">Add New User</a>
                            @if( Auth::user()->role == "super admin")
                            <span data-href="/export-csv" id="export" class="btn btn-inverse my-button btn-outline-primary" onclick ="exportTasks (event.target);">Export</span>
                           
                            <button type  = "submit" class=" delete_btn btn btn-inverse my-button btn-outline-primary"  style="margin-left: 0px;">Delete
                            </button>
                            @endif
                              

                        </div>
                     </div>
                  <div class="table_section padding_infor_info">
                     <div class="table-responsive-sm">
                        <table class="table table-striped" id = "myTable">
                           <thead>
                              <tr>
                                <th>#</th>
                                 <th>Action</th>
                                 <th>Status</th>
                                 <th>Status</th>
                                 <th>First name</th>
                                 <th>Last name</th>
                                 <th>Email</th>
                                 <th>Age</th>
                                 <th>identification No</th>
                                 {{-- <th>Role</th> --}}
                                 <th>Citizenship</th>
                                 {{-- <th>Gender</th> --}}
                                 <th>Register at</th>
                                <th>Select All &nbsp;<input type="checkbox" id="selectAll" /></th>
                              </tr>
                           </thead>
                           <tbody>
                              @if(!empty($users_list))
                              <?php $count = 1;?>
                              @foreach ($users_list as $user)
                              <tr>
                                <td class="text-capitalize">{{$count}}</td>
                                <td class = "row">
                                    
                                    <a style="margin: 2px;" href="view_user_details/{{$user['user_id']}}" class="btn check-icon btn-sm btn-inverse btn-outline-primary">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    @if( Auth::user()->role == "super admin")
                                    <a style="margin: 2px;" href="edit_user_details/{{$user['user_id']}}" class="btn btn-sm btn-blue  btn-inverse btn-outline-primary">
                                        <i class="fa fa-pencil"></i> 
                                   </a>
                                   @endif
                                   @if( Auth::user()->role == "super admin")
                                   {{-- <a style="margin: 2px;" id = "btn_multidelete1" style="margin: 2px;" href="delete_user_details/{{$user['user_id']}}" data-toggle="modal" data-target="#multiconfirm-modal1" class="btn btn-sm btn-red btn-inverse btn-outline-danger"><i class="fa fa-trash"></i></a> --}}
                                   <a style="margin: 2px;" style="margin: 2px;"   href="#" 
                                   data-id={{$user['user_id']}} 
                                   data-toggle="modal" 
                                   data-target="#deleteModal" class="btn btn-sm btn-red btn-inverse btn-outline-danger del"><i class="fa fa-trash"></i></a>
                                
                                   @endif
                                   {{-- <div id = "btn_multidelete1" class="btn btn-inverse my-button btn-outline-primary" data-toggle="modal" data-target="#multiconfirm-modal1" style="margin-left: 0px; display:none">Delete --}}
                                </td>  
                                <td class="text-capitalize">
                                    @if ($user['user_status'] == "0")
                                    <a style="color:#fcfbff; font-size:xx-large; margin-top:-12px;"  class="btn btn-sm btn-green"><i class="fa fa-question" style="width:25px;"></i></a>
                                    @elseif ($user['user_status'] == "1")
                                    <a style="color: lawngreen; font-size:xx-large;  margin-top:-12px;" class="btn btn-sm btn-green"><i class="fa fa-check" style="width:25px;"></i></a>
                                    @elseif ($user['user_status'] == "3")
                                    <a style="color :red; font-size:xx-large;  margin-top:-12px;" class="btn btn-sm btn-green"><i class="fa fa-times" style="width:25px;"></i></a>
                                    @elseif ($user['user_status'] == "-1")
                                    <a style="color: red;  font-size:xx-large;  margin-top:-12px;" class="btn btn-sm btn-green"><i class="fa fa-ban" style="width:25px;"></i></a>
                                    @endif
                                </td>  
                                 <td class="text-capitalize">
                                    @if ($user['identification_type'] == 0)
                                    Incomplete Profile
                                @elseif ($user['user_status'] == "0")
                                    Pending
                                @elseif ($user['user_status'] == "1")
                                    Active
                                @elseif ($user['user_status'] == "3")
                                   Invalid Id
                                @elseif ($user['user_status'] == "-1")
                                    Blocked
                                @endif
                            </td>                              
                                <td class="text-capitalize">{{$user['first_name']}}</td>
                                <td class="text-capitalize">{{$user['last_name']}}</td>
                                <td class="text-capitalize">{{$user['email']}}</td>
                                @php
                                $dateOfBirth = $user['dob'];
                                
                                    $today = date("Y-m-d");
                                    $diff = date_diff(date_create($dateOfBirth), date_create($today));
                                    $age = $diff->format('%y');
                                @endphp
                                <td class="text-capitalize">{{$age}} years</td>
                                <td class="text-capitalize">{{$user['identification_num']}}</td>
                                {{-- <td class="text-capitalize">{{$user['role']}}</td> --}}
                                <td class="text-capitalize">
                                    @if($user['identification_type'] == 0 ) NIL
                                    @elseif($user['nationality'] == "SA" || $user['nationality'] == "South Africa" || $user['identification_type'] == 1 ) SA
                                @else Not SA @endif</td>
                                {{-- <td class="text-capitalize">{{$user['gender']}}</td> --}}
                                @php
                                $register = strtotime($user['created_at']);
                                   $date = date('m/d/y',$register) ;
                                @endphp
                                <td class="text-capitalize">{{$user['created_at']}}</td>
                              
                                <td>
                                 <input type = "checkbox" id = "example" name = "checkbox[]" value="{{$user['user_id']}}"></td> 
                              </tr>
                              
                              <?php $count = $count+1;?>
                              @endforeach
                              
                              @else
                              <tr>
                                <th>No User Found</th>
                              </tr>
                              @endif
                           </tbody>
                        
                        </table>
                        
                     </div>
                  </div>
               </form>
               <div class="modal modal-danger fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                      <div class="modal-content">
                          <div class="modal-header">
                              <h5 class="modal-title" style="color:black !important;" id="exampleModalLabel">Delete User</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                              </button>
                          </div>
                          <div class="modal-body">
                          <form action="{{ route('confirm_delete') }}" method="post">
                              @csrf
                              
                              <input id="id" name="id" hidden>
                              <h5 class="text-center" style="color:black !important;">Are you sure you want to delete this user?</h5>
                            
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                          </div>
                          </form>
                      </div>
                  </div>
              </div>
            </div>
         </div>            
      </div>
   </div>
@stop