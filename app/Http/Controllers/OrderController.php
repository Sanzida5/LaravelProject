<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use Auth;

class OrderController extends Controller
{
    public function manageCarts(){
        
        $data['carts'] = Order::where("status",false)->orderBy("id","desc")->paginate(100);

        return view("admin.manageCart", $data);
    }
    public function addToCart(Request $request, $id){
        $product = Product::find($id);
        $user = Auth::user();

        if($product){
            $order = Order::where([["status",false], ["user_id", $user->id]])->first();

            if($order){
                $orderItem = OrderItem::where("status",false)->where("product_id", $id)->where("order_id", $order->id)->first();
                if($orderItem){
                     
                    $orderItem->qty += 1;
                    $orderItem->save();
                }
                else{
                    $oi = new OrderItem();
                    $oi->status = false;
                    $oi->product_id = $id;
                    $oi->order_id = $order->id;
                    $oi->save();
                }
            }
            else{
                
                $o = new Order();
                $o->user_id = $user->id;
                $o->status = false;
                $o->save();

              

            }
            return redirect()->route('cart')->with('success', "product added or updated on cart successfully");
        }
        else{
            return redirect()->route('home.index')->with("error","product not found");
        }

        
    }
    
    public function removeFormCart(Request $request, $id){
        $product = Product::find($id);
        $user = Auth::user();

        if($product){
            $order = Order::where([["status",false], ["user_id", $user->id]])->first();

            if($order){
                $orderItem = OrderItem::where("status",false)->where("product_id", $id)->where("order_id", $order->id)->first();
                if($orderItem){
                   
                    if($orderItem->qty > 1){
                        $orderItem->qty -= 1;
                        $orderItem->save();
                        
                    }
                    else{
                        $orderItem->delete();
                    }
                }
            }
           
            return redirect()->route('cart')->with('success', "product updated on cart successfully");
        }
        else{
            return redirect()->route('home.index')->with("error","product not found");
        }
        
    }

    public function cart(){
        $data['order'] = Order::where([["user_id", Auth::id()], ["status",false]])->first();
        
        return view("home.cart",$data);
    }
    public function checkout(Request $req){
        $data['addresses'] = Address::where("user_id", Auth::id())->get();

        if($req->isMethod("post")){
            $data = $req->validate([
                'street_name' => 'required',
                'landmark' => 'required',
                'area' => 'required',
                'pincode' => 'required',
                'city' => 'required',
                
                'type' => 'required',
            ]);

            $data['user_id'] = Auth::id();

            Address::create($data);

            return redirect()->back()->with("success", "Address Inserted Successfully");
        }
        return view("home.checkout",$data);
    }

}
