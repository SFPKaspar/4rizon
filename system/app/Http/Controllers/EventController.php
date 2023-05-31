<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\UserEmails;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DjUser;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\EventAttend;
use App\Models\AdminNotification;
use App\Models\Bookings;
use App\Models\Guest;
use App\Models\Dj_Event;
use App\Models\Notifications;
use App\Models\user_infos;
use App\Models\Transaction;
use App\Models\DjTime;
use Illuminate\Support\Str;
use DB;
use DateTime;
 
class EventController extends Controller
{
    function event_list(){
    	$event_data = Event::all();
    	return view("event.list",['event_list'=>$event_data,]);
    }
    function users_event_attend_list(){
        $event_data = Bookings::all();
       
        return view("event.attendList",['event_list'=>$event_data,]);
    }
   function checkDate(Request $request)
   {
       
       $date = $request->date;
       
       $check = Event::where('event_date',$date)->first();
       if(!empty($check)){
        $response = "<span style='color: red;'>Not Available.</span>";
        
       }
       else{
        $response = "<span style='color: green;'>Available.</span>";
       }
       echo $response;
   }
    function users_transaction_list(){
        $event_data = Transaction::all();
        return view("event.transactions",['event_list'=>$event_data,]);
    }
    function users_events_attend_lists($fromdate,$todate){
        if($fromdate == $todate){
          $event_data = Bookings::whereNotNull('enter_at')->where('created_at', '>=', $fromdate.' 00:00:00')->get();
        }else{
              $event_data = Bookings::whereNotNull('enter_at')->whereDate('created_at','>=',$fromdate)->whereDate('created_at','<=',$todate)->get();
        }
        return view("event.attendList",['event_list'=>$event_data,]);
    }
    function edit_event($id){
    	$event = Event::find($id);
        // $start = $event->event_start_time;
        // $end = $event->event_end_time;
        // $start = date("g:i A", strtotime($event->event_start_time." UTC"));
        $start = date('h:i A', strtotime($event->event_start_time));
        $end = date("h:i A", strtotime($event->event_end_time));
        
        $dj = DjUser::where('dj_status','1')->get();
        $dj_time = Dj_Event::where('event_id',$id)->get('time');
     
        if (strtotime($start) > strtotime($end)) {
            $next_day = $event->event_date;
            $datetime = new DateTime($next_day);
            $datetime->modify('+1 day');
            $event_date = $datetime->format('Y-m-d');
        }
        else{
            $event_date = $event->event_date;
        }
       
        $start_t = date('h:i A', strtotime($event->event_start_time));
        $end_t = date('h:i A', strtotime($event->event_end_time));

        $begin = new DateTime($event->event_date.''.$start_t);
       
        $end1 = new DateTime($event_date.''.$end_t);
    //  echo json_encode($begin);die();
        while($begin < $end1) {
            
            $output = $begin->format('h:i A');
            $begin->modify('+60 minutes');          /** Note, it modifies time by 60 minutes */
            // $output .= $begin->format('h:i A');
            
            $timeRanges[] = $output;
            // $result = Carbon::createFromFormat('h:i A', $output)->format('h:i A');
            // array_push($timeRanges,$result);
        }

        // print_r($timeRanges);
        // echo json_encode($timeRanges);die();
        // $intervals = CarbonInterval::hours(1)->toPeriod($start, $end);
        // echo json_encode($intervals);die();
        $eventData = Dj_Event::where('event_id',$id)->get();
         
        $info = Dj_Event::where('event_id',$id)->get();
       
        $data1 = [];
        $data = [];
        
    	return view("event.edit",['event'=>$event,'dj_list'=>$dj,'intervals'=>$timeRanges,'data'=>$data,'start_time'=>$start,'end_time'=>$end,'event_data'=>$eventData]);
    }
    function delete_timeslot($id){
        $event= Dj_Event::where('id', $id)->first();
        $event_id = $event->event_id;
        $delete= Dj_Event::where('id', $id)->delete();
    	
        $data['success'] = "deleted";
        return redirect('/edit_event/'.$event_id);

       
    }
    function view_user_event_details($id){
    	$event = Bookings::find($id);
        $guests = Guest::where('booking_id','=',$event->booking_id)->where('status','=',0)->get();
    	return view("event.viewDetails",['event'=>$event,'guests'=>$guests]);
    }
    function add_new_event(){
    	return view("event.add");
    }
    public function event_delete(Request $req)
{
    // Need to find all addresses with the contacdt Id and delete them.
    
    $user_id = $req->id;
   
    Event::where('id',$user_id)->delete();
    return redirect('/event_list')->with('success','Event deleted successfully');   
}
    function create_event(Request $req){
        // $startTime = date("g:i A", strtotime($req->event_start_time." UTC"));
        // $endTime = date("g:i A", strtotime($req->event_end_time." UTC"));
        $check = Event::where('event_date',$req->event_date)->first();
        if(!empty($check)){
            return redirect('/add_new_event')->with('success','This date is not available');
        }
        else{
           
            $event = new Event;
        $event->event_name         = $req->event_name;
        $event->pkg_price         = $req->event_price;
        $event->event_short_description        = $req->event_short_description;
        $event->event_description          = $req->event_description   ;
        if ($req->hasFile('event_image')) {
            $eventPic             = time().'.'.$req->event_image->extension();  
            $req->event_image->move(public_path('image'), $eventPic);
            $event->event_image = $eventPic;
        }

        $event->event_date  = $req->event_date;
        $event->event_start_time  =date('h:i A', strtotime($req->event_start_time));
        $event->event_end_time    = date('h:i A', strtotime($req->event_end_time));
        $event->stage_1  = $req->stage_1;
        $event->stage_2  = $req->stage_2;
        $event->stage_3  = $req->stage_3;
        $event->special  = $req->special;
        $event->pkg_price  = $req->event_price;
        $event->save();
        $last_id = $event->id;
        $getLastEvent = Event::where('event_name',$req->event_name)->first();
        $unique_id = str::random(15);
        $gallery = new Gallery;
        $gallery->event_id          = $getLastEvent->id;
        $gallery->gallery_name      = $req->event_name;
        
        $new = strtotime($getLastEvent->created_at); 
                                
        $gallery_date = date('Y-m-d',$new);
       
        $gallery->gallery_date      = $gallery_date;
        
        $gallery->unique_id         = $unique_id;
        $gallery->save();
        // $notification = new Notifications;
        // $notification->notification_type  = "1";
        // $notification->event_id            = $getLastEvent->id;
        // $notification->event_name          = $req->event_name;
        // $notification->event_description   = $req->event_short_description;
        // $notification->event_date       = $req->event_date;
        // $notification->save();
        // $dj=Dj_Event::where('event_id',"0")->get();
        // if(!empty($dj)){
        //     foreach($dj as $d){
        //         $time = date('h:i A', strtotime($d['time']));
                
        //         Dj_Event::where('event_id','=',0)->orWhere('artist1',"!=","Select One")->update([
        //             'event_id'=>$last_id,'time'=>$time
        //           ]);
        //     }
        // }
        // $users = user_infos::get();
        // foreach($users as $user){
        //     $player = user_infos::where('user_id',$user['user_id'])->first();
        //     $player_id = $player->player_id;

        //     $message = "Get excited for an upcoming event ".$req->event_name." at 4rizon";
        //     $this->mobile_push_notification($message,$player_id);
        // }
        return redirect('/event_list')->with('success','Event Created Successfully!');
        }
    }
    function multiple_delete_event(Request $req){
   
        $type = "application/json";
        $checkbox = $req->input('checkbox',[]);
      //  $checkbox = $req->djId;
       
        if(!empty($checkbox)){
          
          foreach($checkbox as $cb){
          
          foreach($checkbox as $cb){
            $user = Event::where('id','=',$cb)->first();
            if(!empty($user)){
              $delete = Event::where('id',$cb)->delete();
      
            }
          }
        
        }
        
        return redirect('/event_list')->with('success','Event Deleted Successfully');
       
      }
      else{
        return redirect('/event_list')->with('success','No Record Selected');
      }
      
      }
    function payment(Request $req){
       
        $booking_data = Bookings::where('booking_id','=',$req->booking_id)->first();
        
        $transaction = Transaction::where('booking','=',$req->booking_id)->first();
        if(empty($transaction)){
            $event = new Transaction;
            $event->amount         = $req->price;
            $event->booking =$booking_data->booking_id;
            $event->user = $booking_data->user_id;
            $event->event = $booking_data->event_id;
            $event->save();
            Bookings::where('booking_id','=',$req->booking_id)->update([
                'payment_status'=>1
              ]);
            return redirect('/users_event_attend_list')->with('success','Payment Done');  
        }
        else{
            return redirect('/users_event_attend_list')->with('success','Payment Already Made');
        }
    }
    function update_event(Request $req){ 
        
        $event                             = Event::find($req->id);
        $event->event_name                 = $req->event_name;
        $event->pkg_price                 = $req->event_price;
        if(!empty($req->event_start_time)){
            $event->event_start_time                 = date('h:i A', strtotime($req->event_start_time));
        }
        if(!empty($req->event_end_time)){
            $event->event_end_time                 = date('h:i A', strtotime($req->event_end_time));
        }
        $event->event_short_description    = $req->event_short_description;
        $event->event_description          = $req->event_description   ;
        if ($req->hasFile('event_image')) {
            $eventPic             = time().'.'.$req->event_image->extension();  
            $req->event_image->move(public_path('image'), $eventPic);
            $event->event_image = $eventPic;
        }
        $event->event_date = $req->event_date;
        $event->stage_1  = $req->stage_1;
        $event->stage_2  = $req->stage_2;
        $event->stage_3  = $req->stage_3;
        $event->special  = $req->special;
        $event->save();
        
        $all_dj = Dj_Event::where('event_id',$req->id)->get();
        foreach($all_dj as $ad){
            $user1 = DjUser::where('id',$ad['artist1'])->first();
            $user2 = DjUser::where('id',$ad['artist2'])->first();
            $user3 = DjUser::where('id',$ad['artist3'])->first();
            $messages = "You have been assigned a new event".$req->event_name;
            if(!empty($user1)){
                $this->mobile_push_notificationdj($messages,$user1->device_id);
            }
            if(!empty($user2)){
                $this->mobile_push_notificationdj($messages,$user2->device_id);
            }
            if(!empty($user3)){
                $this->mobile_push_notificationdj($messages,$user3->device_id);
            }    
        }
        $event_id = $req->id;
        return redirect('/edit_event/'.$event_id)->with('success','Event Details Updated Successfully!');
    }
    public function dj_time_allocation(Request $req){
        
        $dj_time = new Dj_Event;
       
        $dj_time->event_id = $req->id;
        $dj_time->artist1 = $req->artist1;
        $dj_time->artist2 = $req->artist2;
        $dj_time->artist3 = $req->artist3;
        $dj_time->time    = $req->time;
        
        $dj_time->save();
        
       
        $info = Dj_Event::where('event_id',$req->id)->get();
        $data1 = [];
        $data = [];
        foreach($info as $infos){
        $artist1 = DjUser::where('id',$infos['artist1'])->first();
        $data1['artist1'] = $artist1->first_name;
        $artist2 = DjUser::where('id',$infos['artist2'])->first();
        $data1['artist2'] = $artist1->first_name;
        $artist3 = DjUser::where('id',$infos['artist3'])->first();
        $data1['artist3'] = $artist1->first_name;
        $data1['time'] = $infos['time'];
        $data1['status'] = $infos['going_status'];
        array_push($data,$data1);
        }
       
        
        return response()->json($data);
    }
    public function delete_event ($id) {
        if(Event::where('id', $id)->exists()) {
            $event_name = Event::where('id',$id)->first();
            $bookings = Bookings::where('event_id',$id)->get();
            foreach($bookings as $booking){
                $user_id = $booking['user_id'];
                $dj_id = $booking['dj_id'];
                if(!empty($user_id)){
                    $player_id = user_infos::where('id',$user_id)->pluck('player_id'); 
                    $message = $event_name->event_name."on".$event_name->event_date."is cancelled";
                    $this->mobile_push_notification($message,$player_id);
                }
                if(!empty($dj_id)){
                    $player_id = DjUser::where('id',$dj_id)->pluck('device_id'); 
                    $message = $event_name->event_name."on".$event_name->event_date."is cancelled";
                    $this->mobile_push_notification($message,$player_id);
                }
            }

            $delete_bookings = Bookings::where('event_id',$id)->delete();
            $delete_guests = Guest::where('event_id',$id)->delete();
            $delete_dj = Dj_Event::where('event_id',$id)->delete();
            $event= Event::where('id', $id)->delete();
            return redirect('/event_list')->with('success','Event Details Deleted Successfully!');
        }else{
            return redirect('/event_list')->with('error','Event Not Found');
        }
    }
    // api's
    function user_event_attend(Request $req){
        $check = Bookings::where('event_id',$req->event_id)->where('user_id',$req->user_id)->first();
        if($check->status == "attended"){
            return response()->json(['message' => "User Already Visited", 'error' => true,'code'=>'201'], 201);
        }else{
            $return_code = str::random(30);
            $check->exit_code             = $return_code;
            $check->status                = "attended";
            $check->save();
            return response()->json(['message' => "User Enters In The Pub",'exit_code' => $return_code, 'success' => true], 200);
        }
    }
    function user_going_to_event(Request $req){
        $check = Bookings::where('event_id',$req->event_id)->where('user_id',$req->user_id)->where('going_status',$req->status)->first();
        if (!empty($check)) {
            return response()->json(['message' => "Already Going",'error' => true,'code'=>'201'], 201);
        }else{
            Bookings::where('event_id',$req->event_id)->where('user_id',$req->user_id)->update([
                'going_status'=> 1
              ]);
            // $return_code = str::random(30);
            // $eventDetails = Event::find($req->event_id);
            // $event = new Bookings;
            // $event->booking_type   = "1";
            // $event->event_id       = $req->event_id;
            // $event->user_id        = $req->user_id;
            // $event->status         = $req->status;
            // $event->going_status   = $req->status;
            // $event->booking_id   = $return_code;

            // $event->save();
            // $userFind = user_infos::where('user_id',$req->user_id)->first();
            // $messages = "Event Booking Created";
            // $this->mobile_push_notification($messages,$userFind->player_id);
            return response()->json(['message' => "User Going status marked", 'success' => true], 200);
        }
        
    }
    function create_qr_code_event(Request $req){
       
        $check = Bookings::where('event_id',$req->event_id)->where('user_id',$req->user_id)->where('status','Qr Code Created')->first();
        $event_expiry = Event::where('id',$req->event_id)->first();
        $expiry_time = date("G:i:s", strtotime($event_expiry->event_end_time));
        $expiry = $event_expiry->event_date.' '.$expiry_time;
        // echo json_encode( $expiry);die();
        if(!empty($check)){
            return response()->json(['message' => "Qr Code Already Created",'qr_code' => $check->booking_id,'qr_code_expires_at'=>$check->qr_code_expires_at, 'error' => true,'code'=>'201'], 201);
        }else{
            $return_code = str::random(30);
            // $tomorrow = date("Y-m-d H:i:s", strtotime('+1 day'));
            $event = new Bookings;
            $event->booking_type          = "1";
            $event->event_id              = $req->event_id;
            $event->user_id               = $req->user_id;
            $event->booking_id            = $return_code;
            $event->status                = "Qr Code Created";
            $event->qr_code_expires_at    = $expiry;
            $event->save();
            
            return response()->json(['message' => "Qr Code Created",'qr_code' => $return_code,'qr_code_expires_at'=>$expiry, 'success' => true], 200);
        }
    }
    function user_event_exit(Request $req){
        $event          = Bookings::where('exit_code',$req->code)->first();
        $event->exit_at = date("Y-m-d H:i:s");;
        $event->status  = "exit";
        $event->save();
        return response()->json(['message' => "User Exits From The Pub", 'success' => true], 200);
    }
      function invitation_status(Request $req){
        $result = json_decode(file_get_contents("php://input"), true);
        $status = $result['status'];
        $user_id = $result['user_id'];
        $booking_id = $result['booking_id'];
        $host = Guest::where('booking_id','=',$booking_id)->where('user_id','=',$user_id)->first();
        $host_id = $host->host_id;

        if($status == 1){
            Guest::where('booking_id','=',$booking_id)->where('user_id','=',$user_id)->update([
                'status'=> $status
              ]);
              $userFind = user_infos::where('user_id',$user_id)->first();
              $name = $userFind->first_name;
              $message = $name." has rejected your invitation";
              $hostFind = user_infos::where('user_id',$host_id)->first();
              if($hostFind->player_id != null){
                $this->mobile_push_notification($message,$hostFind->player_id);
              }
        }
         if($status == 2){
            Guest::where('booking_id','=',$booking_id)->where('user_id','=',$user_id)->update([
                'status'=> $status
              ]);
              $userFind = user_infos::where('user_id',$user_id)->first();
              $name = $userFind->first_name;
              $message = $name." has accepted your invitation";
              $hostFind = user_infos::where('user_id',$host_id)->first();
              if($hostFind->player_id != null){
            
                $this->mobile_push_notification($message,$hostFind->player_id);
              }
             
        }
        return response()->json(['message' => "status updated", 'success' => true], 200);
    }
    function event_list_api($id){
        $date = \Carbon\Carbon::today();
        $event_data2 = Event::where('event_date','>=',$date)->orderBy('event_date','DESC')->get();
        $eve = Event::where('events.event_date','>=',$date)->orderBy('event_date','DESC')->get();
           
        $event_data = array();
       foreach($eve as $e){
        $event_idd = $e['id'];
        $going = Bookings::where('event_id',$event_idd)->where('going_status',1)->get();
       
        $count =  count($going);
        $e['user_going_count'] = $count;
       
        array_push($event_data,$e);
       }
  
        $bookings   = Bookings::where('user_id',$id)->get();
        $bookings1 = Guest::where('user_id',$id)->orWhere('host_id',$id)->orderBy('guest_id','DESC')->first();
        // $bookings1 = Guest::where('host_id',$id)->orderBy('guest_id','DESC')->first();
    
        if(!empty($bookings1)){
            
            $bookingsRecord = Guest::where('booking_id',$bookings1->booking_id)->get();
            $status = $bookings1->status;
            $bookingsVip   = Bookings::where('booking_id',$bookings1->booking_id)->get();
            foreach($bookingsVip as $vip){
                // $guest = $vip['guest_lists'];
                $vip['guest_list_status'] = $bookingsRecord;
                // echo json_encode($vip);die();
            }
        }
       
        // echo json_encode($bookings);die();
        if($bookings == []){
        
            $merged = $bookings->merge($bookingsVip);
      
            $result = $merged->all();
        
            return response()->json(['event_list' =>$event_data,'booking_list' =>$result,'image_url'=>'https://admin.4rizon.com/image/', 'success' => true], 200);
        }
        else{
            
            return response()->json(['event_list' =>$event_data,'booking_list' =>$bookings,'image_url'=>'https://admin.4rizon.com/image/', 'success' => true], 200);
        }
        return response()->json(['event_list' =>$event_data,'booking_list' =>[],'image_url'=>'https://admin.4rizon.com/image/', 'success' => true], 200);
    }
    function single_event_api($id){
        $event = Event::find($id);
         return response()->json(['event' =>$event,'image_url'=>'https://admin.4rizon.com/image/', 'success' => true], 200);
    }
    function get_bookings($id){
      
        $bookings = Bookings::where('user_id',$id)->get();
        return response()->json(['bookings' =>[$bookings],'success' => true], 200);
    }
    function remove_booking(Request $req){
        $bookings = Bookings::where('id', $req->id)->delete();
        return response()->json(['message' =>"Booking Removed Successfully",'success' => true], 200);
    }
    
    /** DJ Mobile Push Notification **/
    public function mobile_push_notificationdj($message='', $player_id=''){
		/* SEND NOTIFICATION */
		$content = array(
			"en" => $message
			);
		$fields = array(
            
			'app_id' => "346d914e-58bb-407c-875e-e9202378bf8a",
			// 'app_id' => "9b212888-74e1-4626-b188-732bcd1f897b",
			'include_player_ids' => array($player_id),
			'data' => array("noti_type" => "order_update"),
			'contents' => $content
		);
		
		$fields = json_encode($fields);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
		$return["allresponses"] = $response;
		$return = json_encode($return);
		/* SEND NOTIFICATION */
		if(!empty($return)){
			return true;
		}
	}
    
    /** Mobile Push Notification Function **/
	public function mobile_push_notification($message='', $player_id=''){
		/* SEND NOTIFICATION */
		$content = array(
			"en" => $message
			);
		$fields = array(
        
			'app_id' => "e3ead764-83f0-45b2-832e-7b4aa851e4f4",
			'include_player_ids' => array($player_id),
			'data' => array("noti_type" => "order_update"),
			'contents' => $content
		);
		
		$fields = json_encode($fields);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
		$return["allresponses"] = $response;
		$return = json_encode($return);
		/* SEND NOTIFICATION */
		if(!empty($return)){
			return true;
		}
	}
    public function showData(Request $request)
    {
        
        $event_id = $request->event_id;
     
        // $data = array();
        $count = count($request->time);
   $time = array();
   $artist1 = array();
   $artist2 = array();
   $artist3 = array();
        for($i=0; $i<$count; $i++){
          
            $t = $request->time[$i];
            $a1 = $request->artist1[$i];
            $a2 = $request->artist2[$i];
            $a3 = $request->artist3[$i];
              $old_data = Dj_Event::where('event_id',$event_id)->where('time',$t)->first();
              
              if($old_data == ""){
                
                $djevent = new Dj_Event;
                $djevent->time = $t;
                $djevent->event_id = $event_id;
                $djevent->save();
                if($a1 != null){
                    if($a1 != $a2 && $a1 != $a3){
                        $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist1' => $a1,'going_status1' => 0]);
                    }
                   
                }
                if($a2 != null){
                    if($a1 != $a2 && $a2 != $a3){
                        $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist2' => $a2 ,'going_status2' => 0]);
                    }
                }
               
                if($a1 != $a3 && $a2 != $a3){
                    $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist3' => $a3 ,'going_status3' => 0]);
                
                }
              }
              else{
                    if($a1 != null){
                        if($old_data['artist1'] != $a1){
                            if($a1 != $a2 && $a1 != $a3){
                                $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist1' => $a1,'going_status1' => 0]);
                            }
                        }
                    }
                    if($a2 != null){
                        if($old_data['artist2'] != $a2){
                            if($a1 != $a2 && $a2 != $a3){
                                $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist2' => $a2 ,'going_status2' => 0]);
                            }
                        }
                    }
                    if($a2 != null){
                        if($old_data['artist3'] != $a3){
                            if($a1 != $a3 && $a2 != $a3){
                                $update1 = Dj_Event::where('event_id',$event_id)->where('time',$t)->update(['artist3' => $a3 ,'going_status3' => 0]);
                            
                            } 
                        }
                    }
          
          
                 }
       
           
                }
            
            // $delete = Dj_Event::where('event_id',$event_id)->delete();
        
           
            // $status = Dj_Event::where('event_id',$event_id)->get();
            // foreach($status as $s){
            //     $id = $s['id'];
            //     if($s['artist1'] != null){
            //         $update = Dj_Event::where('id','=',$id)->update(['going_status1' => 0]);
            //     }
            //     if($s['artist2'] != null){
            //         $update = Dj_Event::where('id','=',$id)->update(['going_status2' => 0]);
            //     }
            //     if($s['artist3'] != null){
            //         $update = Dj_Event::where('id','=',$id)->update(['going_status3' => 0]);
            //     }
            // }
        
            return response()->json($request);
        }
    
}
