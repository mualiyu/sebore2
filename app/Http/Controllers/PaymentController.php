<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MobileMoney;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Whoops\Run;

class PaymentController extends Controller
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

    public function index()
    {
        return view('payment.index');
    }

    public function customer_search(Request $request)
    {
        // dd($request->all());

        if ($request->ajax()) {

            $search = $request->customer;

            $customers = Customer::where('org_id', Auth::user()->organization_id);

            if (is_string($search) && strlen($search) > 0) {
                // Search in users project
                $customers = $customers->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('address', 'LIKE', '%' . $search . '%');
                });
            }

            $data = $customers->get();


            $output = '';

            if (count($data) > 0) {

                $output = '<ul class="list-group" style="display: block;">';
                $i = 0;
                foreach ($data as $row) {

                    // $output .= '<li class="list-group-item"><input name="customer" type="checkbox" value="' . $row->id . '" >'
                    //     . $row->name . ' -> ' . $row->phone . '</li>';

                    $output .= '<li class="list-group-item"><div class="form-check"><input class="form-check-input" type="checkbox" name="cus[]" value="'
                        . $row->id . '" id="cus[' . $i . ']"><label class="form-check-label" for="cus[' . $i . ']">'
                        . $row->name . ' - ' . $row->email . ' - ' . $row->phone .
                        '</label></div></li>';
                    $i++;
                }

                $output .= '</ul>';
            } else {

                $output .= '<li class="list-group-item">' . 'No Customer' . '</li>';
            }

            return $output;
        }
    }


    public function get_t_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'daterange' => ['required', 'string', 'max:255'],
            'cus' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $d = explode(' - ', $request->daterange);

        $f = explode('/', $d[0]);
        $from = $f[2] . '-' . $f[0] . '-' . $f[1];
        // . ' 00:00:00';
        $t = explode('/', $d[1]);
        $to = $t[2] . '-' . $t[0] . '-' . $t[1];
        // . ' 23:59:59';

        // return $from . "    v    " . $to;

        // dd($request->cus);

        if (count($request->cus) == 1) {
            $customer = Customer::find($request->cus[0]);
            // return $customer->id;
            $transactions = Transaction::where(['customer_id' => $customer->id, 'p_status' => 0])
                ->whereBetween('date', [$from . '-00-00-01', $to . '-23-59-59'])
                // ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->get();

            // return $transactions;
            if (count($transactions) > 0) {
                return view('payment.transactions', compact('transactions', 'customer'));
            } else {
                return back()->with('error', 'No Available or Unpaid Transaction at the moment for this Customer, Or Within the range.');
            }
            // $hash = hash(
            //     'sha512',
            //     $customer->phone .
            //         $from .
            //         $to
            // );

            // // return $hash . '  -----------  ' . $from . " " . $to . $customer->id;

            // $url = 'https://api.ajisaqsolutions.com/api/transaction/listByCustomer?apiUser=' . config('app.apiUser') .
            //     '&apiKey=' . config('app.apiKey') .
            //     '&hash=' . $hash .
            //     '&customerId=' . $customer->phone .
            //     '&from=' . $from .
            //     '&to=' . $to;
            // // dd($url);

            // try {
            //     $response = Http::get($url);
            //     if ($response->successful()) {

            //         $res = json_decode($response);
            //         if ($res->status == 'Ok') {
            //             if (count($res->data) > 0) {
            //                 $transactions = $res->data;
            //                 // dd($res);
            //                 return view('payment.transactions', compact('transactions', 'customer'));
            //             } else {
            //                 return back()->with('error', 'No Transaction for this Customer.');
            //             }
            //         } else {
            //             return back()->with('error', 'Service Error, Try again later!');
            //         }
            //     }
            // } catch (\Throwable $th) {
            //     return back()->with('error', 'Service Error, Make sure you are connected to Internet!');
            // }
        } else {

            $data = [];
            foreach ($request->cus as $c) {

                $customer = Customer::find($c);

                $transactions = Transaction::where(['customer_id' => $customer->id, 'p_status' => 0])
                    // where('customer_id', '=', $customer->id)
                    ->whereBetween('date', [$from . '-00-00-01', $to . '-23-59-59'])
                    // ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
                    ->get();
                // dd($transactions);

                if (count($transactions) > 0) {

                    $d =  [
                        'customer_id' => $customer->id,
                        'transactions' => $transactions
                    ];

                    array_push($data, $d);

                    // return view('payment.transactions', compact('transactions', 'customer'));
                } else {
                    return back()->with('error', 'No Available or Unpaid Transaction at the moment for ' . $customer->name . ', Or Within the range.');
                }

                // $hash = hash(
                //     'sha512',
                //     $customer->phone .
                //         $from .
                //         $to
                // );

                // $url = 'https://api.ajisaqsolutions.com/api/transaction/listByCustomer?apiUser=' . config('app.apiUser') .
                //     '&apiKey=' . config('app.apiKey') .
                //     '&hash=' . $hash .
                //     '&customerId=' . $customer->phone .
                //     '&from=' . $from .
                //     '&to=' . $to;

                // try {
                //     $response = Http::get($url);
                //     if ($response->successful()) {

                //         $res = json_decode($response);
                //         if ($res->status == 'Ok') {
                //             if (count($res->data) > 0) {
                //                 $transactions = $res->data;

                //                 $d =  [
                //                     'customer_id' => $customer->id,
                //                     'transactions' => $transactions
                //                 ];

                //                 array_push($data, $d);
                //                 // return view('payment.transactions', compact('transactions', 'customer'));
                //             } else {
                //                 return back()->with('error', 'No Transaction for ' . $customer->name . '.');
                //             }
                //         } else {
                //             return back()->with('error', 'Service Error, Try again later!');
                //         }
                //     }
                // } catch (\Throwable $th) {
                //     return back()->with('error', 'Service Error, Make sure you are connected to Internet!');
                // }
            }

            return view('payment.customers_transactions', compact('data'));
        }


        return back();
    }

    //pay just for single transaction
    public function pay_single_t(Request $request)
    {
        $gateway = PaymentGateway::where('org_id', '=', Auth::user()->organization_id)->get();

        // dd(Auth::user()->organization->name);

        foreach ($gateway as $g) {

            $new = substr($request->customerNum, -10);
            $num = '234' . $new;

            $mobile_money = MobileMoney::find($g->gateway_code);

            if ($mobile_money->url == 'https://api.console.eyowo.com') {

                $refresh_t = Http::withHeaders([
                    'Content-Type' => 'application/json ',
                    'X-App-Key' => config('app.eyowo_app_key'),
                ])->post($mobile_money->url . '/v1/users/accessToken', [
                    'refreshToken' => $g->token,
                ]);
                $ref_t = json_decode($refresh_t);

                if ($ref_t->success == true) {

                    //transfer to phone start
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json ',
                        'X-App-Key' => config('app.eyowo_app_key'),
                        'X-App-Wallet-Access-Token' => $ref_t->data->accessToken,
                    ])->post($mobile_money->url . '/v1/users/transfers/phone', [
                        'sendSms' => false,
                        'mobile' => $num,
                        'amount' => $request->amount * 100,
                    ]);
                    $res = json_decode($response);

                    if ($res->success == true) {
                        $customer = Customer::where('id', $request->customerId)->get();

                        $payments = Payment::create([
                            'from_id' => Auth::user()->organization_id,
                            'to_id' => $customer[0]->id,
                            'status' => true,
                            'type' => 'Transaction payment from ' . Auth::user()->organization->name . ' to ' . $customer[0]->name,
                            'ref_num' => $res->data->transaction->reference,
                            'amount' => $request->amount,
                            'gateway_code' => $mobile_money->id,
                        ]);
                        $transaction = Transaction::where('id', '=', $request->transaction)->update([
                            'p_status' => 1,
                        ]);
                        return back()->with('success', 'Payment for ' . $request->i_name . ' is Successful! Thank you.');
                    } else {
                        return back()->with('error', $res->error);
                    }
                    //transfer to phone end

                } else {
                    return back()->with('error', $ref_t->error);
                }
            } else {
                return back()->with('error', 'Wallet Not found! Make sure you Have added Wallet in Your Profile.');
            }
        }

        return back()->with('error', 'No Wallet Gateway is allocated to this Organization.');
    }

    // py for all transaction but one customer
    public function pay_all_tran_p_c(Request $request)
    {

        $gateway = PaymentGateway::where('org_id', '=', Auth::user()->organization_id)->get();

        // dd(Auth::user()->organization->name);

        foreach ($gateway as $g) {

            $new = substr($request->c_number, -10);
            $num = '234' . $new;

            $mobile_money = MobileMoney::find($g->gateway_code);

            if ($mobile_money->url == 'https://api.console.eyowo.com') {

                $refresh_t = Http::withHeaders([
                    'Content-Type' => 'application/json ',
                    'X-App-Key' => config('app.eyowo_app_key'),
                ])->post($mobile_money->url . '/v1/users/accessToken', [
                    'refreshToken' => $g->token,
                ]);
                $ref_t = json_decode($refresh_t);

                if ($ref_t->success == true) {

                    //transfer to phone start
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json ',
                        'X-App-Key' => config('app.eyowo_app_key'),
                        'X-App-Wallet-Access-Token' => $ref_t->data->accessToken,
                    ])->post($mobile_money->url . '/v1/users/transfers/phone', [
                        'sendSms' => false,
                        'mobile' => $num,
                        'amount' => $request->t_amount * 100,
                    ]);
                    $res = json_decode($response);

                    if ($res->success == true) {
                        $customer = Customer::where('id', $request->c_customerId)->get();

                        $payments = Payment::create([
                            'from_id' => Auth::user()->organization_id,
                            'to_id' => $customer[0]->id,
                            'status' => true,
                            'type' => 'Transaction payment from ' . Auth::user()->organization->name . ' to ' . $customer[0]->name,
                            'ref_num' => $res->data->transaction->reference,
                            'amount' => $request->t_amount,
                            'gateway_code' => $mobile_money->id,
                        ]);

                        foreach ($request->transactions as $t) {
                            $transaction = Transaction::where('id', '=', $t)->update([
                                'p_status' => 1,
                            ]);
                        }
                        return back()->with('success', 'Payment to ' . $request->c_name . ' for All transactions is successful, Thank you.');
                    } else {
                        return back()->with('error', $res->error);
                    }
                    //transfer to phone end

                } else {
                    return back()->with('error', $ref_t->error);
                }
            } else {
                return back()->with('error', 'Wallet Not found! Make sure you Have added Wallet in Your Profile.');
            }
        }

        return back()->with('error', 'No Wallet Gateway is allocated to this Organization.');
    }


    public function pay_all_tran_p_c_bulk(Request $request)
    {
        // return $request->all();

        $gateway = PaymentGateway::where('org_id', '=', Auth::user()->organization_id)->get();

        // dd(Auth::user()->organization->name);

        foreach ($gateway as $g) {

            $mobile_money = MobileMoney::find($g->gateway_code);


            if ($mobile_money->url == 'https://api.console.eyowo.com') {

                $refresh_t = Http::withHeaders([
                    'Content-Type' => 'application/json ',
                    'X-App-Key' => config('app.eyowo_app_key'),
                ])->post($mobile_money->url . '/v1/users/accessToken', [
                    'refreshToken' => $g->token,
                ]);
                $ref_t = json_decode($refresh_t);

                if ($ref_t->success == true) {

                    $c_names = [];

                    foreach ($request->info as $i) {

                        $customer = Customer::find($i);

                        array_push($c_names, $customer->name);

                        $amount = $request->amount[$i];
                        $trans = json_decode($request->transactions[$i]);

                        $new = substr($customer->phone, -10);
                        $num = '234' . $new;

                        //transfer to phone start
                        $response = Http::withHeaders([
                            'Content-Type' => 'application/json ',
                            'X-App-Key' => config('app.eyowo_app_key'),
                            'X-App-Wallet-Access-Token' => $ref_t->data->accessToken,
                        ])->post($mobile_money->url . '/v1/users/transfers/phone', [
                            'sendSms' => false,
                            'mobile' =>  $num,
                            'amount' => $amount * 100,
                        ]);
                        $res = json_decode($response);

                        if ($res->success == true) {
                            // $customer = Customer::where('id', $request->c_customerId)->get();

                            $payments = Payment::create([
                                'from_id' => Auth::user()->organization_id,
                                'to_id' => $customer->id,
                                'status' => true,
                                'type' => 'Transaction payment from ' . Auth::user()->organization->name . ' to ' . $customer->name,
                                'ref_num' => $res->data->transaction->reference,
                                'amount' => $res->data->transaction->amount,
                                'gateway_code' => $mobile_money->id,
                            ]);
                            foreach ($trans as $t) {
                                Transaction::where('id', '=', $t)->update([
                                    'p_status' => 1,
                                ]);
                            }
                            // return back()->with('success', 'Successful! Payment to ' . $request->c_name . ' for All transactions, Thank you.');
                        } else {
                            return back()->with('error', $res->error);
                        }
                        //transfer to phone end
                    }
                    return back()->with('success', 'Payment to ' . $c_names[0] . ' And others is made successful, Thank you.');
                } else {
                    return back()->with('error', $ref_t->error);
                }
            } else {
                return back()->with('error', 'Wallet Not found! Make sure you Have added Wallet in Your Profile.');
            }
        }

        return back()->with('error', 'No Wallet Gateway is allocated to this Organization.');
    }
}
