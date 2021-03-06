<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\LogProduct;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
	if($request->has('name')){
		return Product::where('name','like','%'.$request->name.'%')->paginate()->appends('name',$request->name);	
	}
	if($request->has('likes')){
		return Product::where('likes','=',$request->likes)->paginate()->appends('likes',$request->likes);	
	}        
	return Product::paginate();
	//return Product::All();
    }
 
    public function show(Product $product)
    {
        return $product;
    }
 
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'numeric|required',
            'quantity'=>'integer|required',
            'availability' => 'boolean',
            ]);
        
        if($request->user()->hasRole('admin')){
            $product = Product::create($request->all());
            return response()->json($product, 201);
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
                
        
    }
 
    public function update(Request $request, Product $product)
    {
        if($request->has('price')){
            if($request->user()->hasRole('admin')){
                $p = Product::find($product->id);
                $product->update($request->all());
                //this is for save log when price is updated
               

                if($p->price != $request->price)
                {
                    $logProduct = new LogProduct();
                    $logProduct->old_price = $p->price;
                    $logProduct->new_price = $request->price;
                    $p->logProducts()->save($logProduct);
                }	
                    
                return response()->json($product, 200);

            }
            else{
                return response()->json(['message' => 'Unauthorized'], 401);
            }
                
        }
        else{
            $product->update($request->all());
            return response()->json($product, 200);
        }
        
        
    }

    public function setLike(Request $request, Product $product)
    {
        if($request->user()->hasAnyRole(['admin','user'])){
            $likes = $product->likes;
            $likes++;
            $product->likes = $likes;
            $product->update();
            return response()->json(['message'=>'The Product get new like, thank you!','product'=>$product],200);
        }
        else{
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
    }
 
    public function delete(Request $request,Product $product)
    {
        if($request->user()->hasRole('admin')){
            $product->delete();
            return response()->json(null, 204);
        }
        else{
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
    }
}
