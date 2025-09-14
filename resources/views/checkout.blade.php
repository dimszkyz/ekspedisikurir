@extends('layouts.app')

@section('content')
<main class="pt-20">
    <section class="shop-checkout container">
        <h2 class="page-title">Pengiriman dan Checkout</h2>
        <div class="checkout-steps">
            {{-- ... Step Indicator ... --}}
        </div>
        <div class="form-group">
            <label for="courier">Pilih Kurir</label>
            <select name="courier" id="courier" class="form-control">
                <option value="">-- Pilih Kurir --</option>
                @foreach($couriers as $courier)
                <option value="{{ $courier['code'] }}">
                    {{ $courier['name'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="service">Pilih Layanan</label>
            <select name="service" id="service" class="form-control">
                <option value="">-- Pilih layanan --</option>
            </select>
        </div>



        <form id="checkout-form" name="checkout-form" action="{{ route('cart.place.an.order') }}" method="POST">
            @csrf
            <div class="checkout-form">
                <div class="billing-info__wrapper">
                    <div class="row">
                        <div class="col-6">
                            <h4>DETAIL PENGIRIMAN</h4>
                        </div>
                        @if ($address)
                        <div class="col-6 text-right">
                            <a href="{{ route('user.address.index') }}" class="btn btn-link fw-semi-bold mt-4"
                                style="text-decoration: underline; background: none; border: none;">Ubah Alamat</a>

                        </div>
                        @endif
                    </div>

                    {{-- Jika alamat sudah ada, tampilkan detailnya --}}
                    @if ($address)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="my-account__address-list">
                                <div class="my-account__address-item__detail">
                                    <p><strong>{{ $address->name }}</strong></p>
                                    <p>{{ $address->phone }}</p>
                                    <p>{{ $address->address }}</p>
                                    <p>{{ $address->landmark }}</p>
                                    <p>{{ $address->locality }}, {{ $address->city }}, {{ $address->state }}</p>
                                    <p>{{ $address->zip }}, {{ $address->country }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Jika alamat belum ada, tampilkan form untuk mengisinya --}}
                    @else
                    <div class="row mt-4" id="address-form-fields">
                        <p>Anda belum memiliki alamat tersimpan. Silakan isi detail di bawah ini.</p>
                        <p class="mb-3">Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>

                        <div class="col-md-6 mb-3">
                            <label for="name">Nama Penerima <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name', auth()->user()->name) }}"
                                required>
                            @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone">No. Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                id="phone" name="phone" value="{{ old('phone') }}" required>
                            @error('phone')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="address">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                required>{{ old('address') }}</textarea>
                            @error('address')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="landmark">Patokan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('landmark') is-invalid @enderror"
                                id="landmark" name="landmark" value="{{ old('landmark') }}" required>
                            @error('landmark')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="locality">Kelurahan/Desa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('locality') is-invalid @enderror"
                                id="locality" name="locality" value="{{ old('locality') }}" required>
                            @error('locality')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label>Tipe Alamat <span class="text-danger">*</span></label>
                            <div class="d-flex mt-2">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="type" id="type_rumah"
                                        value="Rumah" {{ old('type', 'Rumah') == 'Rumah' ? 'checked' : '' }}
                                        required>
                                    <label class="form-check-label" for="type_rumah">Rumah</label>
                                </div>
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="type" id="type_kantor"
                                        value="Kantor" {{ old('type') == 'Kantor' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_kantor">Kantor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="type_lainnya"
                                        value="Lainnya" {{ old('type') == 'Lainnya' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="type_lainnya">Lainnya</label>
                                </div>
                            </div>
                            @error('type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="city">Kota/Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                id="city" name="city" value="{{ old('city') }}" required>
                            @error('city')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror"
                                id="state" name="state" value="{{ old('state') }}" required>
                            @error('state')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zip">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('zip') is-invalid @enderror"
                                id="zip" name="zip" value="{{ old('zip') }}" required>
                            @error('zip')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country">Negara <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                id="country" name="country" value="{{ old('country', 'Indonesia') }}"
                                required>
                            @error('country')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif
                </div>

                <div class="checkout__totals-wrapper">
                    <div class="sticky-content">
                        <div class="checkout__totals">
                            <h3>Pesanan Anda</h3>
                            <table class="checkout-cart-items">
                                <thead>
                                    <tr>
                                        <th>PRODUK</th>
                                        <th class="text-right">SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->product->name }} x {{ $item->quantity }}
                                        </td>
                                        <td class="text-right">
                                            Rp.
                                            {{ number_format($item->subtotal ?? $item->price * $item->quantity, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <table class="checkout-totals">
                                <tbody>
                                    @if (Session::has('discounts'))
                                    <tr>
                                        <th>Subtotal</th>
                                        <td class="text-right">Rp.
                                            {{ number_format(Session::get('discounts')['subtotal'] + Session::get('discounts')['discount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Diskon ({{ Session::get('coupon')['code'] }})</th>
                                        <td class="text-right">- Rp.
                                            {{ number_format(Session::get('discounts')['discount'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @else
                                    <tr>
                                        <th>SUBTOTAL</th>
                                        <td class="text-right">Rp. {{ number_format($subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <th>TOTAL</th>
                                        <td class="text-right"><strong>Rp.
                                                {{ number_format($total, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                        <div class="checkout__payment-methods">
                            <div class="form-check">
                                <input class="form-check-input form-check-input_fill" type="radio" name="mode"
                                    id="mode3" value="cod" checked>
                                <label class="form-check-label" for="mode3">
                                    Cash On Delivery (COD)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input form-check-input_fill" type="radio" name="mode"
                                    id="mode4" value="transfer">
                                <label class="form-check-label" for="mode4">
                                    Transfer Bank
                                </label>
                            </div>
                            <div class="policy-text">
                                Data pribadi Anda akan digunakan untuk memproses pesanan Anda...
                            </div>
                            @error('mode')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" id="pay-button" class="btn btn-primary btn-checkout">BUAT
                            PESANAN</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</main>

@push('scripts')
{{-- 1. Muat library Snap.js dari Midtrans --}}
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="{{ config('midtrans.client_key') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // [BARU] Variabel global untuk menyimpan ID pesanan yang sedang diproses
        let pendingOrderId = null;

        $('#checkout-form').on('submit', function(event) {
            var payButton = $('#pay-button');
            var selectedPaymentMethod = $('input[name="mode"]:checked').val();

            if (selectedPaymentMethod === 'transfer') {
                event.preventDefault(); // Hentikan form submission untuk AJAX

                payButton.prop('disabled', true);
                payButton.html('Memproses...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    cache: false,
                    success: function(data) {
                        // Aktifkan kembali tombol jika ada error dari server
                        if (data.error || !data.snap_token) {
                            alert(data.error || 'Gagal mendapatkan token pembayaran.');
                            payButton.prop('disabled', false);
                            payButton.html('BUAT PESANAN');
                            return;
                        }

                        // [MODIFIKASI] Simpan Order ID yang diterima dari server
                        pendingOrderId = data.order_id;

                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                pendingOrderId =
                                    null; // Reset ID karena berhasil
                                sendPaymentResult(result);
                            },
                            onPending: function(result) {
                                pendingOrderId =
                                    null; // Reset ID karena pending
                                sendPaymentResult(result);
                            },
                            onError: function(result) {
                                alert("Pembayaran Gagal!");
                                // [MODIFIKASI] Panggil fungsi pembatalan
                                cancelOrder(pendingOrderId);
                                payButton.prop('disabled', false);
                                payButton.html('BUAT PESANAN');
                            },
                            onClose: function() {
                                // [MODIFIKASI] Panggil fungsi pembatalan jika popup ditutup
                                // Hanya panggil jika pembayaran tidak sukses/pending
                                if (pendingOrderId) {
                                    console.log(
                                        'Popup ditutup, membatalkan pesanan...'
                                    );
                                    cancelOrder(pendingOrderId);
                                }
                                payButton.prop('disabled', false);
                                payButton.html('BUAT PESANAN');
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert("Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.");
                        payButton.prop('disabled', false);
                        payButton.html('BUAT PESANAN');
                    }
                });
            } else { // Jika metode 'cod' atau lainnya
                payButton.prop('disabled', true);
                payButton.html('Memproses...');
                // Form akan submit secara normal
            }
        });

        function sendPaymentResult(result) {
            $.ajax({
                // Pastikan route ini benar, sesuaikan jika perlu
                url: "{{ route('payment.success') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    result: result
                },
                success: function() {
                    window.location.href = "{{ route('cart.order.confirmation') }}";
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal memproses hasil pembayaran di server.');
                }
            });
        }

        // [BARU] Fungsi untuk mengirim request pembatalan order ke server
        function cancelOrder(orderId) {
            if (!orderId) {
                return; // Jangan lakukan apa-apa jika tidak ada order ID
            }

            console.log('Mencoba membatalkan pesanan ID: ' + orderId);

            $.ajax({
                url: "{{ route('cart.order.cancel') }}", // Pastikan route ini ada di web.php
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: orderId
                },
                success: function(response) {
                    console.log('Respons pembatalan:', response.message);
                    pendingOrderId = null; // Reset ID setelah diproses
                },
                error: function(xhr) {
                    console.error('Gagal mengirim permintaan pembatalan:', xhr.responseText);
                    pendingOrderId = null; // Reset ID
                }
            });
        }
    });
</script>
@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let destination_area_id = "{{ $user->address->area_id ?? '' }}";

        if (!destination_area_id) {
            console.warn("Destination area_id belum ada");
            return;
        }

        fetch(`/shipping/rates?destination=${destination_area_id}`)
            .then(res => res.json())
            .then(data => {
                console.log("Rates dari server:", data); // cek di console browser

                let select = document.getElementById("shipping_option");
                select.innerHTML = ""; // reset isi

                if (data.pricing && data.pricing.length > 0) {
                    data.pricing.forEach(rate => {
                        let option = document.createElement("option");
                        option.value = JSON.stringify({
                            courier: rate.courier_name,
                            service: rate.courier_service_name,
                            cost: rate.price
                        });
                        option.textContent = `${rate.courier_name.toUpperCase()} - ${rate.courier_service_name} (Rp ${rate.price.toLocaleString()})`;
                        select.appendChild(option);
                    });
                } else {
                    let option = document.createElement("option");
                    option.value = "";
                    option.textContent = "Tidak ada layanan tersedia";
                    select.appendChild(option);
                }
            })
            .catch(err => console.error("Error ambil ongkir:", err));
    });
</script>
<script>
document.getElementById('courier').addEventListener('change', function() {
    let courier = this.value;
    let destination = "{{ auth()->user()->address->zip ?? '40115' }}"; // contoh pakai kode pos user
    let totalWeight = {{ $cartItems->sum(fn($i) => $i->product->weight * $i->quantity) }}; // pastikan ada field `weight`

    if(courier) {
        fetch("{{ route('checkout.services') }}?courier=" + courier + "&destination=" + destination + "&weight=" + totalWeight)
            .then(res => res.json())
            .then(data => {
                let serviceSelect = document.getElementById('service');
                serviceSelect.innerHTML = '<option value="">-- Pilih layanan --</option>';
                data.pricing.forEach(service => {
                    serviceSelect.innerHTML += `<option value="${service.courier_service_name}" data-price="${service.price}">
                        ${service.courier_service_name} - Rp${service.price}
                    </option>`;
                });
            });
    }
});
</script>

@endsection

@endpush
@endsection