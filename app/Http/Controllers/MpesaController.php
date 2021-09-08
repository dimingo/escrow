<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DateTime;

use Log;
use DB;

use App\Clients;
use App\User;
use App\Tdetails;
use App\Vendors;
use App\Payments;
use App\mpesa_token;

class MpesaController extends Controller
{
    /**
     * Lipa na M-PESA password
     * */
    public function lipaNaMpesaPassword(){

        $lipa_time              = Carbon::rawParse('now')->format('YmdHms');
        $passkey                = '9f202c6227ba12753d83b4cac5f376ca01ccce8556c4fb65b2e1050dc770848e';
        // $BusinessShortCode      = 174379;
        $BusinessShortCode = 4051259;
        $timestamp              = $lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode.$passkey.$timestamp);

        return $lipa_na_mpesa_password;
    }
    /**
     * Lipa na M-PESA STK Push 
     * 
     * @return [curl] response
     *
     **/
    // public function customerMpesaSTKPush(){

    //     $phone_number = 254713374606;
    //     $amount = 1;

    //     $url    = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        

    //     $curl   = curl_init();

    //     curl_setopt( $curl, CURLOPT_URL, $url );
    //     curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json','Authorization:Bearer '.$this->generateAccessToken()) );
        
    //     Log:info($this->generateAccessToken());

    //     $curl_post_data = [

    //         //Fill in the request parameters with valid values
            
    //         'BusinessShortCode' => 174379,
    //         'Password'          => $this->lipaNaMpesaPassword(),
    //         'Timestamp'         => Carbon::rawParse('now')->format('YmdHms'),
    //         'TransactionType'   => 'CustomerPayBillOnline',
    //         'Amount'            => 1,
    //         'PartyA'            => $phone_number, // replace this with your phone number
    //         'PartyB'            => 174379,
    //         'PhoneNumber'       => $phone_number, // replace this with your phone number
    //         'CallBackURL'       => 'https://c52e5ec177a8.ngrok.io/api/mpesa_response',
    //         'AccountReference'  => $phone_number,
    //         'TransactionDesc'   => "Testing stk push on sandbox"
    //     ];
        
    //     $data_string = json_encode($curl_post_data);

    //     Log::info($data_string . 'Data String');

    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_POST, true);
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        
    //     Log::info($curl. 'Curl');
    //     $curl_response = curl_exec($curl);
        
    //     Log::info($curl_response . 'Response');
    //     return $curl_response;
    // }




    public function customerMpesaSTKPush($phone_number, $amount, $trans_id){
        // $phone_number = 254700682679;
        // $amount = 1;
        // $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $curl = curl_init();
        
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json','Authorization:Bearer '.$this->generateAccessToken()) );
        
        $curl_post_data = [
        
        //Fill in the request parameters with valid values
        'BusinessShortCode' => 4051259,
        'Password' => $this->lipaNaMpesaPassword(),
        'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => 1,
        'PartyA' => $phone_number, // replace this with your phone number
        'PartyB' => 4051259,
        'PhoneNumber' => $phone_number, // replace this with your phone number
        'CallBackURL' => 'https://supamallescrow.com/v1/escrow/transaction/confirmation',
        'AccountReference' => $trans_id,
        'TransactionDesc' => "Testing stk push on sandbox"
        ];
        
        $data_string = json_encode($curl_post_data);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        
        $curl_response = curl_exec($curl);
        Log::info($curl_response);
        return $curl_response;
        }





    /**
     * Generate an access token for every stk push
     * 
     * return [string] access_token
     */

    public function generateAccessToken(){
        Log::info('generateAccessToken function reached');
        $consumer_key = env('MPESA_CONSUMER_KEY', '');
        $consumer_secret = env('MPESA_CONSUMER_SECRET', '');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);

        $url    = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        // $url ='https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        // $curl_info = curl_getinfo($curl);
        Log::info('curl response is: '. $curl_response);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $access_token = json_decode($curl_response);

        $token= $access_token->access_token;
        Log::info('generateAccessToken generated: '.$token);
   
    return $token;
}



    // Access Token Alternative
    


    // public function generateAccessToken(){


    //     $token_m=DB::table('mpesa_tokens')->limit(1)->get();

    //     if($token_m) {
    //         foreach ($token_m as $x) {
    //             $token = $x->access_token;
    //        }

    //     }
    //     else {


    //         $consumer_key = 'oeazFs2H1ywGrmGAw0FqUzEOrHyPVzVw';
    //         $consumer_secret = 'DxpO6qlcsKMABZK8';
    //         $credentials = base64_encode($consumer_key . ":" . $consumer_secret);

    //         $url    = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
            

    //         $url = "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    //         $curl = curl_init();

    //         curl_setopt($curl, CURLOPT_URL, $url);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
    //         curl_setopt($curl, CURLOPT_HEADER, false);
    //         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //         $curl_response = curl_exec($curl);
    //         //$curl_info = curl_getinfo($curl);
    //         $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //         $access_token = json_decode($curl_response);
    //         // Log::info($access_token);
    //         $token= $access_token->access_token;
    //         Log::info('URL fxn tkn '.$token);
    //     }
    //     return $token;
    // }

    /**
     * Mpesa callback, gives response which is then stored in the DB
     */
    public function mpesaCallback( Request $request ){
        Log::info('Callback function engaged');

        if ($request['ResultCode'] == '1032') {
            Log::info('User cancelled Mpesa STK Push Request');
        }else{
            Log::info('Callback Request came through');
            Log::info($request->all());

        $reciept_number     = $request['Body']['stkCallback']['CallbackMetadata']['Item'][1]['Value'];
        Log::info( $reciept_number );
        $transaction_date   = $request['Body']['stkCallback']['CallbackMetadata']['Item'][3]['Value'];
        Log::info( $transaction_date );
        $phone_number       = $request['Body']['stkCallback']['CallbackMetadata']['Item'][4]['Value'];
        Log::info( $phone_number );
        
        if (User::where('phone_number', '=', $phone_number)->exists()) {
            // $mpesa_response = DB::table('users')
            //                     ->where('phone_number', $phone_number )
            //                     ->update([
            //                         'mpesa_transaction_codes' => $reciept_number,
            //                         'mpesa_transaction_dates' => $transaction_date,                                  
            //                     ]);
        
        
        $message = "Mpesa Payment Received Successfully.";
        
        $this->sendBulkSMS($phone_number, $message);

        return $request;

         }else{

            // $mpesa_response = DB::table('users')
            //                     ->where('phone_number', $phone_number )
            //                     ->update([
            //                         'mpesa_transaction_codes' => $reciept_number,
            //                         'mpesa_transaction_dates' => $transaction_date,
            //                         'eligible'             => true,
            //                     ]);
        
        
        $message = "Mpesa Payment Succesfully Received.";              
        $this->sendBulkSMS( $phone_number, $message );

        return $request;
         }
        }
        
    }

    public function confirmationMpesa( Request $request ){
        // Log::info(implode( ",", $request->all() ));
        Log::info('Next Confirmation Log: ');
        Log::info($request->all());
        $receipt_number = $request['TransID'];
        $transaction_date = $request['TransTime'];
        $firstName = $request['FirstName'];
        $lastName = $request['LastName'];
        $middleName = $request['MiddleName'];
        $amount = $request['TransAmount'];
        $status = $request['failed'];
        $name = $firstName." ".$middleName." ".$lastName;
        //$phone_acc = $request['BillRefNumber'];
        $trans_id = $request['BillRefNumber'];
        $phone_number = $request['MSISDN'];

        Log::info($trans_id);
        $user = DB::table('users')->where('phone_number','=',$phone_number)->first();

        // $resultCode = $request['Body']['stkCallback']['ResultCode'];

        // $date = strtotime(strval($transaction_date));
        // $dateFormat = date('Y-m-d H:i:s',$date);

        $trans_date = new DateTime($transaction_date);

        // $user_latest_payment = DB::table('payments')
        //                         ->where('phoneno', $phone_number)
        //                         ->orderBy('id', 'DESC')
        //                         ->first();

        // $user_latest_payment = Payments::where('transactioncode','=', $trans_id)
        //                                 ->first();

        // Log::info('User Payment Details: '.$user_latest_payment);

        // $user_payment_time = new DateTime($user_latest_payment->created_at);

        
        //Check if customer paid successfully
        if($receipt_number == ''|| $receipt_number == null ) {
            Log::info('User either cancelled Mpesa STK Push Request or has insufficient funds');
        }else{

            //Check whether it's paybill or stk push
            if($phone_number){
                    $update_mpesa_code = DB::table('payments')
                                        ->where('transactioncode', $trans_id)
                                        ->update([
                                            'mpesacode' => $receipt_number,
                                            'amount_paid' => $amount
                                        ]);

                    $update_paid_status = DB::table('tdetails')
                    ->where('id', $trans_id)
                    ->update([
                        'paid' => 1,
                        'transactioncode' => $receipt_number,
                    ]);
                }
                else{
                    //Paybill  Details
                }

                // $message = "Mpesa Payment Succesfully Received.";              
                // $this->sendBulkSMS( $phone_number, $message );                        

                return response()->json([
                        'ResultCode' => 0,
                        'ResultDesc' => "Accepted",
                        ]);                  
            }
        
        /*BidLeo Checks past this point*/    

        //Check if customer paid successfully
        // if($resultCode != 0 ) {
        //     Log::info('User either cancelled Mpesa STK Push Request or has insufficient funds');
        // }else{

        //     //Check whether it's paybill or stk push
        //     if($phone_number == $phone_acc){
        //         //STK Push
        //         $users_bids = DB::table('bid')->where('phone_number', $phone_number)->get();

        //         foreach($users_bids as $bids){
        //             if($bid->created_at->diffInSeconds($dateFormat) < 30 ){
        //                 $update_bid_status = DB::table('bid')
        //                             ->where('bid_unique_id', $bid->bid_unique_id)                                
        //                             ->update([
        //                                 'bid_status' => 'active',
        //                                 'mpesa_reciept' => $reciept_number,
        //                                 'mpesa_payment_date' => $dateFormat
        //                             ]);

        //                 $update_rewards = DB::table('users')
        //                                     ->where('phone_number', '=',$phone_number)
        //                                     ->update([
        //                                         'name' => $firstName.' '.$middleName.' '.$lastName,
        //                                         'rewards_wallet' => $this_user->rewards_wallet + 5
        //                                     ]);            
        //             }                
    
        //         }
                
        //         //Check If User Is Eligible
        //         if($specific_user != null && $specific_user->eligible  == false){
        //             //User Is Not Eligible but has now paid
        //             $update_user = DB::table('users')
        //             ->where('phone_number', $phone_number )
        //             ->update([
        //                 'name'      => $name,                                    
        //                 'eligible'  => true,
        //             ]);
        //         }else{

        //             $update_user = DB::table('users')
        //             ->where('phone_number', $phone_number )
        //             ->update([
        //                 'name'      => $name,                                                           
        //             ]);
        //         }
               

        //     }else{
        //         //Paybill                

        //         if(stripos($phone_acc, '-')){
        //             $phone_acc = str_replace("-", " ", $phone_acc);
        //         }

        //         $bidder_unique_id= substr( bin2hex( random_bytes( 12 ) ),  0, 12 );

        //         $password = Crypt::encrypt($phone_number. substr( bin2hex( random_bytes( 4 ) ),  0, 4 ));  

        //         if (stripos($phone_acc, 'BIKE') !== false) { //Case insensitive
        //             $item = DB::table('item')->where('item_category','=','BIKE')->where('status','=','available')->first();
        //         }else if(stripos($phone_acc, 'PHONE') !== false){
        //             $item = DB::table('item')->where('item_category','=','PHONE')->where('status','=','available')->first();
        //         }else if(stripos($phone_acc, 'SHOP') !== false){
        //             $item = DB::table('item')->where('item_category','=','VOUCHER')->where('status','=','available')->first();
        //         }

        //         if(stripos($phone_acc, 'MCJ') !== false){
        //             $affiliate = 'mcj';
        //         }else if(stripos($phone_acc, 'GHETTO') !== false){
        //             $affiliate = 'ghetto'; 
        //         }else if(stripos($phone_acc, 'TEN') !== false){
        //             $affiliate = 'ten';
        //         }else if(stripos($phone_acc, 'BAK') !== false){
        //             $affiliate = 'bak';
        //         }else if(stripos($phone_acc, 'JAMBO') !== false){
        //             $affiliate = 'jambo';
        //         }else{
        //             $affiliate = 'bidleo';
        //         }
                
        //         $bid_amount = (int) filter_var($phone_acc, FILTER_SANITIZE_NUMBER_INT);
        //         $bid_amount = round($bid_amount);
        //         $bid_unique_id = $item->item_category. substr( bin2hex( random_bytes( 6 ) ),  0, 6 );

        //         //Check for existing user
               
        //         if ($specific_user === null && $amount == '20') {
        //             //user doesn't exist

        //             if((int)$amount > 20){
        //                 $paybill_balance = (int)$amount - 20;

        //                 $new_user = new User([
        //                     'phone_number' => $phone_number,
        //                     'password' => $password,
        //                     'bidder_unique_id' => $bidder_unique_id,
        //                     'role' => 'bidder',
        //                     'rewards_wallet' => 5,
        //                     'paybill_balance' => $paybill_balance,
        //                     'eligible' => true,           
        //                 ]);
    
        //             } else{
        //                 $new_user = new User([
        //                     'phone_number' => $phone_number,
        //                     'password' => $password,
        //                     'bidder_unique_id' => $bidder_unique_id,
        //                     'role' => 'bidder',
        //                     'rewards_wallet' => 5,
        //                     'eligible' => true,           
        //                 ]);
        //             }
            
        //             $new_user->save();
            
        //             $new_bid = new Bid([
        //                     'auction_id' => $item->auction_id,
        //                     'bid_amount' =>$bid_amount,
        //                     'item_name' => $item->item_name,
        //                     'bidder' => $phone_number,
        //                     'bidder_name' => $firstName.' '.$middleName.' '.$lastName,
        //                     'affiliate' => $affiliate,
        //                     'bid_unique_id' => $bid_unique_id,
        //                     'bid_start_time' => Carbon::now(),
        //                     'item_unique_id' => $item->item_unique_id,
        //                     'item_category' => $item->item_category,
        //                     'bid_status' => 'active',
        //                     'mpesa_reciept' => $reciept_number,
        //                     'mpesa_payment_date' => $dateFormat
        //                 ]);
            
        //             $new_bid->save();

        //             $auction_id = $item->auction_id;

        //             $this->updateAuction($phone_number, $bid_amount, $auction_id);
        //         }else{

        //             if((int)$amount > 20){
        //                 $paybill_balance = (int)$amount - 20;
    
        //                 $update_paybill = DB::table('users')
        //                 ->where('phone_number', '=',$phone_number)
        //                 ->update([
        //                     'paybill_balance' => $this_user->paybill_balance + $paybill_balance
        //                 ]); 
        //             }                   

        //             $update_rewards = DB::table('users')
        //             ->where('phone_number', '=',$phone_number)
        //             ->update([
        //                 'rewards_wallet' => $this_user->rewards_wallet + 5
        //             ]); 

        //             $new_bid = new Bid([
        //                 'auction_id' => $item->auction_id,
        //                 'bid_amount' =>$bid_amount,
        //                 'item_name' => $item->item_name,
        //                 'bidder' => $phone_number,
        //                 'affiliate' => $affiliate,
        //                 'bid_unique_id' => $bid_unique_id,
        //                 'bid_start_time' => Carbon::now(),
        //                 'item_unique_id' => $item->item_unique_id,
        //                 'item_category' => $item->item_category,
        //                 'bid_status' => 'active',
        //                 'mpesa_reciept' => $reciept_number
        //             ]);
        
        //         $new_bid->save();

        //         $auction_id = $item->auction_id;

        //         $this->updateAuction($phone_number, $bid_amount, $auction_id);
        //         }

        //     }    

        // $message = "Mpesa Payment Succesfully Received.";              
        // $this->sendBulkSMS( $phone_number, $message );                        

        // return response()->json([
        //         'ResultCode' => 0,
        //         'ResultDesc' => "Accepted",
        //         ]);       
        //     }                         
                   
    }

    public function validationMpesa( Request $request ){
        Log::info( "validation mpesa" . $request );
    }

    public function registerMpesaUrls(){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization: Bearer '. $this->generateAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'ShortCode' => "600141",
            'ResponseType' => 'Completed',
            'ConfirmationURL' => "https://c52e5ec177a8.ngrok.io/api/confirmation",
            'ValidationURL' => "https://c52e5ec177a8.ngrok.io/api/validation"
        )));
        $curl_response = curl_exec($curl);
        echo $curl_response;
    }

    /**
     * Send confirmation SMS when user has paid
     */
    public function sendConfirmationSMS( $phone_number, $message, $offerCode, $cpId, $linkId ){
        
        $token = $this->GenerateToken();
        Log::info( $token );
        $phone = $phone_number;
        $message = $message;
        $url12 = 'https://dsvc.safaricom.com:9480/api/auth/login';
        $timeStamp =  date("YdmGis");
        $sms = 'API';
        $uniqueId = $phone.'_'.$timeStamp;
        $LinkId=$linkId;
        $OfferCode=$offerCode;
        $CpId=$cpId;
        $data='{
					                        "requestId":"'.$uniqueId.'",					                    					                
                                            "requestTimeStamp": "'.date("YdmGis").'", 
                                            "channel": "'.$sms.'",   
                                            "sourceAddress": "224.223.10.27",									                                      
                                            "requestParam": 
                                              {
                                                "data": 
                                                [
                                                {
                                                    "name": "LinkId",
                                                    "value":"'.$LinkId.'"
                                                  },
                                                  
                                                  {
                                                    "name": "OfferCode",
                                                    "value":"'.$OfferCode.'"
                                                  },
                                                  {
                                                    "name": "Msisdn",
                                                    "value": "'.$phone.'"
                                                  },
                                                    {
                                                    "name": "Content",
                                                        "value": "'.$message.'"
                                                      },
                                                  {
                                                    "name": "CpId",
                                                    "value":"'.$CpId.'"
                                                  }
                                                ]
                                              },
                                              "operation": "SendSMS"
                                            }';

                                    $data_strings12=$data;
                                    Log::info('req '.$data_strings12);
                                    $ch = curl_init($url12);

                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_strings12);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                            'Content-Type: application/json',
                                            'X-Requested-With: XMLHttpRequest',
                                            'X-Authorization:Bearer ' . $token
                                        )
                                    );

                                    $results12 = curl_exec($ch);
                                    Log::info( $results12 );
                                    
    }

    public function GenerateToken(){
        try {
            $username = "amwaytech_apiuser";
            $password = "AMWAYTECH_APIUSER@ps2545";

            $url12 = "https://dsvc.safaricom.com:9480/api/auth/login";

            $data12 = array(
                "username" => $username,
                "password" => $password
            );

            $data_strings12 = json_encode($data12);
            $ch = curl_init($url12);

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_strings12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'X-Requested-With: XMLHttpRequest'
                )
            );
            $results12 = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $objs12 = json_decode($results12,true);
            $token=$objs12['token'];
            Log::info('token '.$token);
            $refreshToken = $objs12['refreshToken'];


        }
        catch(\Exception $e)
        {
            $token=$e->getMessage();
        }

        $temporary_token = new mpesa_token([
            'access_token' => $token,                      
        ]);

        $temporary_token->save();

        return $token;
    }

    public function parsePhoneNumber($phone_number){
        $number = "";

        if(strlen($phone_number) == 12){ //254722000000

            $number = substr($phone_number,3,11);

        }else if(strlen($phone_number) == 10){ //0722000000
            $number = substr($phone_number,1,9);

        } else if(strlen($phone_number) == 9){ //722000000
            $number = $phone_number;

        } 

        return $number;

    }





    public function transactionpayment( Request $request ){
        if($request->itemCheckbox == null || $request->itemCheckbox == '')
        {
            //$sum = array_sum($request->itemCheckbox);
            $sum = $request->subtotal;
        

            // dd($request->all());

            $values = $request->except(['_token']);
            $phone_number = $request->clientNumber; 

            if($request->subtotal == $sum)
            {
                $amount = $request->total; 
                
                //dd('equal and total = '.$amount);

                $phone_number = substr($phone_number, -9);
                $phone_number = 254 . $phone_number;

                $trans_id = $request->orderId;
                
                $this->customerMpesaSTKPush($phone_number, $amount, $trans_id);
                
                Tdetails::where('id', '=', $values['orderId'])
                        ->update(['transactioncode' => 'mpesaCode']);
                    
                $transactioncode = $request->orderId;

                $pay = new Payments;
                $pay->transactioncode = $transactioncode;
                $pay->phoneno = $phone_number;
                $pay->mpesacode = '';
                $pay->amount_paid = 0;
                $pay->amount_due = 0;
                $pay->save();

                return redirect('/home');

            }

            if($request->total != $sum)
            {
                $tariff = 0;

                $total = $request->total;
                // $t = (intval($total) - ( intval($total) - intval($sum)));
                $t = $sum;

                if($t >= 1 && $t < 50)
                {
                    $tariff = 3;
                }

                if($t >= 50 && $t <= 100)
                {
                    $tariff = 13;
                }

                if($t >= 101 && $t <= 499)
                {
                    $tariff = 83;
                }

                if($t >= 500 && $t <= 999)
                {
                    $tariff = 89;
                }

                if($t >= 1000 && $t <= 1499)
                {
                    $tariff = 105;
                }

                if($t >= 1500 && $t <= 2499)
                {
                    $tariff = 110;
                }

                if($t >= 2500 && $t <= 3499)
                {
                    $tariff = 159;
                }

                if($t >= 3500 && $t <= 4999)
                {
                    $tariff = 181;
                }

                if($t >= 5000 && $t <= 7499)
                {
                    $tariff = 232;
                }

                if($t >= 7500 && $t <= 9999)
                {
                    $tariff = 265;
                }

                if($t >= 10000 && $t <= 14999)
                {
                    $tariff = 347;
                }

                if($t >= 15000 && $t <= 19999)
                {
                    $tariff = 370;
                }

                if($t >= 20000 && $t<= 24999)
                {
                    $tariff = 386;
                }

                if($t >= 25000 && $t <= 29999)
                {
                    $tariff = 391;
                }

                if($t >= 30000 && $t <= 34999)
                {
                    $tariff = 396;
                }

                if($t >= 35000 && $t <= 39999)
                {
                    $tariff = 570;
                }

                if($t >= 40000 && $t <= 44999)
                {
                    $tariff = 575;
                }

                if($t >= 45000 && $t <= 49999)
                {
                    $tariff = 580;
                }

                if($t >= 50000 && $t <= 69999)
                {
                    $tariff = 623;
                }

                if($t >= 70000 && $t <= 150000)
                {
                    $tariff = 628;
                }

                // dd('not equal and total = '. ($t + intval($tariff)) );

                $amount = $t + intval($tariff);

                // dd($amount);

                $phone_number = substr($phone_number, -9);
                $phone_number = 254 . $phone_number;

                $trans_id = $request->orderId;
                
                $this->customerMpesaSTKPush($phone_number, $amount, $trans_id);
                
                Tdetails::where('id', '=', $values['orderId'])
                        ->update(['transactioncode' => 'mpesaCode']);
                    
                $transactioncode = $request->orderId;

                $pay = new Payments;
                $pay->transactioncode = $transactioncode;
                $pay->phoneno = $phone_number;
                $pay->mpesacode = '';
                $pay->save();

                return redirect('/home');

            }
        } else {
            return back();
        }

    }
}
