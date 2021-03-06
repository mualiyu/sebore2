<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\PlanDetail;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

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
     * 
     */
    public function index()
    {
        $plan = PlanDetail::where('org_id', '=', Auth::user()->organization_id)->orderBy('id', 'desc')->first();
        if ($plan) {
            if ($plan->status != 1) {
                $p = 'You have reached your limit for this Plan, consider Upgrading.';
            }
        } else {
            return redirect('/plan');
        }
        $d = now();
        $da = explode("T", $d);
        $date = explode(" ", $da[0]);

        $from = $date[0];
        $to = $date[0];
        
        // $from = "2021-08-28";
        // $to = "2021-08-28";
        // $cur = explode("-", $date[0]);
        // // $cur[1];
        // return $cur[1];

        $months = array(
            '',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July ',
            'August',
            'September',
            'October',
            'November',
            'December',
        );


        $transactions = [];
        $org = Auth::user()->organization;
        $transactions = Transaction::where(['org_id' => $org->id, "type" => "collection"])->whereBetween('date', [$from . '-00-00-01', $to . '-23-59-59'])->get();

        if ($org->theme) {
            if ($org->theme == 1) {
                $card1 = 'rgb(94,46,46)';
                $card2 = 'rgb(109,61,61)';
                $card3 = 'rgb(127,79,79)';
            } elseif ($org->theme == 2) {
                $card1 = 'rgb(126,170,57)';
                $card2 = 'rgb(124,155,76)';
                $card3 = 'rgb(139,170,91)';
            } elseif ($org->theme == 3) {
                $card1 = 'rgb(75, 70, 245)';
                $card2 = 'rgb(75, 70, 235)';
                $card3 = 'rgb(75, 70, 225)';
            } else {
                $card1 = 'rgb(109, 41, 41)';
                $card2 = 'rgb(100, 41, 41)';
                $card3 = 'rgb(91, 41, 41)';
            }
        } else {
            $card1 = 'rgb(109, 41, 41)';
            $card2 = 'rgb(100, 41, 41)';
            $card3 = 'rgb(91, 41, 41)';
        }

        return view('home', compact('transactions', 'card1', 'card2', 'card3', 'from', 'to', 'months'));
    }

    public function show_profile()
    {
        $organization = Organization::find(Auth::user()->organization_id);
        $user = Auth::user();
        $p_gateway = PaymentGateway::where('org_id', '=', $organization->id)->get();

        return view('profile.profile', compact('organization', 'user', 'p_gateway'));
    }

    public function show_users()
    {
        $users = User::where('organization_id', '=', Auth::user()->organization_id)->orderBy('created_at', 'desc')->get();

        return view('users.index', compact('users'));
    }

    public function show_add_user()
    {
        return view('users.add_user');
    }

    public function create_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string']
        ]);
        if ($validator->fails()) {
            // dd($validator);
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'password' => Hash::make($request['password']),
        ]);

        User::where('id', '=', $user->id)->update([
            'organization_id' => Auth::user()->organization_id,
            'admin_role_id' => $request['role'],
        ]);

        return redirect('/users')->with(['success' => 'New User is Created']);
    }

    public function update_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return back()->with('error', 'User not Updated. Try again!');
        }

        $user = User::where('id', '=', Auth::user()->id)->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'admin_role_id' => $request['role'],
        ]);

        return redirect()->route('profile')->with(['success' => 'Profile is Updated']);
    }

    public function update_organization(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string'],
            'theme' => ['nullable']
        ]);
        if ($validator->fails()) {
            return back()->with('error', 'Organization not Updated. Try again!');
        }

        $org = Organization::find($id);

        // //api start
        // $name = $request['name'];
        // $description = $request['description'];
        // $logo = url('/storage/pic/' . $org->logo);
        // $phone = $request['phone'];
        // $hash = hash('sha512', $name . $description . $logo . $phone);

        // $url = 'https://api.ajisaqsolutions.com/api/organization/update?apiUser=' .
        //     config('app.apiUser') . '&apiKey=' .
        //     config('app.apiKey') . '&hash=' .
        //     $hash . '&id=' .
        //     $id . '&name=' .
        //     $name . '&description=' .
        //     $description . '&logoUrl=' .
        //     $logo . '&phone=' . $phone;

        // $response = Http::post($url);

        // $res = json_decode($response);

        // if ($res->status != 'Ok') {
        //     return back()->with("error", "Sorry! Fail to update your Details, Try later.");
        // }

        $organization = Organization::where('id', '=', $id)->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'theme' => $request['theme'],
        ]);

        return redirect()->route('profile')->with(['success' => 'Organization is Updated']);
    }

    public function update_org_pic(Request $request, $id)
    {

        // dd($user[0]->picture);
        $validator = Validator::make($request->all(), [
            "image" => "image|mimes:jpeg,jpg,png,gif|max:9000",
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Organization Logo not Uploaded. Try again!');
        }

        if ($request->hasFile("image")) {
            $imageNameWExt = $request->file("image")->getClientOriginalName();
            $imageName = pathinfo($imageNameWExt, PATHINFO_FILENAME);
            $imageExt = $request->file("image")->getClientOriginalExtension();

            $imageNameToStore = $imageName . "_" . time() . "." . $imageExt;

            $request->file("image")->storeAs("public/pic", $imageNameToStore);
        } else {
            // $imageNameToStore = $user[0]->picture;
            return back()->with('error', 'Organization Logo not Uploaded. Try again!');
        }

        $org = Organization::find($id);

        // // Api start here
        // $name = $org->name;
        // $description = $org->description;
        // $logo = url('/storage/pic/' . $imageNameToStore);
        // $phone = $org->phone;
        // $hash = hash('sha512', $name . $description . $logo . $phone);

        // $url = 'https://api.ajisaqsolutions.com/api/organization/update?apiUser=' .
        //     config('app.apiUser') . '&apiKey=' .
        //     config('app.apiKey') . '&hash=' .
        //     $hash . '&id=' .
        //     $id . '&name=' .
        //     $name . '&description=' .
        //     $description . '&logoUrl=' .
        //     $logo . '&phone=' . $phone;

        // $response = Http::post($url);

        // $res = json_decode($response);

        // if ($res->status != 'Ok') {
        //     return back()->with("error", "Sorry! System Fail to update your Logo. Try later!");
        // }

        $arrayToStore = [
            "logo" => $imageNameToStore,
        ];

        Organization::where('id', '=', $id)->update($arrayToStore);


        return redirect()->route("profile")->with(['success' => "Organization Logo is Uploaded and Updated."]);
    }



    public function change_user_role(Request $request)
    {
        dd($request->all());
    }

    public function show_single_user($id)
    {
        $user = User::find($id);

        return view('users.profile', compact('user'));
    }

    public function delete_user($id)
    {
        $res = User::where('id', $id)->delete();

        if ($res) {
            return back()->with(['success' => 'One User is Deleted from system']);
        } else {
            return back()->with(['error' => 'User NOT Deleted. Try Again!']);
        }
    }

    public function update_single_user(Request $request, $id)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return back()->with('error', 'User not Updated. Try again!');
        }

        $user = User::where('id', '=', $id)->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'],
            'admin_role_role' => $request['role'],
        ]);

        if ($user) {
            return back()->with(['success' => 'Profile is Updated']);
        } else {
            return back()->with('error', 'User not Updated. Try again!');
        }

        // dd($request->all());
    }
}
