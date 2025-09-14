<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Http;


class CartController extends Controller
{
    // ... (Method lain yang tidak berubah seperti index, add_to_cart, dll tetap di sini)
    public function getServices(Request $request)
    {
        $courier = $request->courier;
        $destination = $request->destination;
        $weight = $request->weight;

        $response = Http::withToken(config('services.biteship.api_key'))
            ->post('https://api.biteship.com/v1/rates/couriers', [
                'origin_area_id' => config('services.biteship.origin'), // area_id toko kamu
                'destination_postal_code' => $destination,
                'courier_code' => $courier,
                'items' => [
                    [
                        'name' => 'Produk Checkout',
                        'weight' => $weight,
                        'quantity' => 1
                    ]
                ]
            ]);

        if ($response->failed()) {
            return response()->json(['pricing' => []], 500);
        }

        return response()->json([
            'pricing' => $response->json()['pricing'] ?? []
        ]);
    }


    public function getShippingRates(Request $request, BiteshipService $biteship)
    {
        $cartItems = CartItem::where('user_id', Auth::id())->with('product')->get();

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                "name" => $item->product->name,
                "description" => $item->product->name,
                "length" => 10,
                "width" => 10,
                "height" => 10,
                "weight" => 500, // idealnya ambil dari product
                "quantity" => $item->quantity
            ];
        }

        $rates = $biteship->getCourierRates($request->destination, $items);

        // Pastikan response mengandung 'pricing'
        return response()->json([
            'pricing' => $rates['pricing'] ?? [],
        ]);
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $items = CartItem::where('user_id', $userId)->orderBy('created_at', 'DESC')->get();
        $productId = $request->product_id;
        $product = Product::where('id', $productId)->first();

        $subtotal = 0;

        foreach ($items as $item) {
            $price = $item->price;
            $quantity = $item->quantity;
            $subtotal += $price * $quantity;
        }

        $discount = 0;
        if (session()->has('coupon')) {
            $this->calculateDiscount();
        }

        $total = $subtotal - $discount;

        return view('cart', compact('productId', 'product', 'items', 'subtotal', 'discount', 'total'));
    }

    public function add_to_cart(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $productId = $request->id;
        $product = Product::find($productId);

        if (!$product || $product->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Barang tidak tersedia atau stok tidak mencukupi!');
        }

        $item = CartItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            if ($product->quantity < ($item->quantity + $request->quantity)) {
                return redirect()->back()->with('error', 'Stok produk tidak mencukupi!');
            }
            $item->increment('quantity', $request->quantity ?? 1);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $request->quantity ?? 1,
                'price' => $product->sale_price > 0 ? $product->sale_price : $product->regular_price,
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function buyNow(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->id);

        if (!$product || $product->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi!');
        }

        session()->put('buy_now_item', [
            'product_id' => $request->id,
            'quantity' => $request->quantity,
        ]);

        // Pastikan session lain bersih sebelum ke checkout
        session()->forget('selected_checkout_items');

        return redirect()->route('cart.checkout');
    }

    public function decrease_cart_quantity(Request $request, $id)
    {
        $userId = Auth::id();
        // Cari item berdasarkan ID dan pastikan milik user yang sedang login
        $item = CartItem::where('id', $id)->where('user_id', $userId)->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        // Hanya kurangi jika kuantitas lebih dari 1
        if ($item->quantity > 1) {
            $item->decrement('quantity', 1);

            // Hitung ulang diskon jika ada kupon yang aktif
            if (Session::has('coupon')) {
                $this->calculateDiscount();
            }

            return redirect()->back()->with('success', 'Kuantitas produk berhasil diperbarui.');
        }

        // Jika kuantitas sudah 1, jangan lakukan apa-apa
        return redirect()->back()->with('error', 'Kuantitas minimum adalah 1. Gunakan tombol hapus untuk menghilangkan produk.');
    }

    /**
     * [BARU] Memperbarui kuantitas item dari input manual.
     * Mencegah kuantitas diisi kurang dari 1.
     */
    public function update_cart_quantity(Request $request, $id)
    {
        // Validasi input dari pengguna
        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ], [
            'quantity.min' => 'Kuantitas minimum untuk produk adalah 1.'
        ]);

        $userId = Auth::id();
        $item = CartItem::where('id', $id)->where('user_id', $userId)->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        // Cek ketersediaan stok sebelum update
        $product = Product::find($item->product_id);
        if ($product->quantity < $request->quantity) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi!');
        }

        // Jika validasi dan stok aman, baru lakukan pembaruan
        $item->quantity = $request->quantity;
        $item->save();

        // Hitung ulang diskon jika ada kupon yang aktif
        if (Session::has('coupon')) {
            $this->calculateDiscount();
        }

        return redirect()->back()->with('success', 'Kuantitas produk berhasil diperbarui.');
    }

    /**
     * [BARU] Menangani item yang dipilih dari keranjang untuk di-checkout.
     */
    public function checkoutSelected(Request $request)
    {
        $selectedProductIds = $request->input('selected_products', []);

        if (empty($selectedProductIds)) {
            return redirect()->back()->with('error', 'Silakan pilih produk yang akan di-checkout.');
        }

        $userId = Auth::id();
        $selectedItems = CartItem::where('user_id', $userId)
            ->whereIn('id', $selectedProductIds)
            ->get();

        if ($selectedItems->isEmpty()) {
            return redirect()->back()->with('error', 'Produk yang dipilih tidak valid.');
        }

        // Simpan item yang dipilih ke sesi untuk diproses di halaman checkout
        session()->put('selected_checkout_items', $selectedItems);

        // Pastikan session buyNow bersih
        session()->forget('buy_now_item');

        return redirect()->route('cart.checkout');
    }

    /**
     * [MODIFIKASI] Menampilkan halaman checkout dengan 3 skenario berbeda.
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->first();

        // --- Ambil daftar kurir dari Biteship ---
        $response = Http::withToken(config('services.biteship.api_key'))
            ->get('https://api.biteship.com/v1/couriers');

        $couriers = $response->successful() ? $response->json()['couriers'] : [];


        // Skenario 1: Checkout dari "Beli Sekarang"
        if (session()->has('buy_now_item')) {
            $buyNowData = session('buy_now_item');
            $product = Product::find($buyNowData['product_id']);
            $quantity = $buyNowData['quantity'];

            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('shop.index')->with('error', 'Produk tidak ditemukan.');
            }

            $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;
            $subtotal = $price * $quantity;
            $total = $subtotal;

            $item = new \stdClass();
            $item->product = $product;
            $item->quantity = $quantity;
            $item->subtotal = $subtotal;
            $items = collect([$item]);

            $this->setAmountForCheckout(true);

            return view('checkout', compact('address', 'items', 'subtotal', 'total'));
        }
        // Skenario 2: Checkout dari item yang dipilih di keranjang
        elseif (session()->has('selected_checkout_items')) {
            $items = session('selected_checkout_items');

            if ($items->isEmpty()) {
                return redirect()->route('cart.index')->with('info', 'Tidak ada item terpilih.');
            }

            $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
            $total = $subtotal;

            $this->setAmountForCheckout(false, $items); // Kirim item terpilih

            return view('checkout', compact('address', 'items', 'subtotal', 'total'));
        }
        // Skenario 3: Checkout dari seluruh isi Keranjang Belanja
        else {
            $items = CartItem::where('user_id', $user->id)->get();
            if ($items->isEmpty()) {
                return redirect()->route('cart.index')->with('info', 'Keranjang Anda kosong.');
            }

            $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);

            $this->calculateDiscount();

            if (Session::has('discounts')) {
                $subtotal = Session::get('discounts')['subtotal'];
                $total = Session::get('discounts')['total']; // Total sudah dihitung tanpa pajak
            } else {
                $total = $subtotal; // PAJAK DIHAPUS
            }

            $this->setAmountForCheckout(false);

            return view('checkout', compact('address', 'items', 'subtotal', 'total'));
        }
    }

    /**
     * [MODIFIKASI] Menyimpan pesanan ke database.
     */
    public function place_an_order(Request $request)
    {
        $request->validate(
            ['mode' => 'required|in:cod,transfer'],
            ['mode.required' => 'Silakan pilih metode pembayaran.']
        );

        $user_id = Auth::id();
        $address = Address::where('user_id', $user_id)->first();

        if (!$address) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'landmark' => 'required|string',
                'locality' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip' => 'required|string|max:10',
                'country' => 'required|string',
                'type' => 'required|in:Rumah,Kantor,Lainnya',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $address = new Address();
            $address->user_id = $user_id;
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->address = $request->address;
            $address->landmark = $request->landmark;
            $address->locality = $request->locality;
            $address->city = $request->city;
            $address->state = $request->state;
            $address->zip = $request->zip;
            $address->country = $request->country;
            $address->type = $request->type;
            $address->isdefault = 1;
            $address->save();
        }

        $checkout = Session::get('checkout');
        if (!$checkout) {
            return redirect()->route('shop.index')->with('error', 'Sesi checkout berakhir, silakan coba lagi.');
        }

        DB::beginTransaction();
        try {
            $order = new Order();
            $order->user_id = $user_id;
            $order->subtotal = $checkout['subtotal'];
            $order->discount = $checkout['discount'];
            $order->tax = 0;
            $order->total = $checkout['total'];
            $order->name = $address->name;
            $order->phone = $address->phone;
            $order->address = $address->address;
            $order->landmark = $address->landmark;
            $order->locality = $address->locality;
            $order->city = $address->city;
            $order->state = $address->state;
            $order->zip = $address->zip;
            $order->country = $address->country;
            $order->status = 'ordered';
            $order->save();

            // Logika untuk memproses OrderItems (tetap sama)
            if ($checkout['is_buy_now']) {
                $buyNowData = session('buy_now_item');
                $product = Product::find($buyNowData['product_id']);
                OrderItem::create(['product_id' => $buyNowData['product_id'], 'order_id' => $order->id, 'price' => $checkout['price'], 'quantity' => $buyNowData['quantity']]);
                $product->quantity -= $buyNowData['quantity'];
                $product->save();
            } elseif (isset($checkout['is_selected_checkout']) && $checkout['is_selected_checkout']) {
                $selectedItems = session('selected_checkout_items', collect());
                foreach ($selectedItems as $item) {
                    OrderItem::create(['product_id' => $item->product_id, 'order_id' => $order->id, 'price' => $item->price, 'quantity' => $item->quantity]);
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->quantity -= $item->quantity;
                        $product->save();
                    }
                }
            } else {
                $cartItems = CartItem::where('user_id', $user_id)->get();
                foreach ($cartItems as $item) {
                    OrderItem::create(['product_id' => $item->product_id, 'order_id' => $order->id, 'price' => $item->price, 'quantity' => $item->quantity]);
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->quantity -= $item->quantity;
                        $product->save();
                    }
                }
            }


            // Panggil fungsi pembayaran yang sesuai
            if ($request->mode == 'transfer') {
                return $this->processTransferOrder($order, $address);
            } else { // COD
                // Untuk COD, hapus keranjang sekarang karena tidak ada callback pembayaran
                CartItem::where('user_id', $user_id)->delete();
                return $this->processCodOrder($order);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.checkout')->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * [BARU] Memproses pesanan dengan metode pembayaran COD.
     */
    protected function processCodOrder(Order $order)
    {
        try {
            // 1. Buat transaksi
            $transaction = new Transaction();
            $transaction->user_id = $order->user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = 'cod';
            $transaction->status = 'pending';
            $transaction->save();

            // 2. Simpan order_id ke session untuk konfirmasi
            Session::put('order_id', $order->id);

            // 3. Commit perubahan ke database
            DB::commit();

            // 4. Bersihkan session yang tidak diperlukan
            Session::forget(['checkout', 'coupon', 'discounts', 'buy_now_item', 'selected_checkout_items']);

            // 5. Arahkan ke halaman konfirmasi pesanan
            return redirect()->route('cart.order.confirmation');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.checkout')->with('error', 'Gagal memproses pesanan COD: ' . $e->getMessage());
        }
    }

    /**
     * [BARU] Memproses pesanan dengan metode pembayaran Transfer (Midtrans).
     */
    protected function processTransferOrder(Order $order, Address $address)
    {
        try {
            MidtransConfig::$serverKey = config('midtrans.server_key');
            MidtransConfig::$isProduction = config('midtrans.is_production');
            MidtransConfig::$isSanitized = true;
            MidtransConfig::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id . '-' . time(),
                    'gross_amount' => $order->total,
                ],
                'customer_details' => [
                    'first_name' => $address->name,
                    'email' => Auth::user()->email,
                    'phone' => $address->phone,
                ],
            ];

            $snapToken = MidtransSnap::getSnapToken($params);

            $transaction = new Transaction();
            $transaction->user_id = $order->user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = 'transfer';
            $transaction->status = 'pending';
            $transaction->payment_token = $snapToken;
            $transaction->save();

            Session::put('order_id', $order->id);

            DB::commit();

            // [MODIFIKASI] Tambahkan order_id ke dalam response
            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order->id // <-- Tambahkan baris ini
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelPendingOrder(Request $request)
    {
        $orderId = $request->input('order_id');
        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'Order ID tidak ada.'], 400);
        }

        $userId = Auth::id();
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', 'ordered') // Pastikan statusnya masih awal
            ->first();

        if ($order) {
            // Cek status transaksi, hanya batalkan jika masih 'pending'
            $transaction = $order->transaction;
            if ($transaction && $transaction->status === 'pending') {
                DB::beginTransaction();
                try {
                    // 1. Kembalikan stok produk
                    foreach ($order->orderItems as $item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->quantity += $item->quantity;
                            $product->save();
                        }
                    }

                    // 2. Hapus order (order items dan transaction akan terhapus otomatis jika ada cascade delete)
                    // Jika tidak ada cascade, hapus manual:
                    $order->orderItems()->delete();
                    $order->transaction()->delete();
                    $order->delete();

                    DB::commit();

                    return response()->json(['status' => 'success', 'message' => 'Pesanan berhasil dibatalkan.']);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()], 500);
                }
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Pesanan tidak ditemukan atau sudah diproses.'], 404);
    }

    /**
     * [MODIFIKASI] Mengatur jumlah total untuk checkout.
     * Menerima parameter $items untuk kasus checkout pilihan.
     */
    public function setAmountForCheckout($isBuyNow = false, $items = null)
    {
        $user_id = Auth::id();

        if ($isBuyNow && session()->has('buy_now_item')) {
            // ... (Logika Buy Now tidak berubah)
            $buyNowData = session('buy_now_item');
            $product = Product::find($buyNowData['product_id']);
            $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;
            $subtotal = $price * $buyNowData['quantity'];
            $total = $subtotal;

            Session::put('checkout', [
                'is_buy_now' => true,
                'is_selected_checkout' => false,
                'discount' => 0,
                'subtotal' => $subtotal,
                'tax' => 0,
                'total' => $total,
                'price' => $price
            ]);
        }
        // Jika $items diberikan (dari checkout pilihan)
        elseif ($items !== null) {
            $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
            $total = $subtotal;
            Session::put('checkout', [
                'is_buy_now' => false,
                'is_selected_checkout' => true,
                'discount' => 0, // Logika diskon bisa ditambahkan di sini
                'subtotal' => $subtotal,
                'tax' => 0,
                'total' => $total
            ]);
        } else {
            // ... (Logika checkout seluruh keranjang tidak berubah)
            $cartItems = CartItem::where('user_id', $user_id)->get();
            if ($cartItems->isEmpty()) {
                Session::forget('checkout');
                return;
            }

            $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);

            if (Session::has('discounts')) {
                $discountData = Session::get('discounts');
                Session::put('checkout', [
                    'is_buy_now' => false,
                    'is_selected_checkout' => false,
                    'discount' => $discountData['discount'] ?? 0,
                    'subtotal' => $discountData['subtotal'] ?? $subtotal,
                    'tax' => 0,
                    'total' => $discountData['total'] ?? ($subtotal)
                ]);
            } else {
                Session::put('checkout', [
                    'is_buy_now' => false,
                    'is_selected_checkout' => false,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'total' => $subtotal,
                ]);
            }
        }
    }

    // ... (Method lainnya seperti remove_item, empty_cart, calculateDiscount, order_confirmation tetap sama)

    public function remove_item(Request $request)
    {
        $userId = Auth::id();
        $cartItemId = $request->id;

        CartItem::where('id', $cartItemId)->where('user_id', $userId)->delete();

        if (Session::has('coupon')) {
            $this->calculateDiscount();
        }

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang!');
    }

    public function empty_cart()
    {
        $userId = Auth::id();
        CartItem::where('user_id', $userId)->delete();

        // Hapus juga session terkait checkout jika ada
        Session::forget(['coupon', 'discounts', 'selected_checkout_items']);

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan!');
    }

    public function calculateDiscount()
    {
        if (!Session::has('coupon')) return;

        $user_id = Auth::id();
        $items = CartItem::where('user_id', $user_id)->get();

        if ($items->isEmpty()) {
            Session::forget('coupon');
            Session::forget('discounts');
            return;
        }

        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
        $discount = 0;

        $coupon = Session::get('coupon');
        if ($coupon['type'] == 'fixed') {
            $discount = $coupon['value'];
        } else {
            $discount = ($subtotal * $coupon['value']) / 100;
        }

        $subtotalAfterDiscount = $subtotal - $discount;
        $totalAfterDiscount = $subtotalAfterDiscount;

        Session::put('discounts', [
            'discount' => $discount,
            'subtotal' => $subtotalAfterDiscount,
            'tax' => 0,
            'total' => $totalAfterDiscount
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        $result = $request->input('result');

        // Simpan informasi yang Anda butuhkan ke dalam session
        Session::flash('transaction_details', [
            'transaction_id' => $result['transaction_id'],
            'payment_type'   => str_replace('_', ' ', $result['payment_type']),
            'status_message' => $result['status_message'],
            'gross_amount'   => number_format($result['gross_amount'], 0, ',', '.'),
        ]);

        // [BARU] Hapus item keranjang setelah pembayaran berhasil dari sisi client
        $user_id = Auth::id();
        $checkout = Session::get('checkout');

        if ($checkout) {
            if (isset($checkout['is_selected_checkout']) && $checkout['is_selected_checkout']) {
                $selectedItems = session('selected_checkout_items', collect());
                $itemIdsToDelete = $selectedItems->pluck('id')->toArray();
                CartItem::where('user_id', $user_id)->whereIn('id', $itemIdsToDelete)->delete();
            } else if (!$checkout['is_buy_now']) {
                // Ini akan menghapus semua item jika bukan "Beli Sekarang" dan bukan "Item terpilih"
                CartItem::where('user_id', $user_id)->delete();
            }
        }

        // Bersihkan session setelah pembayaran berhasil
        Session::forget(['checkout', 'coupon', 'discounts', 'buy_now_item', 'selected_checkout_items']);

        return response()->json(['status' => 'success']);
    }

    public function order_confirmation()
    {
        // Pastikan ada order_id di session, ini wajib untuk COD dan Transfer
        if (!Session::has('order_id')) {
            return redirect()->route('cart.index');
        }

        // Ambil order berdasarkan session
        $order = Order::find(Session::get('order_id'));

        // Ambil detail transaksi (ini hanya akan ada untuk pembayaran transfer)
        $transactionDetails = Session::get('transaction_details');

        // Hapus session order_id agar tidak bisa diakses lagi jika halaman di-refresh
        Session::forget('order_id');

        // Kirim kedua variabel ke view
        return view('order-confirmation', compact('order', 'transactionDetails'));
    }
}
