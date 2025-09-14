{{-- resources/views/partials/courier_dropdown.blade.php --}}
<div class="form-group mt-4">
    <label for="courier">Pilih Kurir</label>
    <select name="courier" id="courier" class="form-control" @if(empty($shippingCouriers)) disabled @endif>
        <option value="">-- Pilih Kurir --</option>
        @if(isset($shippingCouriers) && is_array($shippingCouriers) && count($shippingCouriers) > 0)
            @foreach($shippingCouriers as $courier)
                @if(isset($courier['code']) && isset($courier['name']))
                <option value="{{ $courier['code'] }}">
                    {{ $courier['name'] }}
                </option>
                @endif
            @endforeach
        @else
            <option value="" disabled>Tidak ada kurir yang tersedia.</option>
        @endif
    </select>
    @if(empty($shippingCouriers))
        <small class="text-danger mt-1 d-block">Gagal memuat daftar kurir.</small>
    @endif
</div>