<?php
namespace App\Traits;

use App\Models\Notification;
use App\Models\NotificationUser;
use Carbon\Carbon;
use Auth;

trait NotificationTrait
{
    public function insertNotification($data)
    {
        $notificaiton = new Notification();
        $notificaiton->id = $data['notification_id']; 
        $notificaiton->notification_type = $data['notification_type'];
        $notificaiton->action_type = $data['action_type'];
        $notificaiton->title = $data['title'];
        $notificaiton->description = $data['description'];
        $notificaiton->url = $data['url'];
        $notificaiton->data = $data['data'];
        $notificaiton->save();

        $notificaiton_user = new NotificationUser();
        $notificaiton_user->notification_id = $data['notification_id'];
        $notificaiton_user->user_id = $data['user_id'];
        $notificaiton_user->save();
    }

    public function readNotification($notification){
        $user = Auth::user();
        if($user){
            $userUnreadNotification = NotificationUser::where('notification_id',$notification)->where('user_id',$user->id)->first();
            if($userUnreadNotification) {  
               if ($userUnreadNotification->read_at == '') {
                   $userUnreadNotification->read_at = Carbon::now();
                   $userUnreadNotification->save();
               } 
            }
        }
    }

}