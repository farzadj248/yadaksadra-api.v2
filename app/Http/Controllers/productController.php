<?php

namespace App\Http\Controllers;

use App\Helper\EventLogs;

use \Carbon\Carbon;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Favorites;
use App\Models\ProductsImages;
use App\Models\ProductsCategories;
use App\Models\ProductCarCompany;
use App\Models\ProductCarModels;
use App\Models\ProductsBrands;
use App\Models\ProductCarTypes;
use App\Models\ProductDefinedCar;
use App\Models\ProductsProperties;
use App\Models\ProductCountryBuilders;
use App\Models\ViewersStatistics;
use App\Helper\GenerateSlug;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\ProductCarYears;
use Tymon\JWTAuth\Facades\JWTAuth;

class productController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Product::select(
            'products.id',
            'products.commercial_code',
            'products.technical_code',
            'products.title',
            'products.slug',
            'products.status',
            'products.main_price as price',
            'products.brand_name',
            'products.rating',
            'products.views'
        )
            ->orderBy("id", "desc");
        if ($request->q) {
            $data->where('products.title', 'like', '%' . $request->q . '%')
                ->orWhere('products.short_body', 'like', '%' . $request->q . '%')
                ->orWhere('products.category_name', 'like', '%' . $request->q . '%')
                ->orWhere('products.brand_name', 'like', '%' . $request->q . '%')
                ->orWhere('products.country_name', 'like', '%' . $request->q . '%');
        }
        $data = $data->paginate($request->perPage);
        return ProductResource::collection($data);
    }

    public function getProductsForTorob(Request $request)
    {
        $products = Product::select('slug', 'id', 'main_price', 'main_off', 'main_inventory')
            ->where("status", 1)
            ->orderBy("id", "desc")
            ->paginate(100);


        $products->setCollection(
            $products->getCollection()
                ->map(function ($item) {
                    $item['product_id'] = $item->id;
                    $item['page_url'] = "https://yadaksadra.com/product/" . $item->slug;
                    $item['price'] = $item->main_off ? ($item->main_price - ((100 - $item->main_off) / 100)) : $item->main_price;
                    $item['availability'] = $item->main_inventory ? $item->main_inventory > 0 ? "instock " : "outofstock" : "outofstock";
                    $item['old_price'] = $item->main_price;

                    unset($item['id']);
                    unset($item['slug']);
                    unset($item['main_price']);
                    unset($item['main_off']);
                    unset($item['main_inventory']);

                    return $item;
                })
        );

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products
        ], Response::HTTP_OK);
    }

    public function getProductForTorob(Request $request)
    {

        $slug = "";
        $productId = "";

        if ($request->filled("page_url")) {
            $ex = explode('/', $request->page_url);
            $slug = $ex[count($ex) - 1];
        } else if ($request->filled("page_unique")) {
            $productId = $request->page_unique;
        }

        if ($slug || $productId) {

            $products = Product::select('slug', 'id', 'title', 'category_name', 'short_body', 'main_price', 'main_off', 'main_inventory')
                ->with("images", "properties")
                ->where("slug", $slug)
                ->orWhere("id", $productId)
                ->where("status", 1)
                ->orderBy("id", "desc")
                ->paginate(100);
        } else {
            $products = Product::select('slug', 'id', 'title', 'category_name', 'short_body', 'main_price', 'main_off', 'main_inventory')
                ->with("images", "properties")
                ->orderBy("id", "desc")
                ->where("status", 1)
                ->paginate(100);
        }

        $products->setCollection(
            $products->getCollection()
                ->map(function ($item) {
                    $images = [];
                    if ($item->images->url) {
                        foreach (json_decode($item->images->url) as $key => $value) {
                            $images[] = $value->url;
                        }
                    }

                    $properties = [];
                    foreach ($item->properties as $key => $value) {
                        $properties[] = [$value->value => $value->child];
                    }

                    $item["title"] = $item->title;
                    $item["page_unique"] = $item->id;
                    $item["current_price"] = $item->main_off ? ($item->main_price - ((100 - $item->main_off) / 100)) : $item->main_price;
                    $item["old_price"] = $item->main_price;
                    $item["availability"] = $item->main_inventory ? $item->main_inventory > 0 ? "instock" : "outofstock" : "outofstock";
                    $item["image_link"] = $images[0] ?? "";
                    $item["image_links"] = $images ?? [];
                    $item["page_url"] = "https://yadaksadra.com/product/" . $item->slug;
                    $item["short_desc"] = $item->short_body;
                    $item["guarantee"] =  "گارانتی اصالت و سلامت فیزیکی کالا";
                    $item["spec"] = [
                        "اصالت کالا" => "اصل"
                    ];

                    unset($item['id']);
                    unset($item['images']);
                    unset($item['slug']);
                    unset($item['main_price']);
                    unset($item['main_off']);
                    unset($item['main_inventory']);
                    unset($item['short_body']);
                    unset($item['properties']);

                    return $item;
                })
        );

        return [
            "count" => $products->total(),
            "max_pages" => $products->lastPage(),
            "products" => $products->items()
        ];
    }

    public function getProducts(Request $request)
    {
        $role = "Normal";

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }

        $products1 = Product::select(
            'products.id',
            'products.title',
            'products.slug',
            'products.commercial_code',
            'products.technical_code',
            'products.main_price',
            'products.main_off',
            'products.main_inventory',
            'products.custom_price',
            'products.custom_off',
            'custom_inventory',
            'products.market_price',
            'products.market_off',
            'products.market_inventory',
            'products.main_price_2',
            'products.main_off_2',
            'products.main_inventory_2',
            'products.custom_price_2',
            'products.custom_off_2',
            'custom_inventory_2',
            'products.market_price_2',
            'products.market_off_2',
            'products.market_inventory_2',
            'products.main_price_3',
            'products.main_off_3',
            'products.main_inventory_3',
            'products.custom_price_3',
            'products.custom_off_3',
            'custom_inventory_3',
            'products.market_price_3',
            'products.market_off_3',
            'products.market_inventory_3',
            'products.main_minimum_purchase',
            'products.main_minimum_purchase_2',
            'products.main_minimum_purchase_3',
            'products.market_minimum_purchase',
            'products.market_minimum_purchase_2',
            'products.market_minimum_purchase_3',
            'products.custom_minimum_purchase',
            'products.custom_minimum_purchase_2',
            'products.custom_minimum_purchase_3',
            'products.brand_id',
            'products.category_name',
            'products.brand_name',
            'products.country_id',
            'products.status',
            'products_images.url as image',
            'products.rating',
            'products.views',
            'products.number_sales',
            'is_amazing',
            'amazing_expire',
            'amazing_start',
            'amazing_off'
        )
            ->leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
            ->where('products.status', 1);

        if ($request->q) {
            $products1->whereRaw('concat(products.title,products.category_name,products.brand_name,products.country_name,products.tags,products.commercial_code,products.technical_code) like ?', "%{$request->q}%");
        }
        // dd($request->filters);

        if (array_key_exists('company', $request->filters)) {
            if (!empty($request->filters['company'])) {
                $company = ProductCarCompany::where("title", $request->filters['company'])
                    ->select("id")
                    ->first();

                if ($request->filters['car']) {
                    $car = ProductCarTypes::where('title', $request->filters['car'])
                        ->select("id")
                        ->first();

                    if ($request->filters['model']) {
                        $model = ProductCarModels::where('title', $request->filters['model'])
                            ->where("car_id", $car->id)
                            ->select("id")
                            ->first();
                            

                        if (array_key_exists('year', $request->filters)) {
                            $cars = ProductDefinedCar::select("company_id", "car_id", "model_id", "year_name", "product_id")
                                ->where("company_id", $company->id)
                                ->where("car_id", $car->id)
                                ->where("model_id", $model->id)
                                ->where("year_name", $request->filters['year'])
                                ->get();
                        } else {
                            $cars = ProductDefinedCar::select("company_id", "car_id", "model_id", "product_id")
                                ->where("company_id", $company->id)
                                ->where("car_id", $car->id)
                                ->where("model_id", $model->id)
                                ->get();
                        }
                    } else {
                        $cars = ProductDefinedCar::select("company_id", "car_id", "product_id")
                            ->where("company_id", $company->id)
                            ->where("car_id", $car->id)
                            ->get();
                    }
                } else {
                    $cars = ProductDefinedCar::select("product_id")
                        ->where("company_id", $company->id)
                        ->get();
                }

                $product_res = [];
                foreach ($cars as $item) {
                    array_push($product_res, $item->product_id);
                }

                $products1->whereIn("products.id", array_unique($product_res));
            }
        } else if (array_key_exists('companies', $request->filters)) {
            if ($request->filters['companies']) {
                $companies = ProductCarCompany::whereIn("title", $request->filters['companies'])
                    ->select("id")
                    ->get();

                $product_res = [];
                foreach ($companies as $comapny) {
                    $cars = ProductDefinedCar::select("product_id")
                        ->where("company_id", $comapny->id)
                        ->get();

                    foreach ($cars as $item) {
                        array_push($product_res, $item->product_id);
                    }
                }

                $products1->whereIn("products.id", array_unique($product_res));
            }
        }


        if (array_key_exists('properties', $request->filters)) {

            if ($request->filters['properties']) {
                $ids = ProductsProperties::whereIn('value', $request->filters['properties'])
                    ->select("product_id")
                    ->get();

                $p_ids = [];
                foreach ($ids as $p) {
                    array_push($p_ids, $p->product_id);
                }

                $products1->whereIn("products.id", $p_ids);
            }
        }

        if ($request->filters['categories']) {
            $categories = array();
            foreach ($request->filters['categories'] as $item) {
                $res1 = ProductsCategories::select("id", "title", "parent_id")->where("title", $item)->first();

                if ($res1) {
                    array_push($categories, $res1->id);

                    $res2 = ProductsCategories::select("id", "parent_id")->where('parent_id', $res1->id)->get();

                    if ($res2->count() > 0) {
                        foreach ($res2 as $sub) {
                            array_push($categories, $sub->id);

                            $res3 = ProductsCategories::select("id", "parent_id")->where('parent_id', $sub->id)->get();

                            foreach ($res3 as $child) {
                                array_push($categories, $child->id);
                            }
                        }
                    }
                }
            }

            $products1->whereIn("products.category_id", $categories);
        }

        if ($request->filters['brands']) {
            $brands = array();
            foreach ($request->filters['brands'] as $item) {
                if (is_int($item)) {
                    $res1 = ProductsBrands::select("id", "title", "parent_id")->where("id", $item)->first();
                } else {
                    $res1 = ProductsBrands::select("id", "title", "parent_id")->where("title", $item)->first();
                }

                if ($res1) {
                    array_push($brands, $res1->id);

                    $res2 = ProductsBrands::select("id", "parent_id")->where('parent_id', $res1->id)->get();

                    if ($res2->count() > 0) {
                        foreach ($res2 as $sub) {
                            array_push($brands, $sub->id);

                            $res3 = ProductsBrands::select("id", "parent_id")->where('parent_id', $sub->id)->get();

                            foreach ($res3 as $child) {
                                array_push($brands, $child->id);
                            }
                        }
                    }
                }
            }

            $products1->whereIn("products.brand_id", $brands);
        }
        // Filter by model
        // if ($request->filters['model']) {
        //     $models = array();
        //     foreach ($request->filters['model'] as $item) {
        //         // Assuming model_name exists in the product_car_models table and you want to filter based on that
        //         $res1 = ProductCarModels::select("id")->where("title", $item)->first();

        //         if ($res1) {
        //             // Push the model's ID to the array
        //             array_push($models, $res1->id);
        //         }
        //     }

        //     // If models array is not empty, apply filter to the products query
        //     if (!empty($models)) {
        //         $products1->whereIn("products.model_id", $models);
        //     }
        // }

        // Filter by year
        // if ($request->filters['years']) {
        //     $years = array();
        //     foreach ($request->filters['years'] as $item) {
        //         $res1 = ProductCarYears::select("id", "title")->where("title", $item)->first();

        //         if ($res1) {
        //             array_push($years, $res1->id);
        //         }
        //     }

        //     $products1->whereIn("products.year_id", $years);
        // }

        // if ($request->filters['years']) {
        //     $products1->whereIn("products.country_name", $request->filters['countries']);
        // }
        // if ($request->filters['model']) {
        //     $products1->whereIn("products.country_name", $request->filters['countries']);
        // }

        if ($request->filters['countries']) {
            $products1->whereIn("products.country_name", $request->filters['countries']);
        }

        if ($request->filters['isActive'] == 1) {
            switch ($role) {
                case "Marketer":

                    $products1->where('products.main_inventory_2', '>', 1);

                    break;

                case "Saler":
                    $products1->where('products.main_inventory_3', '>', 1);

                    break;

                default:
                    $products1->where('products.main_inventory', '>', 1);

                    break;
            }
        }


        $filter_min_price = 0;
        $filter_max_price = 0;

        if ($request->filters['price']) {
            $filter_min_price = $request->filters['price'][0] . '000000';
            $filter_max_price = $request->filters['price'][1] . '000000';
        }

        switch ($role) {
                // case "Marketer":
                //     $products1->whereBetween('products.main_price_2', [$filter_min_price, $filter_max_price]);
                //     break;

                // case "Saler":
                //     $products1->whereBetween('products.main_price_3', [$filter_min_price, $filter_max_price]);
                //     break;

                // default :
                //     $products1->whereBetween('products.main_price', [$filter_min_price, $filter_max_price]);
                //     break;
        }

        switch ($request->filters['sortBy']) {
            case "new":
                $products1->orderBy('products.id', 'desc');
                break;

            case "view":
                $products1->orderBy('products.views', 'desc');
                break;

            case "rating":
                $products1->orderBy('products.rating', 'desc');
                break;

            case "sale":
                $products1->orderBy('products.number_sales', 'desc');
                break;

            case "cheap":
                switch ($request->role) {
                    case "Marketer":
                        $products1->orderBy('products.main_price_2', 'asc');
                        break;

                    case "Saler":
                        $products1->orderBy('products.main_price_3', 'asc');
                        break;

                    default:
                        $products1->orderBy('products.main_price', 'asc');
                        break;
                }
                break;

            case "expensive":
                switch ($request->role) {
                    case "Marketer":
                        $products1->orderBy('products.main_price_2', 'desc');
                        break;

                    case "Saler":
                        $products1->orderBy('products.main_price_3', 'desc');
                        break;

                    default:
                        $products1->orderBy('products.main_price', 'desc');
                        break;
                }
                break;
        }

        $products = $products1->paginate(10);

        switch ($role) {
            case "Marketer":
                $products->setCollection(
                    $products->getCollection()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            $item['main_price'] = $item->main_price_2;
                            $item['market_price'] = $item->market_price_2;
                            $item['custom_price'] = $item->custom_price_2;

                            $item['main_off'] = $item->main_off_2;
                            $item['market_off'] = $item->market_off_2;
                            $item['custom_off'] = $item->custom_off_2;

                            $item['main_inventory'] = $item->main_inventory_2;
                            $item['market_inventory'] = $item->market_inventory_2;
                            $item['custom_inventory'] = $item->custom_inventory_2;

                            $item['main_minimum_purchase'] = $item->main_minimum_purchase_2;
                            $item['market_minimum_purchase'] = $item->market_minimum_purchase_2;
                            $item['custom_minimum_purchase'] = $item->custom_minimum_purchase_2;

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_inventory_2']);
                            unset($item['main_price_2']);
                            unset($item['main_off_2']);

                            unset($item['custom_inventory_2']);
                            unset($item['custom_price_2']);
                            unset($item['custom_off_2']);

                            unset($item['market_inventory_2']);
                            unset($item['market_price_2']);
                            unset($item['market_off_2']);

                            unset($item['main_minimum_purchase_2']);
                            unset($item['main_minimum_purchase_3']);
                            unset($item['market_minimum_purchase_2']);
                            unset($item['market_minimum_purchase_3']);
                            unset($item['custom_minimum_purchase_2']);
                            unset($item['custom_minimum_purchase_3']);

                            return $item;
                        })
                );
                break;

            case "Saler":
                $products->setCollection(
                    $products->getCollection()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            $item['main_price'] = $item->main_price_3;
                            $item['market_price'] = $item->market_price_3;
                            $item['custom_price'] = $item->custom_price_3;

                            $item['main_off'] = $item->main_off_3;
                            $item['market_off'] = $item->market_off_3;
                            $item['custom_off'] = $item->custom_off_3;

                            $item['main_inventory'] = $item->main_inventory_3;
                            $item['market_inventory'] = $item->market_inventory_3;
                            $item['custom_inventory'] = $item->custom_inventory_3;

                            $item['main_minimum_purchase'] = $item->main_minimum_purchase_3;
                            $item['market_minimum_purchase'] = $item->market_minimum_purchase_3;
                            $item['custom_minimum_purchase'] = $item->custom_minimum_purchase_3;

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_inventory_3']);
                            unset($item['main_price_3']);
                            unset($item['main_off_3']);

                            unset($item['custom_inventory_3']);
                            unset($item['custom_price_3']);
                            unset($item['custom_off_3']);

                            unset($item['market_inventory_3']);
                            unset($item['market_price_3']);
                            unset($item['market_off_3']);

                            unset($item['main_minimum_purchase_2']);
                            unset($item['main_minimum_purchase_3']);
                            unset($item['market_minimum_purchase_2']);
                            unset($item['market_minimum_purchase_3']);
                            unset($item['custom_minimum_purchase_2']);
                            unset($item['custom_minimum_purchase_3']);

                            return $item;
                        })
                );
                break;

            default:
                $products->setCollection(
                    $products->getCollection()
                        ->map(function ($item) use ($request) {
                            $item['isFavorite'] = Favorites::where("product_id", $item->id)
                                ->where('uuid', $request->uuid)->exists();

                            $item['main_price'] = $item->main_price;
                            $item['market_price'] = $item->market_price;
                            $item['custom_price'] = $item->custom_price;

                            $item['main_off'] = $item->main_off;
                            $item['market_off'] = $item->market_off;
                            $item['custom_off'] = $item->custom_off;

                            $item['main_inventory'] = $item->main_inventory;
                            $item['market_inventory'] = $item->market_inventory;
                            $item['custom_inventory'] = $item->custom_inventory;

                            $item['main_minimum_purchase'] = $item->main_minimum_purchase;
                            $item['market_minimum_purchase'] = $item->market_minimum_purchase;
                            $item['custom_minimum_purchase'] = $item->custom_minimum_purchase;

                            if ($item->is_amazing == 1) {
                                $startTime = Carbon::parse($item->amazing_start);
                                $endTime = Carbon::parse($item->amazing_expire);
                                $now = Carbon::now();

                                if ($now->between($startTime, $endTime)) {
                                    $item['isOffer'] = true;
                                    $item['offer'] = $item->amazing_off;
                                } else {
                                    $item['isOffer'] = false;
                                }
                            } else {
                                $item['isOffer'] = false;
                            }

                            unset($item['amazing_start']);
                            unset($item['amazing_expire']);
                            unset($item['is_amazing']);
                            unset($item['amazing_off']);

                            unset($item['main_minimum_purchase_2']);
                            unset($item['main_minimum_purchase_3']);
                            unset($item['market_minimum_purchase_2']);
                            unset($item['market_minimum_purchase_3']);
                            unset($item['custom_minimum_purchase_2']);
                            unset($item['custom_minimum_purchase_3']);

                            return $item;
                        })
                );
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products
        ], Response::HTTP_OK);
    }

    public function majorShopping(Request $request)
    {
        $role = "Normal";

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }

        $products1 = Product::select(
            'products.id',
            'products.title',
            'products.slug',
            'products.commercial_code',
            'products.technical_code',
            'products.main_price',
            'products.main_off',
            'products.main_inventory',
            'products.custom_price',
            'products.custom_off',
            'custom_inventory',
            'products.market_price',
            'products.market_off',
            'products.market_inventory',
            'products.main_price_2',
            'products.main_off_2',
            'products.main_inventory_2',
            'products.custom_price_2',
            'products.custom_off_2',
            'custom_inventory_2',
            'products.market_price_2',
            'products.market_off_2',
            'products.market_inventory_2',
            'products.main_price_3',
            'products.main_off_3',
            'products.main_inventory_3',
            'products.custom_price_3',
            'products.custom_off_3',
            'custom_inventory_3',
            'products.market_price_3',
            'products.market_off_3',
            'products.market_inventory_3',
            'products.main_minimum_purchase',
            'products.main_minimum_purchase_2',
            'products.main_minimum_purchase_3',
            'products.market_minimum_purchase',
            'products.market_minimum_purchase_2',
            'products.market_minimum_purchase_3',
            'products.custom_minimum_purchase',
            'products.custom_minimum_purchase_2',
            'products.custom_minimum_purchase_3',
            'products.brand_id',
            'products.category_name',
            'products.brand_name',
            'products.country_id',
            'products.status',
            'products_images.url as image',
            'products.rating',
            'products.views',
            'products.number_sales',
            'is_amazing',
            'amazing_expire',
            'amazing_start',
            'amazing_off'
        )
            ->leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
            ->where('products.status', 1);

        if ($request->filters['category']) {
            $category = ProductsCategories::select("id", "title", "parent_id")->where("title", $request->filters['category'])->first();

            if ($category) {
                $products1->where("products.category_id", $category->id);
            }
        }

        $products = $products1->get();

        switch ($role) {
            case "Marketer":
                $products
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        $item['main_price'] = $item->main_price_2;
                        $item['market_price'] = $item->market_price_2;
                        $item['custom_price'] = $item->custom_price_2;

                        $item['main_off'] = $item->main_off_2;
                        $item['market_off'] = $item->market_off_2;
                        $item['custom_off'] = $item->custom_off_2;

                        $item['main_inventory'] = $item->main_inventory_2;
                        $item['market_inventory'] = $item->market_inventory_2;
                        $item['custom_inventory'] = $item->custom_inventory_2;

                        $item['main_minimum_purchase'] = $item->main_minimum_purchase_2;
                        $item['market_minimum_purchase'] = $item->market_minimum_purchase_2;
                        $item['custom_minimum_purchase'] = $item->custom_minimum_purchase_2;

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_inventory_2']);
                        unset($item['main_price_2']);
                        unset($item['main_off_2']);

                        unset($item['custom_inventory_2']);
                        unset($item['custom_price_2']);
                        unset($item['custom_off_2']);

                        unset($item['market_inventory_2']);
                        unset($item['market_price_2']);
                        unset($item['market_off_2']);

                        unset($item['main_minimum_purchase_2']);
                        unset($item['main_minimum_purchase_3']);
                        unset($item['market_minimum_purchase_2']);
                        unset($item['market_minimum_purchase_3']);
                        unset($item['custom_minimum_purchase_2']);
                        unset($item['custom_minimum_purchase_3']);

                        return $item;
                    });
                break;

            case "Saler":
                $products
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        $item['main_price'] = $item->main_price_3;
                        $item['market_price'] = $item->market_price_3;
                        $item['custom_price'] = $item->custom_price_3;

                        $item['main_off'] = $item->main_off_3;
                        $item['market_off'] = $item->market_off_3;
                        $item['custom_off'] = $item->custom_off_3;

                        $item['main_inventory'] = $item->main_inventory_3;
                        $item['market_inventory'] = $item->market_inventory_3;
                        $item['custom_inventory'] = $item->custom_inventory_3;

                        $item['main_minimum_purchase'] = $item->main_minimum_purchase_3;
                        $item['market_minimum_purchase'] = $item->market_minimum_purchase_3;
                        $item['custom_minimum_purchase'] = $item->custom_minimum_purchase_3;

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_inventory_3']);
                        unset($item['main_price_3']);
                        unset($item['main_off_3']);

                        unset($item['custom_inventory_3']);
                        unset($item['custom_price_3']);
                        unset($item['custom_off_3']);

                        unset($item['market_inventory_3']);
                        unset($item['market_price_3']);
                        unset($item['market_off_3']);

                        unset($item['main_minimum_purchase_2']);
                        unset($item['main_minimum_purchase_3']);
                        unset($item['market_minimum_purchase_2']);
                        unset($item['market_minimum_purchase_3']);
                        unset($item['custom_minimum_purchase_2']);
                        unset($item['custom_minimum_purchase_3']);

                        return $item;
                    });
                break;

            default:
                $products
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        $item['main_price'] = $item->main_price;
                        $item['market_price'] = $item->market_price;
                        $item['custom_price'] = $item->custom_price;

                        $item['main_off'] = $item->main_off;
                        $item['market_off'] = $item->market_off;
                        $item['custom_off'] = $item->custom_off;

                        $item['main_inventory'] = $item->main_inventory;
                        $item['market_inventory'] = $item->market_inventory;
                        $item['custom_inventory'] = $item->custom_inventory;

                        $item['main_minimum_purchase'] = $item->main_minimum_purchase;
                        $item['market_minimum_purchase'] = $item->market_minimum_purchase;
                        $item['custom_minimum_purchase'] = $item->custom_minimum_purchase;

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_minimum_purchase_2']);
                        unset($item['main_minimum_purchase_3']);
                        unset($item['market_minimum_purchase_2']);
                        unset($item['market_minimum_purchase_3']);
                        unset($item['custom_minimum_purchase_2']);
                        unset($item['custom_minimum_purchase_3']);

                        return $item;
                    });
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products
        ], Response::HTTP_OK);
    }

    public function getSimilarProducts(Request $request)
    {
        $role = "Normal";

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }


        $products = Product::leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'products.id')
                ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
        })
            ->where('products.brand_id', $request->brand)
            ->where('products.status', 1)
            ->orWhere('products.category_id', $request->category)
            ->take(10);


        switch ($role) {
            case "Marketer":
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    'products.main_price_2',
                    'products.main_off_2',
                    'products.main_inventory_2',
                    'products.custom_price_2',
                    'products.custom_off_2',
                    'custom_inventory_2',
                    'products.market_price_2',
                    'products.market_off_2',
                    'products.market_inventory_2',
                    'products.brand_name',
                    'products.status',
                    'products_images.url as image',
                    'products.rating',
                    'products.views',
                    'is_amazing',
                    'amazing_expire',
                    'amazing_start',
                    'amazing_off'
                );

                $products1 = $products->get()
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        if ((int)$item->main_inventory_2 > 0) {
                            $item['price'] = $item->main_price_2;
                            $item['off'] = $item->main_off_2;
                            $item['inventory'] = $item->main_inventory_2;
                            $item['grade'] = "Main";
                        } else if ((int)$item->custom_inventory_2 > 0) {
                            $item['price'] = $item->custom_price_2;
                            $item['off'] = $item->custom_off_2;
                            $item['inventory'] = $item->custom_inventory_2;
                            $item['grade'] = "Custom";
                        } else if ((int)$item->market_inventory_2 > 0) {
                            $item['price'] = $item->market_price_2;
                            $item['off'] = $item->market_off_2;
                            $item['inventory'] = $item->market_inventory_2;
                            $item['grade'] = "Market";
                        } else {
                            $item['price'] = 0;
                            $item['off'] = 0;
                            $item['inventory'] = 0;
                            $item['grade'] = "Main";
                        }

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_inventory_2']);
                        unset($item['main_price_2']);
                        unset($item['main_off_2']);

                        unset($item['custom_inventory_2']);
                        unset($item['custom_price_2']);
                        unset($item['custom_off_2']);

                        unset($item['market_inventory_2']);
                        unset($item['market_price_2']);
                        unset($item['market_off_2']);

                        return $item;
                    });
                break;

            case "Saler":
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    'products.main_price_3',
                    'products.main_off_3',
                    'products.main_inventory_3',
                    'products.custom_price_3',
                    'products.custom_off_3',
                    'custom_inventory_3',
                    'products.market_price_3',
                    'products.market_off_3',
                    'products.market_inventory_3',
                    'products.brand_name',
                    'products.status',
                    'products_images.url as image',
                    'products.rating',
                    'products.views',
                    'is_amazing',
                    'amazing_expire',
                    'amazing_start',
                    'amazing_off'
                );

                $products1 = $products->get()
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        if ((int)$item->main_inventory_3 > 0) {
                            $item['price'] = $item->main_price_3;
                            $item['off'] = $item->main_off_3;
                            $item['inventory'] = $item->main_inventory_3;
                            $item['grade'] = "Main";
                        } else if ((int)$item->custom_inventory_3 > 0) {
                            $item['price'] = $item->custom_price_3;
                            $item['off'] = $item->custom_off_3;
                            $item['inventory'] = $item->custom_inventory_3;
                            $item['grade'] = "Custom";
                        } else if ((int)$item->market_inventory_3 > 0) {
                            $item['price'] = $item->market_price_3;
                            $item['off'] = $item->market_off_3;
                            $item['inventory'] = $item->market_inventory_3;
                            $item['grade'] = "Market";
                        } else {
                            $item['price'] = 0;
                            $item['off'] = 0;
                            $item['inventory'] = 0;
                            $item['grade'] = "Main";
                        }

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_inventory_3']);
                        unset($item['main_price_3']);
                        unset($item['main_off_3']);

                        unset($item['custom_inventory_3']);
                        unset($item['custom_price_3']);
                        unset($item['custom_off_3']);

                        unset($item['market_inventory_3']);
                        unset($item['market_price_3']);
                        unset($item['market_off_3']);

                        return $item;
                    });
                break;

            default:
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    'products.main_price',
                    'products.main_off',
                    'products.main_inventory',
                    'products.custom_price',
                    'products.custom_off',
                    'custom_inventory',
                    'products.market_price',
                    'products.market_off',
                    'products.market_inventory',
                    'products.brand_name',
                    'products.status',
                    'products_images.url as image',
                    'products.rating',
                    'products.views',
                    'is_amazing',
                    'amazing_expire',
                    'amazing_start',
                    'amazing_off'
                );

                $products1 = $products->get()
                    ->map(function ($item) use ($request) {
                        $item['isFavorite'] = Favorites::where("product_id", $item->id)
                            ->where('uuid', $request->uuid)->exists();

                        if ((int)$item->main_inventory > 0) {
                            $item['price'] = $item->main_price;
                            $item['off'] = $item->main_off;
                            $item['inventory'] = $item->main_inventory;
                            $item['grade'] = "Main";
                        } else if ((int)$item->custom_inventory > 0) {
                            $item['price'] = $item->custom_price;
                            $item['off'] = $item->custom_off;
                            $item['inventory'] = $item->custom_inventory;
                            $item['grade'] = "Custom";
                        } else if ((int)$item->market_inventory > 0) {
                            $item['price'] = $item->market_price;
                            $item['off'] = $item->market_off;
                            $item['inventory'] = $item->market_inventory;
                            $item['grade'] = "Market";
                        } else {
                            $item['price'] = 0;
                            $item['off'] = 0;
                            $item['inventory'] = 0;
                            $item['grade'] = "Main";
                        }

                        if ($item->is_amazing == 1) {
                            $startTime = Carbon::parse($item->amazing_start);
                            $endTime = Carbon::parse($item->amazing_expire);
                            $now = Carbon::now();

                            if ($now->between($startTime, $endTime)) {
                                $item['isOffer'] = true;
                                $item['offer'] = $item->amazing_off;
                            } else {
                                $item['isOffer'] = false;
                            }
                        } else {
                            $item['isOffer'] = false;
                        }

                        unset($item['amazing_start']);
                        unset($item['amazing_expire']);
                        unset($item['is_amazing']);
                        unset($item['amazing_off']);

                        unset($item['main_inventory']);
                        unset($item['main_price']);
                        unset($item['main_off']);

                        unset($item['custom_inventory']);
                        unset($item['custom_price']);
                        unset($item['custom_off']);

                        unset($item['market_inventory']);
                        unset($item['market_price']);
                        unset($item['market_off']);

                        return $item;
                    });
                break;
        }


        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products1
        ], Response::HTTP_OK);
    }

    public function product_search(Request $request)
    {
        $products = Product::select(
            'products.id',
            'products.title',
            'products.slug',
            'products_images.url as image',
            'products.short_body'
        )
            ->leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
            ->where('products.title', 'like', '%' . $request->q . '%')
            ->orWhere('products.short_body', 'like', '%' . $request->q . '%')
            ->orWhere('products.category_name', 'like', '%' . $request->q . '%')
            ->orWhere('products.brand_name', 'like', '%' . $request->q . '%')
            ->orWhere('products.country_name', 'like', '%' . $request->q . '%')
            ->take(30)
            ->get();

        $brands = ProductsBrands::orderBy("order", "asc")->where("parent_id", 0)->take(5)->get();
        $categories = ProductsCategories::orderBy("order", "asc")->where("parent_id", 0)->take(5)->get();

        foreach ($products as $product) {
            $product->image = json_decode($product->image);
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'products' => $products,
                'category' => [
                    'brands' => $brands,
                    'categories' => $categories
                ]
            ]
        ], Response::HTTP_OK);
    }

    public function getAmazinProducts(Request $request)
    {
        $role = "Normal";
        $productsCategories = ProductsCategories::where("parent_id", 20)->paginate(10);

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }

        $currentDate  = Carbon::now();

        $products =  Product::leftJoin('products_images', function ($query) {
            $query->on('products_images.product_id', '=', 'products.id')
                ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
        })
            ->orderBy("products.updated_at", 'asc')
            ->where("products.is_amazing", "=", 1);

        if ($request->has("sortBy")) {
            switch ($request->sortBy) {
                case "new":
                    $products->orderBy('products.id', 'desc');
                    break;

                case "view":
                    $products->orderBy('products.views', 'desc');
                    break;

                case "rating":
                    $products->orderBy('products.rating', 'desc');
                    break;

                case "sale":
                    $products->orderBy('products.number_sales', 'desc');
                    break;

                case "cheap":
                    switch ($role) {
                        case "Marketer":
                            $products->orderBy('products.main_price_2', 'asc');
                            break;

                        case "Saler":
                            $products->orderBy('products.main_price_3', 'asc');
                            break;

                        default:
                            $products->orderBy('products.main_price', 'asc');
                            break;
                    }
                    break;

                case "expensive":
                    switch ($role) {
                        case "Marketer":
                            $products->orderBy('products.main_price_2', 'desc');
                            break;

                        case "Saler":
                            $products->orderBy('products.main_price_3', 'desc');
                            break;

                        default:
                            $products->orderBy('products.main_price', 'desc');
                            break;
                    }
                    break;
            }
        }

        switch ($role) {
            case "Marketer":
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    "products.isFreeDelivery",
                    'products.main_price_2',
                    'products.main_off_2',
                    'products.main_inventory_2',
                    'products.custom_price_2',
                    'products.custom_off_2',
                    'custom_inventory_2',
                    'products.market_price_2',
                    'products.market_off_2',
                    'products.market_inventory_2',
                    'products.status',
                    'products_images.url as image',
                    'products.brand_name',
                    'products.rating',
                    'products.views',
                    'products.amazing_expire as expire',
                    'products.amazing_off as offer_percentage',
                    'products.is_amazing as hasOffer',
                    'products.amazing_start',
                    'products.updated_at'
                );

                $amazin_products = $products->get()->map(function ($item) use ($request, $currentDate) {
                    $item['isFavorite'] = Favorites::where("product_id", $item->id)
                        ->where('uuid', $request->uuid)->exists();

                    if ((int)$item->main_inventory_2 > 0) {
                        $item['price'] = $item->main_price_2;
                        $item['off'] = $item->main_off_2;
                        $item['inventory'] = $item->main_inventory_2;
                        $item['grade'] = "Main";
                    } else if ((int)$item->custom_inventory_2 > 0) {
                        $item['price'] = $item->custom_price_2;
                        $item['off'] = $item->custom_off_2;
                        $item['inventory'] = $item->custom_inventory_2;
                        $item['grade'] = "Custom";
                    } else if ((int)$item->market_inventory_2 > 0) {
                        $item['price'] = $item->market_price_2;
                        $item['off'] = $item->market_off_2;
                        $item['inventory'] = $item->market_inventory_2;
                        $item['grade'] = "Market";
                    } else {
                        $item['price'] = 0;
                        $item['off'] = 0;
                        $item['inventory'] = 0;
                        $item['grade'] = "Main";
                    }

                    unset($item['main_inventory_2']);
                    unset($item['main_price_2']);
                    unset($item['main_off_2']);

                    unset($item['custom_inventory_2']);
                    unset($item['custom_price_2']);
                    unset($item['custom_off_2']);

                    unset($item['market_inventory_2']);
                    unset($item['market_price_2']);
                    unset($item['market_off_2']);

                    $startTime = Carbon::parse($item->amazing_start);
                    $endTime = Carbon::parse($item->expire);
                    $now = Carbon::now();

                    if ($now->between($startTime, $endTime)) {
                        return $item;
                    }
                });

                break;

            case "Saler":
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    "products.isFreeDelivery",
                    'products.main_price_3',
                    'products.main_off_3',
                    'products.main_inventory_3',
                    'products.custom_price_3',
                    'products.custom_off_3',
                    'custom_inventory_3',
                    'products.market_price_3',
                    'products.market_off_3',
                    'products.market_inventory_3',
                    'products.brand_name',
                    'products.status',
                    'products_images.url as image',
                    'products.rating',
                    'products.views',
                    'products.amazing_expire as expire',
                    'products.amazing_off as offer_percentage',
                    'products.is_amazing as hasOffer',
                    'products.amazing_start',
                    'products.updated_at'
                );

                $amazin_products = $products->get()->map(function ($item) use ($request, $currentDate) {
                    $item['isFavorite'] = Favorites::where("product_id", $item->id)
                        ->where('uuid', $request->uuid)->exists();

                    if ((int)$item->main_inventory_3 > 0) {
                        $item['price'] = $item->main_price_3;
                        $item['off'] = $item->main_off_3;
                        $item['inventory'] = $item->main_inventory_3;
                        $item['grade'] = "Main";
                    } else if ((int)$item->custom_inventory_3 > 0) {
                        $item['price'] = $item->custom_price_3;
                        $item['off'] = $item->custom_off_3;
                        $item['inventory'] = $item->custom_inventory_3;
                        $item['grade'] = "Custom";
                    } else if ((int)$item->market_inventory_3 > 0) {
                        $item['price'] = $item->market_price_3;
                        $item['off'] = $item->market_off_3;
                        $item['inventory'] = $item->market_inventory_3;
                        $item['grade'] = "Market";
                    } else {
                        $item['price'] = 0;
                        $item['off'] = 0;
                        $item['inventory'] = 0;
                        $item['grade'] = "Main";
                    }

                    unset($item['main_inventory_3']);
                    unset($item['main_price_3']);
                    unset($item['main_off_3']);

                    unset($item['custom_inventory_3']);
                    unset($item['custom_price_3']);
                    unset($item['custom_off_3']);

                    unset($item['market_inventory_3']);
                    unset($item['market_price_3']);
                    unset($item['market_off_3']);

                    $startTime = Carbon::parse($item->amazing_start);
                    $endTime = Carbon::parse($item->expire);
                    $now = Carbon::now();

                    if ($now->between($startTime, $endTime)) {
                        return $item;
                    }
                });
                break;

            default:
                $products->select(
                    'products.id',
                    'products.title',
                    'products.slug',
                    "products.isFreeDelivery",
                    'products.main_price',
                    'products.main_off',
                    'products.main_inventory',
                    'products.custom_price',
                    'products.custom_off',
                    'custom_inventory',
                    'products.market_price',
                    'products.market_off',
                    'products.market_inventory',
                    'products.brand_name',
                    'products.status',
                    'products_images.url as image',
                    'products.rating',
                    'products.views',
                    'products.amazing_expire as expire',
                    'products.amazing_off as offer_percentage',
                    'products.is_amazing as hasOffer',
                    'products.amazing_start',
                    'products.updated_at'
                );

                $amazin_products = $products->get()->map(function ($item) use ($request, $currentDate) {
                    $item['isFavorite'] = Favorites::where("product_id", $item->id)
                        ->where('uuid', $request->uuid)->exists();

                    if ((int)$item->main_inventory > 0) {
                        $item['price'] = $item->main_price;
                        $item['off'] = $item->main_off;
                        $item['inventory'] = $item->main_inventory;
                        $item['grade'] = "Main";
                    } else if ((int)$item->custom_inventory > 0) {
                        $item['price'] = $item->custom_price;
                        $item['off'] = $item->custom_off;
                        $item['inventory'] = $item->custom_inventory;
                        $item['grade'] = "Custom";
                    } else if ((int)$item->market_inventory > 0) {
                        $item['price'] = $item->market_price;
                        $item['off'] = $item->market_off;
                        $item['inventory'] = $item->market_inventory;
                        $item['grade'] = "Market";
                    } else {
                        $item['price'] = 0;
                        $item['off'] = 0;
                        $item['inventory'] = 0;
                        $item['grade'] = "Main";
                    }

                    unset($item['main_inventory']);
                    unset($item['main_price']);
                    unset($item['main_off']);

                    unset($item['custom_inventory']);
                    unset($item['custom_price']);
                    unset($item['custom_off']);

                    unset($item['market_inventory']);
                    unset($item['market_price']);
                    unset($item['market_off']);

                    $startTime = Carbon::parse($item->amazing_start);
                    $endTime = Carbon::parse($item->expire);
                    $now = Carbon::now();

                    if ($now->between($startTime, $endTime)) {
                        return $item;
                    }
                });
                break;
        }

        $res = [];


        foreach ($amazin_products as $product) {
            if (!empty($product)) {
                array_push($res, $product);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'amazin_products' => $res,
                'categories' => $productsCategories
            ],
        ], Response::HTTP_OK);
    }


    public function getUserRecentVisits(Request $request)
    {
        if ($request->ids) {
            $ids = json_decode($request->ids);

            $products1 = Product::leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
                ->whereIn('products.id', $ids);

            switch ($request->role) {
                case "Marketer":
                    $products1->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        'products.market_inventory as inventory',
                        'products.brand_id',
                        'products.brand_name',
                        'products.country_id',
                        'products.status',
                        'products_images.url as image',
                        'products.main_price_2 as price',
                        'products.market_off as off ',
                        'products.rating',
                        'products.views',
                        'products.number_sales'
                    );

                    break;

                case "Saler":
                    $products1->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        'products.custom_inventory as inventory',
                        'products.brand_id',
                        'products.brand_name',
                        'products.country_id',
                        'products.status',
                        'products_images.url as image',
                        'products.main_price_3 as price',
                        'products.custom_off as off',
                        'products.rating',
                        'products.views',
                        'products.number_sales'
                    );

                    break;

                default:
                    $products1->select(
                        'products.id',
                        'products.title',
                        'products.slug',
                        'products.main_inventory as inventory',
                        'products.brand_id',
                        'products.brand_name',
                        'products.country_id',
                        'products.status',
                        'products_images.url as image',
                        'products.main_price as price',
                        'products.main_off as off',
                        'products.rating',
                        'products.views',
                        'products.number_sales'
                    );

                    break;
            }

            $products = $products1->get();
        } else {
            $products = [];
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $products
        ], Response::HTTP_OK);
    }

    public function NotificationOfWarehouseStock(Request $request)
    {
        $products = Product::select(
            'products.id',
            'products.title',
            'products.slug',
            'products.main_inventory',
            'products.market_inventory',
            'products.custom_inventory',
            'products_images.url as image'
        )
            ->leftJoin('products_images', function ($query) {
                $query->on('products_images.product_id', '=', 'products.id')
                    ->whereRaw('products_images.id IN (select MAX(a2.id) from products_images as a2 join products as u2 on u2.id = a2.product_id group by u2.id)');
            })
            ->orderBy("id", "desc")
            ->where("products.main_inventory", "<", 10)
            ->where("products.market_inventory", "<", 10)
            ->where("products.custom_inventory", "<", 10)
            ->get();

        $details = [
            'view' => 'email.products_inventory',
            'products' => $users,
            'subject' => $request->subject,
            'body' => $request->body
        ];

        $job = (new \App\Jobs\SendQueueEmail($details))
            ->delay(now()->addSeconds(2));

        dispatch($job);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    public function isCompatibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|numeric',
            'company' => 'required|numeric',
            'type' => 'required|numeric',
            'model' => 'nullable|numeric',
            'year' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->year) {
            $res = ProductDefinedCar::where("product_id", $request->product_id)
                ->where("company_id", $request->company)
                ->where("car_id", $request->type)
                ->where("model_id", $request->model)
                ->where("year_id", $request->year)
                ->exists();
        } else if ($request->model) {
            $res = ProductDefinedCar::where("product_id", $request->product_id)
                ->where("company_id", $request->company)
                ->where("car_id", $request->type)
                ->where("model_id", $request->model)
                ->exists();
        } else {
            $res = ProductDefinedCar::where("product_id", $request->product_id)
                ->where("company_id", $request->company)
                ->where("car_id", $request->type)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => $res
        ], Response::HTTP_OK);
    }

    public function definedCar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|numeric',
            'company_name' => 'required|string',
            'car_id' => 'numeric|nullable',
            'car_name' => 'string|nullable',
            'model_id' => 'numeric|nullable',
            'model_name' => 'string|nullable',
            'year_id' => 'string|nullable',
            'year_name' => 'nullable',
            'products' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }
    }

    public function product_define_cars(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required',
            'cars' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }


        if ($request->products) {
            foreach (json_decode($request->products, true) as $product) {
                if ($product['id']) {
                    foreach (json_decode($request->cars, true) as $car) {
                        ProductDefinedCar::create([
                            'company_id' => $car['company_id'],
                            'company_name' => $car['company_name'],
                            "car_id" => $car['car_id'],
                            "car_name" => $car['car_name'],
                            "model_id" => $car['model_id'],
                            "model_name" => $car['model_name'],
                            "year_id" => $car['year_id'],
                            "year_name" => $car['year_name'],
                            "product_id" => $product['id']
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function getProductSeo(Request $request)
    {

        $product = Product::where("slug", $request->slug)
            ->select("id", "title", "short_body", "main_inventory", "main_price", "main_off")
            ->first();

        if (!$product) {
            return response()->json([
                'success' => true,
                'statusCode' => 422,
                'message' => 'محصولی با این عنوان یافت نشد!',
                'data' => null
            ], Response::HTTP_OK);
        }

        $image = ProductsImages::where("product_id", $product->id)
            ->select("url")
            ->first();


        $image_url = null;

        if ($image) {
            if (!is_array($image->url)) {
                $images = json_decode($image->url, true);
            } else {
                $images = $image->url;
            }

            if ($images) {
                $image_url = $images[0]["url"];
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                "id" => $product->id,
                "title" => $product->title,
                "description" => $product->short_body,
                "inventory" => $product->main_inventory,
                "price" => $product->main_price,
                "off" => $product->main_off,
                "picture" => $image_url
            ]
        ], Response::HTTP_OK);
    }

    public function seen_product(Request $request)
    {
        $product = Product::where("id", $request->id)->first();
        if (!$product) return;

        $product->increment('views', 1);

        ViewersStatistics::create([
            "type" => "product",
            "post_id" => $product->id,
            "price" => $product->main_price,
            "ip_address" => $request->ip(),
            "action" => "seen"
        ]);

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.'
        ], Response::HTTP_OK);
    }

    public function getPartialProduct(Request $request)
    {
        $role = "Normal";

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }

        $product = Product::where("id", $request->id)->first();
        $images = ProductsImages::select("id", "url")->where("product_id", $product->id)->first();

        $isOffer = false;
        $offer = 0;
        $expire = null;
        if ($product->is_amazing == 1) {
            $startTime = Carbon::parse($product->amazing_start);
            $endTime = Carbon::parse($product->amazing_expire);
            $now = Carbon::now();

            if ($now->between($startTime, $endTime)) {
                $isOffer = true;
                $offer = $product->amazing_off;
                $expire = $product->amazing_expire;
            }
        }

        switch ($role) {
            case "Marketer":
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'number_sales' => $product->number_sales,
                    'main_inventory' => $product->main_inventory_2,
                    'custom_inventory' => $product->custom_inventory_2,
                    'market_inventory' => $product->market_inventory_2,
                    'views' => $product->views,
                    'main_price' => $product->main_price_2,
                    'custom_price' => $product->custom_price_2,
                    'market_price' => $product->market_price_2,
                    'main_off' => $product->main_off_2,
                    'custom_off' => $product->custom_off_2,
                    'market_off' => $product->market_off_2,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase_2,
                    'market_minimum_purchase' => $product->market_minimum_purchase_2,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase_2,
                    'status' => $product->status,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;

            case "Saler":
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'number_sales' => $product->number_sales,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'main_inventory' => $product->main_inventory_3,
                    'custom_inventory' => $product->custom_inventory_3,
                    'market_inventory' => $product->market_inventory_3,
                    'views' => $product->views,
                    'main_price' => $product->main_price_3,
                    'custom_price' => $product->custom_price_3,
                    'market_price' => $product->market_price_3,
                    'main_off' => $product->main_off_3,
                    'custom_off' => $product->custom_off_3,
                    'market_off' => $product->market_off_3,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase_3,
                    'market_minimum_purchase' => $product->market_minimum_purchase_3,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase_3,
                    'status' => $product->status,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;

            default:
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'number_sales' => $product->number_sales,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'main_inventory' => $product->main_inventory,
                    'custom_inventory' => $product->custom_inventory,
                    'market_inventory' => $product->market_inventory,
                    'views' => $product->views,
                    'main_price' => $product->main_price,
                    'custom_price' => $product->custom_price,
                    'market_price' => $product->market_price,
                    'main_off' => $product->main_off,
                    'custom_off' => $product->custom_off,
                    'market_off' => $product->market_off,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase,
                    'market_minimum_purchase' => $product->market_minimum_purchase,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase,
                    'status' => $product->status,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'product' => $res,
                'images' =>  $images
            ]
        ], Response::HTTP_OK);
    }

    public function getProductById($id)
    {
        $product = Product::findOrFail($id);
        $cars = ProductDefinedCar::where("product_id", $product->id)->get();
        $images = $product->image['url'] ?? [];
        $properties = ProductsProperties::select("id", "title", "value", "child")->where("product_id", $product->id)->get();
        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'product' => $product,
                'images' => $images,
                'properties' => $properties,
                'cars' => $cars
            ]
        ], Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:products',
            "market_unique_identifier" => 'nullable|string|unique:products,market_unique_identifier',
            "main_unique_identifier" => 'nullable|string|unique:products,main_unique_identifier',
            "custom_unique_identifier" => 'nullable|string|unique:products,custom_unique_identifier',
            'commercial_code' => 'nullable|unique:products,commercial_code',
            'technical_code' => 'nullable|unique:products,technical_code',
            'images.*' => 'required|string',
            'short_body' => 'required|string',
            'long_body' => 'nullable|string',
            'main_price' => 'required|numeric',
            'main_price_2' => 'required|numeric',
            'main_price_3' => 'required|numeric',
            'custom_price' => 'required|numeric',
            'custom_price_2' => 'required|numeric',
            'custom_price_3' => 'required|numeric',
            'market_price' => 'required|numeric',
            'market_price_2' => 'required|numeric',
            'market_price_3' => 'required|numeric',
            'category_id' => 'required|numeric|exists:products_categories,id',
            'brand_id' => 'required|numeric|exists:products_brands,id',
            'country_id' => 'required|numeric|exists:product_country_builders,id',
            'cars' => 'nullable|array',
            'video_url' => 'nullable|string',
            'is_amazing' => 'required|boolean',
            'amazing_start' => 'nullable|date_format:Y-m-d H:i:s',
            'amazing_expire' => 'nullable|date_format:Y-m-d H:i:s|after:amazing_start',
            'amazing_off' => 'nullable|numeric',
            'main_inventory' => 'required|numeric',
            'main_inventory_2' => 'required|numeric',
            'main_inventory_3' => 'required|numeric',
            'market_inventory' => 'required|numeric',
            'market_inventory_2' => 'required|numeric',
            'market_inventory_3' => 'required|numeric',
            'custom_inventory' => 'required|numeric',
            'custom_inventory_2' => 'required|numeric',
            'custom_inventory_3' => 'required|numeric',
            'main_off' => 'nullable|numeric',
            'main_off_2' => 'nullable|numeric',
            'main_off_3' => 'nullable|numeric',
            'market_off' => 'nullable|numeric',
            'market_off_2' => 'nullable|numeric',
            'market_off_3' => 'nullable|numeric',
            'custom_off' => 'nullable|numeric',
            'custom_off_2' => 'nullable|numeric',
            'custom_off_3' => 'nullable|numeric',
            'main_minimum_purchase' => 'nullable|numeric',
            'main_minimum_purchase_2' => 'nullable|numeric',
            'main_minimum_purchase_3' => 'nullable|numeric',
            'market_minimum_purchase' => 'nullable|numeric',
            'market_minimum_purchase_2' => 'nullable|numeric',
            'market_minimum_purchase_3' => 'nullable|numeric',
            'custom_minimum_purchase' => 'nullable|numeric',
            'custom_minimum_purchase_2' => 'nullable|numeric',
            'custom_minimum_purchase_3' => 'nullable|numeric',
            'isFreeDelivery' => 'required|boolean',
            'isReadyToSend' => 'required|boolean',
            'special_offer' => 'required|boolean',
            'preparationTime' => 'nullable|numeric',
            'meta_tag_title' => 'nullable|string', 
            'meta_tag_keys.*' => 'nullable|string', 
            'meta_tag_canonical' => 'nullable|string', 
            'meta_tag_description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->unique_identifier) {
            if (Product::where("unique_identifier", $request->unique_identifier)->exists()) {
                return response()->json([
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'شناسه یکتا محصول قبلا تعریف شده است.',
                ], Response::HTTP_OK);
            }
        }

        $category = ProductsCategories::where("id", $request->category_id)->first();
        if ($category) {
            $category_name = $category->title;
            $category->increment('count', 1);
        }

        $brand = ProductsBrands::where("id", $request->brand_id)->first();
        if ($brand) {
            $brand_name = $brand->title;
            $brand->first()->increment('count', 1);
        }

        $country = ProductCountryBuilders::where("id", $request->country_id)->first();
        if ($country) {
            $country_name = $country->title;
            $country->increment('count', 1);
        }

        $product = Product::create([
            "market_unique_identifier" => $request->market_unique_identifier,
            "main_unique_identifier" => $request->main_unique_identifier,
            "custom_unique_identifier" => $request->custom_unique_identifier,
            'commercial_code' => $request->commercial_code,
            'technical_code' => $request->technical_code,
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'isFreeDelivery' => $request->isFreeDelivery,
            'main_price' => $request->main_price,
            'main_price_2' => $request->main_price_2,
            'main_price_3' => $request->main_price_3,
            'custom_price' => $request->custom_price,
            'custom_price_2' => $request->custom_price_2,
            'custom_price_3' => $request->custom_price_3,
            'market_price' => $request->market_price,
            'market_price_2' => $request->market_price_2,
            'market_price_3' => $request->market_price_3,
            'category_id' => $request->category_id,
            'category_name' => $category_name,
            'brand_id' => $request->brand_id,
            'brand_name' => $brand_name,
            'country_id' => $request->country_id,
            'country_name' => $country_name,
            'car_id' => $request->car_id,
            'car_name' => $request->car_name,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'is_amazing' => $request->is_amazing,
            'amazing_expire' => $request->amazing_expire,
            'amazing_start' => $request->amazing_start,
            'amazing_off' => $request->amazing_off,
            'main_inventory' => $request->main_inventory,
            'main_inventory_2' => $request->main_inventory_2,
            'main_inventory_3' => $request->main_inventory_3,
            'market_inventory' => $request->market_inventory,
            'market_inventory_2' => $request->market_inventory_2,
            'market_inventory_3' => $request->market_inventory_3,
            'custom_inventory' => $request->custom_inventory,
            'custom_inventory_2' => $request->custom_inventory_2,
            'custom_inventory_3' => $request->custom_inventory_3,
            'main_off' => $request->main_off,
            'main_off_2' => $request->main_off,
            'main_off_3' => $request->main_off,
            'market_off' => $request->market_off,
            'market_off_2' => $request->market_off_2,
            'market_off_3' => $request->market_off_3,
            'custom_off' => $request->custom_off,
            'custom_off_2' => $request->custom_off_2,
            'custom_off_3' => $request->custom_off_3,
            'main_minimum_purchase' => $request->main_minimum_purchase,
            'main_minimum_purchase_2' => $request->main_minimum_purchase_2,
            'main_minimum_purchase_3' => $request->main_minimum_purchase_3,
            'market_minimum_purchase' => $request->market_minimum_purchase,
            'market_minimum_purchase_2' => $request->market_minimum_purchase_2,
            'market_minimum_purchase_3' => $request->market_minimum_purchase_3,
            'custom_minimum_purchase' => $request->custom_minimum_purchase,
            'custom_minimum_purchase_2' => $request->custom_minimum_purchase_2,
            'custom_minimum_purchase_3' => $request->custom_minimum_purchase_3,
            'tags' => $request->tags,
            'isReadyToSend' => $request->isReadyToSend,
            'preparationTime' => $request->preparationTime,
            'special_offer' => $request->special_offer,
            'meta_tag_title' => $request->meta_tag_title, 
            'meta_tag_keys' => $request->meta_tag_keys, 
            'meta_tag_canonical' => $request->meta_tag_canonical, 
            'meta_tag_description' => $request->meta_tag_description
        ]);

        if (filled($request->cars)) {
            foreach ($request->cars as $item) {
                ProductDefinedCar::create([
                    'country_id' => $item['countryId'],
                    'country' => $item['countryTitle'],
                    'company_id' => $item['companyId'],
                    'company_name' => $item['companyTitle'],
                    "car_id" => $item['carId'] ?? null,
                    "car_name" => $item['carTitle'] ?? null,
                    "model_id" => $item['modelId'] ?? null,
                    "model_name" => $item['typeId'] ?? null,
                    "year_id" => $item['typeId'] ?? null,
                    "year_name" => $item['typeTitle'] ?? null,
                    "product_id" => $product->id
                ]);
            }
        }

        if (filled($request->images)) {
            ProductsImages::create([
                "product_id" => $product->id,
                'url' => $request->images,
            ]);
        }

        if (filled($request->properties)) {
            foreach ($request->properties as $item) {
                ProductsProperties::create([
                    "product_id" => $product->id,
                    "title" => $item['title'],
                    "value" => $item['value'],
                    "child" => $item['child']
                ]);
            }
        }

        $admin = auth()->guard('admin')->user();
        if ($admin) {
            EventLogs::addToLog([
                'subject' => "افزودن محصول جدید: " . $product->title . ", توسط " . $admin->first_name . " " . $admin->last_nam,
                'body' => $product,
                'user_id' => $admin->id,
                'user_name' => $admin->first_name . " " . $admin->last_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت ذخیره شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Product $product)
    {
        if (!$product) {
            return response()->json([
                'success' => true,
                'statusCode' => 422,
                'message' => 'محصولی یافت نشد!',
                'data' => null
            ], Response::HTTP_OK);
        }

        $role = "Normal";

        if ($request->header('Authorization')) {
            $response1 = explode(' ', $request->header('Authorization'));

            if ($response1[1] and $response1[1] != "undefined") {
                $token = trim($response1[1]);
                $user = JWTAuth::setToken($token)->toUser();

                if ($user) {
                    $role = $user->role;
                }
            }
        }

        $images = ProductsImages::select("id", "url")->where("product_id", $product->id)->first();

        $properties = ProductsProperties::select("title", "value", "child")->where("product_id", $product->id)->get();

        $isFavorite = Favorites::where("product_id", $product->id)
            ->where('uuid', $request->uuid)->exists();

        $shopping = Cart::where("product_id", $product->id)
            ->where('uuid', $request->uuid)
            ->where('status', 0)
            ->select('id', 'quantity', 'grade')
            ->first();

        $cars = productDefinedCar::where('product_id', $product->id)->get();

        $isOffer = false;
        $offer = 0;
        $expire = null;
        if ($product->is_amazing == 1) {
            $startTime = Carbon::parse($product->amazing_start);
            $endTime = Carbon::parse($product->amazing_expire);
            $now = Carbon::now();

            if ($now->between($startTime, $endTime)) {
                $isOffer = true;
                $offer = $product->amazing_off;
                $expire = $product->amazing_expire;
            }
        }

        switch ($role) {
            case "Marketer":
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'number_sales' => $product->number_sales,
                    'main_inventory' => $product->main_inventory_2,
                    'custom_inventory' => $product->custom_inventory_2,
                    'market_inventory' => $product->market_inventory_2,
                    'views' => $product->views,
                    'main_price' => $product->main_price_2,
                    'custom_price' => $product->custom_price_2,
                    'market_price' => $product->market_price_2,
                    'main_off' => $product->main_off_2,
                    'custom_off' => $product->custom_off_2,
                    'market_off' => $product->market_off_2,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase_2,
                    'market_minimum_purchase' => $product->market_minimum_purchase_2,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase_2,
                    'status' => $product->status,
                    'isReadyToSend' => $product->isReadyToSend,
                    'preparationTime' => $product->preparationTime,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;

            case "Saler":
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'number_sales' => $product->number_sales,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'main_inventory' => $product->main_inventory_3,
                    'custom_inventory' => $product->custom_inventory_3,
                    'market_inventory' => $product->market_inventory_3,
                    'views' => $product->views,
                    'main_price' => $product->main_price_3,
                    'custom_price' => $product->custom_price_3,
                    'market_price' => $product->market_price_3,
                    'main_off' => $product->main_off_3,
                    'custom_off' => $product->custom_off_3,
                    'market_off' => $product->market_off_3,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase_3,
                    'market_minimum_purchase' => $product->market_minimum_purchase_3,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase_3,
                    'status' => $product->status,
                    'isReadyToSend' => $product->isReadyToSend,
                    'preparationTime' => $product->preparationTime,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;

            default:
                $res = [
                    'id' => $product->id,
                    'technical_code' => $product->technical_code,
                    'commercial_code' => $product->commercial_code,
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'short_body' => $product->short_body,
                    'long_body' => $product->long_body,
                    'rating' => $product->rating,
                    'number_sales' => $product->number_sales,
                    'isFreeDelivery' => $product->isFreeDelivery,
                    'main_inventory' => $product->main_inventory,
                    'custom_inventory' => $product->custom_inventory,
                    'market_inventory' => $product->market_inventory,
                    'views' => $product->views,
                    'main_price' => $product->main_price,
                    'custom_price' => $product->custom_price,
                    'market_price' => $product->market_price,
                    'main_off' => $product->main_off,
                    'custom_off' => $product->custom_off,
                    'market_off' => $product->market_off,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'brand_id' => $product->brand_id,
                    'brand_name' => $product->brand_name,
                    'country_id' => $product->country_id,
                    'country_name' => $product->country_name,
                    'video_url' => $product->video_url,
                    'main_minimum_purchase' => $product->main_minimum_purchase,
                    'market_minimum_purchase' => $product->market_minimum_purchase,
                    'custom_minimum_purchase' => $product->custom_minimum_purchase,
                    'status' => $product->status,
                    'isReadyToSend' => $product->isReadyToSend,
                    'preparationTime' => $product->preparationTime,
                    'updated_at' => $product->updated_at,
                    "isOffer" => $isOffer,
                    "offer" => $offer,
                    "expire" => $expire
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'statusCode' => 201,
            'message' => 'عملیات با موفقیت انجام شد.',
            'data' => [
                'product' => $res,
                'images' => $images,
                'properties' => $properties,
                'isFavorite' =>  $isFavorite,
                'shopping' => $shopping,
                'cars' => $cars
            ]
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|unique:products,title,' . $product->id,
            "market_unique_identifier" => 'nullable|string|unique:products,market_unique_identifier,' . $product->id,
            "main_unique_identifier" => 'nullable|string|unique:products,main_unique_identifier,' . $product->id,
            "custom_unique_identifier" => 'nullable|string|unique:products,custom_unique_identifier,' . $product->id,
            'commercial_code' => 'nullable|unique:products,commercial_code,' . $product->id,
            'technical_code' => 'nullable|unique:products,technical_code,' . $product->id,
            'images.*' => 'nullable|string',
            'short_body' => 'required|string',
            'long_body' => 'nullable|string',
            'main_price' => 'required|numeric',
            'main_price_2' => 'required|numeric',
            'main_price_3' => 'required|numeric',
            'custom_price' => 'required|numeric',
            'custom_price_2' => 'required|numeric',
            'custom_price_3' => 'required|numeric',
            'market_price' => 'required|numeric',
            'market_price_2' => 'required|numeric',
            'market_price_3' => 'required|numeric',
            'category_id' => 'required|numeric|exists:products_categories,id',
            'brand_id' => 'required|numeric|exists:products_brands,id',
            'country_id' => 'required|numeric|exists:product_country_builders,id',
            'cars' => 'nullable|array',
            'video_url' => 'nullable|string',
            'is_amazing' => 'required|boolean',
            'amazing_start' => 'nullable|date_format:Y-m-d H:i:s',
            'amazing_expire' => 'nullable|date_format:Y-m-d H:i:s|after:amazing_start',
            'amazing_off' => 'nullable|numeric',
            'main_inventory' => 'required|numeric',
            'main_inventory_2' => 'required|numeric',
            'main_inventory_3' => 'required|numeric',
            'market_inventory' => 'required|numeric',
            'market_inventory_2' => 'required|numeric',
            'market_inventory_3' => 'required|numeric',
            'custom_inventory' => 'required|numeric',
            'custom_inventory_2' => 'required|numeric',
            'custom_inventory_3' => 'required|numeric',
            'main_off' => 'nullable|numeric',
            'main_off_2' => 'nullable|numeric',
            'main_off_3' => 'nullable|numeric',
            'market_off' => 'nullable|numeric',
            'market_off_2' => 'nullable|numeric',
            'market_off_3' => 'nullable|numeric',
            'custom_off' => 'nullable|numeric',
            'custom_off_2' => 'nullable|numeric',
            'custom_off_3' => 'nullable|numeric',
            'main_minimum_purchase' => 'nullable|numeric',
            'main_minimum_purchase_2' => 'nullable|numeric',
            'main_minimum_purchase_3' => 'nullable|numeric',
            'market_minimum_purchase' => 'nullable|numeric',
            'market_minimum_purchase_2' => 'nullable|numeric',
            'market_minimum_purchase_3' => 'nullable|numeric',
            'custom_minimum_purchase' => 'nullable|numeric',
            'custom_minimum_purchase_2' => 'nullable|numeric',
            'custom_minimum_purchase_3' => 'nullable|numeric',
            'isFreeDelivery' => 'required|boolean',
            'isReadyToSend' => 'required|boolean',
            'special_offer' => 'required|boolean',
            'preparationTime' => 'nullable|numeric',
            'meta_tag_title' => 'nullable|string', 
            'meta_tag_keys.*' => 'nullable|string', 
            'meta_tag_canonical' => 'nullable|string', 
            'meta_tag_description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        if ($request->category_id != $product->category_id) {
            $newCategory = ProductsCategories::where("id", $request->category_id)->first();
            $prevCategory = ProductsCategories::select("count")->where("id", $product->category_id)->first();
            if ($prevCategory) {
                if ($prevCategory->count > 0) {
                    $prevCategory->decrement('count', 1);
                }
            }
            $newCategory->increment('count', 1);
            $category_name = $newCategory->title;
        } else {
            $category_name = $product->category_name;
        }

        if ($request->brand_id != $product->brand_id) {
            $newBrand = ProductsBrands::where("id", $request->brand_id)->first();
            $prevBrand = ProductsBrands::where("id", $product->brand_id)->first();
            if ($prevBrand) {
                if ($prevBrand->count > 0) {
                    $prevBrand->decrement('count', 1);
                }
            }
            $newBrand->increment('count', 1);
            $brand_name = $newBrand->title;
        } else{
            $brand_name = $product->brand_name;
        }

        if ($request->country_id != $product->country_id) {
            $newCountry = ProductCountryBuilders::where("id", $request->country_id)->first();
            $prevcCountry = ProductCountryBuilders::where("id", $product->country_id)->first();
            if ($prevcCountry) {
                if ($prevBrand->count > 0) {
                    $prevBrand->decrement('count', 1);
                }
            }
            $newCountry->increment('count', 1);
            $country_name = $newCountry->title;
        } else{
            $country_name = $product->country_name; 
        }

        $isPriceUpdate = false;

        if (
            $product->main_price != $request->main_price or
            $product->main_price_2 != $request->main_price_2 or
            $product->main_price_3 != $request->main_price_3 or
            $product->custom_price != $request->custom_price or
            $product->custom_price_2 != $request->custom_price_2 or
            $product->custom_price_3 != $request->custom_price_3 or
            $product->market_price != $request->market_price or
            $product->market_price_2 != $request->market_price_2 or
            $product->market_price_3 != $request->market_price_3
        ) {
            $isPriceUpdate = true;
        }

        $product->update([
            'title' => $request->title,
            'slug' => GenerateSlug::get($request->title),
            "market_unique_identifier" => $request->market_unique_identifier,
            "main_unique_identifier" => $request->main_unique_identifier,
            "custom_unique_identifier" => $request->custom_unique_identifier,
            'commercial_code' => $request->commercial_code,
            'technical_code' => $request->technical_code,
            'short_body' => $request->short_body,
            'long_body' => $request->long_body,
            'isFreeDelivery' => $request->isFreeDelivery,
            'main_price' => $request->main_price,
            'main_price_2' => $request->main_price_2,
            'main_price_3' => $request->main_price_3,
            'custom_price' => $request->custom_price,
            'custom_price_2' => $request->custom_price_2,
            'custom_price_3' => $request->custom_price_3,
            'market_price' => $request->market_price,
            'market_price_2' => $request->market_price_2,
            'market_price_3' => $request->market_price_3,
            'category_id' => $request->category_id,
            'category_name' => $category_name,
            'brand_id' => $request->brand_id,
            'brand_name' => $brand_name,
            'country_id' => $request->country_id,
            'country_name' => $country_name,
            'video_url' => $request->video_url,
            'status' => $request->status,
            'is_amazing' => $request->is_amazing,
            'amazing_expire' => $request->amazing_expire,
            'amazing_start' => $request->amazing_start,
            'amazing_off' => $request->amazing_off,
            'main_inventory' => $request->main_inventory,
            'main_inventory_2' => $request->main_inventory_2,
            'main_inventory_3' => $request->main_inventory_3,
            'market_inventory' => $request->market_inventory,
            'market_inventory_2' => $request->market_inventory_2,
            'market_inventory_3' => $request->market_inventory_3,
            'custom_inventory' => $request->custom_inventory,
            'custom_inventory_2' => $request->custom_inventory_2,
            'custom_inventory_3' => $request->custom_inventory_3,
            'main_off' => $request->main_off,
            'main_off_2' => $request->main_off,
            'main_off_3' => $request->main_off,
            'market_off' => $request->market_off,
            'market_off_2' => $request->market_off_2,
            'market_off_3' => $request->market_off_3,
            'custom_off' => $request->custom_off,
            'custom_off_2' => $request->custom_off_2,
            'custom_off_3' => $request->custom_off_3,
            'main_minimum_purchase' => $request->main_minimum_purchase,
            'main_minimum_purchase_2' => $request->main_minimum_purchase_2,
            'main_minimum_purchase_3' => $request->main_minimum_purchase_3,
            'market_minimum_purchase' => $request->market_minimum_purchase,
            'market_minimum_purchase_2' => $request->market_minimum_purchase_2,
            'market_minimum_purchase_3' => $request->market_minimum_purchase_3,
            'custom_minimum_purchase' => $request->custom_minimum_purchase,
            'custom_minimum_purchase_2' => $request->custom_minimum_purchase_2,
            'custom_minimum_purchase_3' => $request->custom_minimum_purchase_3,
            'tags' => $request->tags,
            'isReadyToSend' => $request->isReadyToSend,
            'preparationTime' => $request->preparationTime,
            'special_offer' => $request->special_offer,
            'meta_tag_title' => $request->meta_tag_title, 
            'meta_tag_keys' => $request->meta_tag_keys, 
            'meta_tag_canonical' => $request->meta_tag_canonical, 
            'meta_tag_description' => $request->meta_tag_description
        ]);

        if ($isPriceUpdate == true) {
            $this->update_user_carts($product);
        }

        if (filled($request->cars)) {
            ProductDefinedCar::where("product_id",$product->id)->delete();
            foreach ($request->cars as $item) {
                ProductDefinedCar::create([
                    'country_id' => $item['countryId'],
                    'country' => $item['countryTitle'],
                    'company_id' => $item['companyId'],
                    'company_name' => $item['companyTitle'],
                    "car_id" => $item['carId'] ?? null,
                    "car_name" => $item['carTitle'] ?? null,
                    "model_id" => $item['modelId'] ?? null,
                    "model_name" => $item['typeId'] ?? null,
                    "year_id" => $item['typeId'] ?? null,
                    "year_name" => $item['typeTitle'] ?? null,
                    "product_id" => $product->id
                ]);
            }
        }

        if (filled($request->images)) {
            ProductsImages::where("product_id",$product->id)->delete();
            ProductsImages::create([
                "product_id" => $product->id,
                'url' => $request->images,
            ]);
        }

        if (filled($request->properties)) {
            ProductsProperties::where("product_id", $product->id)->delete();
            foreach ($request->properties as $item) {
                ProductsProperties::create([
                    "product_id" => $product->id,
                    "title" => $item['title'],
                    "value" => $item['value'],
                    "child" => $item['child']
                ]);
            }
        }

        $admin = auth()->guard('admin')->user();
        if ($admin) {
            EventLogs::addToLog([
                'subject' => "ویرایش محصول: ".$product->title .', توسط '.$admin->first_name . " " . $admin->last_name,
                'body' => $product,
                'user_id' => $admin->id,
                'user_name' => $admin->first_name . " " . $admin->last_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    public function update_user_carts($product)
    {
        $cart = Cart::where("status", 0)
            ->where("product_id", $product->id)
            ->get();

        foreach ($cart as $item) {
            $price = 0;
            switch ($item->user_role) {
                case 'Marketer':
                    switch ($item->grade_type) {
                        case 1:
                            $price = $product->main_price_2;
                            $off  = $product->main_off_2;
                            break;

                        case 2:
                            $price = $product->custom_price_2;
                            $off  = $product->custom_off_2;
                            break;

                        case 3:
                            $price = $product->market_price_2;
                            $off  = $product->market_off_2;
                            break;
                    }
                    break;

                case 'Saler':
                    switch ($item->grade_type) {
                        case 1:
                            $price = $product->main_price_3;
                            $off  = $product->main_off_3;
                            break;

                        case 2:
                            $price = $product->custom_price_3;
                            $off  = $product->custom_off_3;
                            break;

                        case 3:
                            $price = $product->market_price_;
                            $off  = $product->market_off_3;
                            break;
                    }
                    break;

                default:
                    switch ($item->grade_type) {
                        case 1:
                            $price = $product->main_price;
                            $off  = $product->main_off;
                            break;

                        case 2:
                            $price = $product->custom_price;
                            $off  = $product->custom_off;
                            break;

                        case 3:
                            $price = $product->market_price;
                            $off  = $product->market_off;
                            break;
                    }
                    break;
            }

            if ($item->saved_price != $price) {
                $item->update([
                    'saved_price' => $price,
                    'saved_off' => $off,
                    'isPriceChanges' => 1
                ]);
            }
        }
    }

    public function price_fluctuations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
            'percent' => 'required|numeric',
            'users1' => 'required|boolean',
            'users2' => 'required|boolean',
            'users3' => 'required|boolean',
            'main_grade' => 'required|boolean',
            'custom_grade' => 'required|boolean',
            'market_grade' => 'required|boolean',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => $validator->errors()
            ], Response::HTTP_OK);
        }

        if ($request->percent <= 0) {
            return response()->json([
                'success' => false,
                'statusCode' => 422,
                'message' => "درصد وارد شده نامعتبر است."
            ], Response::HTTP_OK);
        }

        $products = Product::where('id', 208)->get();

        foreach ($products as $product) {
            if ($request->type == 1) {

                if ($request->users1 == true) {

                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price" => $product->main_price + ($product->main_price * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price" => $product->custom_price + ($product->custom_price * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price" => $product->market_price + ($product->market_price * ((int)$request->percent / 100))
                        ]);
                    }
                }

                if ($request->users2 == true) {
                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price_2" => $product->main_price_2 + ($product->main_price_2 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price_2" => $product->custom_price_2 + ($product->custom_price_2 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price_2" => $product->market_price_2 + ($product->market_price_2 * ((int)$request->percent / 100))
                        ]);
                    }
                }

                if ($request->users3 == true) {
                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price_3" => $product->main_price_3 + ($product->main_price_3 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price_3" => $product->custom_price_3 + ($product->custom_price_3 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price_3" => $product->market_price_3 + ($product->market_price_3 * ((int)$request->percent / 100))
                        ]);
                    }
                }
            } else {

                if ($request->users1 == true) {

                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price" => $product->main_price - ($product->main_price * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price" => $product->custom_price - ($product->custom_price * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price" => $product->market_price - ($product->market_price * ((int)$request->percent / 100))
                        ]);
                    }
                }

                if ($request->users2 == true) {
                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price_2" => $product->main_price_2 - ($product->main_price_2 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price_2" => $product->custom_price_2 - ($product->custom_price_2 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price_2" => $product->market_price_2 - ($product->market_price_2 * ((int)$request->percent / 100))
                        ]);
                    }
                }

                if ($request->users3 == true) {
                    if ($request->main_grade == true) {
                        $product->update([
                            "main_price_3" => $product->main_price_3 - ($product->main_price_3 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->custom_grade == true) {
                        $product->update([
                            "custom_price_3" => $product->custom_price_3 - ($product->custom_price_3 * ((int)$request->percent / 100))
                        ]);
                    }

                    if ($request->market_grade == true) {
                        $product->update([
                            "market_price_3" => $product->market_price_3 - ($product->market_price_3 * ((int)$request->percent / 100))
                        ]);
                    }
                }
            }

            $this->update_user_carts($product);
        }

        if ($request->header('agent')) {
            $admin = Admin::where("id", $request->header('agent'))->first();

            if ($admin) {
                EventLogs::addToLog([
                    'subject' => "اعمال نوسانات قیمت",
                    'body' => $request->type == 1 ? "افزایش نرخ قیمت محصولات به میزان" . $request->percent . " درصد"
                        : "کاهش نرخ قیمت محصولات به میزان" . $request->percent . " درصد",
                    'user_id' => $admin->id,
                    'user_name' => $admin->first_name . " " . $admin->last_name,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'statusCode' => 200,
            'message' => 'عملیات با موفقیت انجام شد.',
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($productId)
    {
        $product = Product::find($productId);
        if (Cart::where("product_id", $product->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'به دلیل وابستگی با سایر بخش ها امکان حذف وجود ندارد.'
            ], 403);
        }

        if ($product->brand_id) {
            ProductsBrands::where("id", $product->brand_id)->first()->decrement('count', 1);
        }

        if ($product->country_id) {
            ProductCountryBuilders::where("id", $product->country_id)->first()->decrement('count', 1);
        }

        ProductsImages::where("product_id", $product->id)->delete();
        ProductsProperties::where('product_id', $product->id)->delete();
        ProductDefinedCar::where('product_id', $product->id)->delete();

        $product->delete();

        $admin = auth()->guard('admin')->user();
        if ($admin) {
            EventLogs::addToLog([
                'subject' => ":حذف محصول" . $product->title . ', توسط' . $admin->first_name . " " . $admin->last_name,
                'body' => $product,
                'user_id' => $admin->id,
                'user_name' => $admin->first_name . " " . $admin->last_name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'محصول با موفقیت حذف شد.',
        ], Response::HTTP_OK);
    }
}
