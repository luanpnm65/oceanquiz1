@php
    $oceanZones = [
        'Tầng Ánh Sáng (0m - 200m)',
        'Tầng Chạng Vạng (200m - 1,000m)',
        'Tầng Nửa Đêm (1,000m - 4,000m)',
        'Tầng Đáy Thẳm (4,000m - 6,000m)',
        'Tầng Hố Thẳm (6,000m - 11,000m)',
    ];
    $selectedZone = old('ocean_zone', isset($set) ? $set->ocean_zone : null);
@endphp

<div class="field">
    <label class="field__label" for="ocean_zone">Tầng đại dương</label>
    <select class="form-control" id="ocean_zone" name="ocean_zone" required>
        <option value="" disabled @selected(blank($selectedZone))>Chọn tầng đại dương</option>
        @foreach ($oceanZones as $zone)
            <option value="{{ $zone }}" @selected($selectedZone === $zone)>{{ $zone }}</option>
        @endforeach
    </select>
    <p class="field__hint">Chọn tầng khớp với độ sâu của bài viết đã lưu.</p>
</div>
