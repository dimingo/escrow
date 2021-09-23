<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Tdetails;
use App\User;
use App\Deliveries;

use Log;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $arr['transactions'] = Tdetails::where('void','=',0) 
                                        ->get();

        $users = User::all();
        $deliveries = Tdetails::where('delivered','=',1)->get();
        
        return view('home', compact('users', 'deliveries'))->with($arr);
    }

    /**
     * Testing Things
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function testsl()
    {
        $arr = array('numbers' => '0797740687', 'message' => 'Message From Ujumbe', 'sender' =>'DEPTHSMS');
        $message_bag = array('message_bag' => $arr);
        $data = array('data'=> [$message_bag]);

        $url = 'https://ujumbesms.co.ke/api/messaging';
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                    CURLOPT_URL => $url,
                    CURLOPT_HTTPHEADER => array('X-Authorization: MDE4MzA3N2E1YjQ3YTBmZjZlOWRhMzk5ZDllM2Rk', 'Email:abraham.nyabera@msimboit.net'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_SSL_VERIFYHOST => FALSE,
                    CURLOPT_SSL_VERIFYPEER => FALSE
                )
        );
        $curl_response = curl_exec($curl);
        curl_close($curl);
        $res = $curl_response;
        dd(curl_error($res));
        return $res;

        // $data = json_encode($data);
        // $response = Http::withHeaders([
        //     'X-Authorization' => 'MDE4MzA3N2E1YjQ3YTBmZjZlOWRhMzk5ZDllM2Rk',
        //     'Email' => 'abraham.nyabera@msimboit.net'
        // ])->post('http://ujumbesms.co.ke/api/messaging', [$data]);

        // dd($response);
    }

    public function tests()
    {
        $this->send("0797740687", "The message to send from escrow", "DEPTHSMS");
        dd("completed");

    }


    /**
     * From Ujumbe
     */
    public function prepare($data)
    {

        $data = [
            "data" => [[
                "message_bag" => [
                    "numbers" => $data['numbers'],
                    "message" => $data['message'],
                    "sender" => $data['sender'],
                ]
            ]]
        ];

        Log::info($data);

        $sms_data = json_encode($data);
        $url = 'https://ujumbesms.co.ke/api/messaging';

        Log::info($sms_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $sms_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($sms_data),
            'X-Authorization: ZTQ1ZGIwNjFkMGFhZDQyOTQ3OTBmYTYyMGJlYjYy',
            'email: abraham.nyabera@msimboit.net'
        ));


        $response = curl_exec($curl);
        Log::info($response);

        if ($response === false) {
            $err = 'Curl error: ' . curl_error($curl);
            curl_close($curl);
            Log::info($err);
            return $err;
        } else {
            curl_close($curl);
            Log::info($response);
            return $response;
        }
    }

    /**
     * @param $numbers = phone numbers separated with commas
     * @param $message
     * @param $sender = The sender id assigned by ujumbeSMS eg DepthSMS
     */
    public function send($numbers, $message, $sender)
    {

        $data = [
            "numbers" => $numbers,
            "message" => $message,
            "sender" => $sender
        ];
        $this->prepare($data);

    }
}
