@extends('layouts.app')

@section('content')
<main class="pt-20">
    <section class="shop-checkout container">
        <h2 class="page-title">Pengiriman dan Checkout</h2>
        <div class="checkout-steps">
            {{-- ... Step Indicator ... --}}
        </div>

        {{-- Menambahkan data-attribute untuk dibaca oleh JS dengan aman --}}
        <form id="checkout-form"
            name="checkout-form"
            action="{{ route('cart.place.an.order') }}"
            method="POST"
            data-total-weight="{{ $totalWeight ?? 0 }}"
            data-subtotal="{{ $total ?? 0 }}">
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
                                    <p>{{ $address->postal_code }}, {{ $address->country }}</p>
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
                            <label for="postal_code">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
                            @error('postal_code')
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
                    @if ($address)
                    <p>{{ $address->province }}, {{ $address->city }}, {{ $address->district }} ({{ $address->postal_code }})</p>
                    @endif

                    <div class="form-group mt-4">
                        <label for="courier">Pilih Kurir</label>
                        <select name="courier" id="courier" class="form-control" @if(empty($shippingCouriers)) disabled @endif>
                            <option value="">-- Pilih Kurir --</option>

                            @if(!empty($shippingCouriers))
                            @foreach($shippingCouriers as $sc)
                            @php
                            // dukung array atau object
                            if (is_array($sc)) {
                            $code = $sc['courier_code'] ?? $sc['courier_service_code'] ?? null;
                            $name = $sc['courier_name'] ?? $sc['courier_service_name'] ?? null;
                            $service = $sc['courier_service_code'] ?? null;
                            $serviceName = $sc['courier_service_name'] ?? null;
                            } else {
                            $code = $sc->courier_code ?? $sc->courier_service_code ?? null;
                            $name = $sc->courier_name ?? $sc->courier_service_name ?? null;
                            $service = $sc->courier_service_code ?? null;
                            $serviceName = $sc->courier_service_name ?? null;
                            }
                            $value = $code . ($service ? '|' . $service : '');
                            @endphp

                            @if($code)
                            <option value="{{ $value }}">
                                {{ $name }}{!! $serviceName ? ' - ' . e($serviceName) : '' !!}
                            </option>
                            @endif
                            @endforeach
                            @else
                            <option value="" disabled>Tidak ada kurir yang tersedia.</option>
                            @endif
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="service">Pilih Layanan</label>
                        <select name="service" id="service" class="form-control" disabled>
                            <option value="">-- Pilih kurir terlebih dahulu --</option>
                        </select>
                    </div>
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
                                        <th>Ongkos Kirim</th>
                                        <td class="text-right" id="shipping-cost">Rp. 0</td>
                                    </tr>
                                    <tr>
                                        <th>TOTAL</th>
                                        <td class="text-right"><strong id="total-price">Rp.
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
                        <input type="hidden" name="shipping_cost" id="shipping_cost_input" value="0">
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
    // Script untuk pembayaran Midtrans & Pembatalan Order
    $(document).ready(function() {
        let pendingOrderId = null;

        $('#checkout-form').on('submit', function(event) {
            var payButton = $('#pay-button');
            var selectedPaymentMethod = $('input[name="mode"]:checked').val();

            if (selectedPaymentMethod === 'transfer') {
                event.preventDefault();

                payButton.prop('disabled', true);
                payButton.html('Memproses...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    cache: false,
                    success: function(data) {
                        if (data.error || !data.snap_token) {
                            alert(data.error || 'Gagal mendapatkan token pembayaran.');
                            payButton.prop('disabled', false);
                            payButton.html('BUAT PESANAN');
                            return;
                        }

                        pendingOrderId = data.order_id;

                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                pendingOrderId = null;
                                sendPaymentResult(result);
                            },
                            onPending: function(result) {
                                pendingOrderId = null;
                                sendPaymentResult(result);
                            },
                            onError: function(result) {
                                alert("Pembayaran Gagal!");
                                cancelOrder(pendingOrderId);
                                payButton.prop('disabled', false);
                                payButton.html('BUAT PESANAN');
                            },
                            onClose: function() {
                                if (pendingOrderId) {
                                    console.log('Popup ditutup, membatalkan pesanan...');
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
            } else {
                payButton.prop('disabled', true);
                payButton.html('Memproses...');
            }
        });

        function sendPaymentResult(result) {
            $.ajax({
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

        function cancelOrder(orderId) {
            if (!orderId) {
                return;
            }
            console.log('Mencoba membatalkan pesanan ID: ' + orderId);
            $.ajax({
                url: "{{ route('cart.order.cancel') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: orderId
                },
                success: function(response) {
                    console.log('Respons pembatalan:', response.message);
                    pendingOrderId = null;
                },
                error: function(xhr) {
                    console.error('Gagal mengirim permintaan pembatalan:', xhr.responseText);
                    pendingOrderId = null;
                }
            });
        }
    });

    // Script untuk Ongkos Kirim Biteship
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutForm = document.getElementById('checkout-form');
        const courierSelect = document.getElementById('courier');
        const serviceSelect = document.getElementById('service');

        // Membaca data dari form yang sudah di-render oleh PHP
        const totalWeight = parseInt(checkoutForm.dataset.totalWeight) || 0;
        const subtotal = parseInt(checkoutForm.dataset.subtotal) || 0;

        courierSelect.addEventListener('change', function() {
            let courier = this.value;
            let destination = "{{ optional(auth()->user()->address)->postal_code ?? '' }}";

            serviceSelect.innerHTML = '<option value="">-- Memuat layanan... --</option>';
            serviceSelect.disabled = true;
            updateTotal(0);

            if (!courier) {
                serviceSelect.innerHTML = '<option value="">-- Pilih kurir terlebih dahulu --</option>';
                return;
            }

            if (!destination) {
                alert('Harap isi atau pilih alamat pengiriman dengan kode pos yang valid terlebih dahulu.');
                serviceSelect.innerHTML = '<option value="">-- Alamat tidak valid --</option>';
                courierSelect.value = '';
                return;
            }

            fetch(`{{ route('checkout.services') }}?courier=${courier}&destination=${destination}&weight=${totalWeight}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mengambil data layanan. Kode Status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    serviceSelect.innerHTML = '<option value="">-- Pilih layanan --</option>';
                    if (data.pricing && data.pricing.length > 0) {
                        data.pricing.forEach(service => {
                            let option = document.createElement('option');
                            option.value = service.price;
                            option.dataset.serviceName = service.courier_service_name;
                            option.textContent = `${service.courier_service_name} - Rp ${service.price.toLocaleString()}`;
                            serviceSelect.appendChild(option);
                        });
                    } else {
                        serviceSelect.innerHTML = '<option value="">-- Tidak ada layanan tersedia --</option>';
                    }
                    serviceSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error saat mengambil ongkos kirim:', error);
                    serviceSelect.innerHTML = '<option value="">-- Gagal memuat layanan --</option>';
                });
        });

        serviceSelect.addEventListener('change', function() {
            let shippingCost = parseInt(this.value) || 0;
            updateTotal(shippingCost);
        });

        function updateTotal(shippingCost) {
            let total = subtotal + shippingCost;

            document.getElementById('shipping-cost').innerText = `Rp. ${shippingCost.toLocaleString()}`;
            document.getElementById('total-price').innerHTML = `<strong>Rp. ${total.toLocaleString()}</strong>`;
            document.getElementById('shipping_cost_input').value = shippingCost;
        }
    });
</script>
@endpush
@endsection