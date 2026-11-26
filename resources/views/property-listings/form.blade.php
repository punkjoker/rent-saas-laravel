<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if(!$listing)
        <label class="block md:col-span-2">
                <span class="text-sm font-semibold">Existing property (optional if creating a new one)</span>
                <select name="property_id" class="form-control mt-1">
                <option value="">Select a property</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}" @selected(old('property_id') == $property->id)>
                        {{ $property->property_name }} · {{ $property->town ?? $property->address }}
                    </option>
                @endforeach
            </select>
        </label>
        <div class="md:col-span-2" style="text-align:center;color:#718078;font-size:12px">— or enter a new property —</div>
        <label class="block">
            <span class="text-sm font-semibold">New property name</span>
            <input type="text" name="property_name" value="{{ old('property_name') }}"
                   maxlength="255" class="form-control mt-1" placeholder="e.g. Palm Residency">
        </label>
        <label class="block">
            <span class="text-sm font-semibold">Town (optional)</span>
            <input type="text" name="property_town" value="{{ old('property_town') }}"
                   maxlength="255" class="form-control mt-1">
        </label>
        <label class="block md:col-span-2">
            <span class="text-sm font-semibold">Property address (optional)</span>
            <input type="text" name="property_address" value="{{ old('property_address') }}"
                   maxlength="255" class="form-control mt-1">
        </label>
    @else
        <div class="md:col-span-2 text-sm text-gray-600">
            Advertising: <strong>{{ $listing->property->property_name }}</strong>
            · {{ $listing->property->town ?? $listing->property->address }}
        </div>
    @endif

    <label class="block md:col-span-2">
        <span class="text-sm font-semibold">Advertisement title</span>
        <input type="text" name="title" value="{{ old('title', $listing?->title) }}"
               maxlength="255" required class="form-control mt-1"
               placeholder="Bright 2-bedroom apartment in Westlands">
    </label>

    <label class="block md:col-span-2">
        <span class="text-sm font-semibold">Description</span>
        <textarea name="description" rows="5" maxlength="10000" class="form-control mt-1"
                  placeholder="Describe the property and its features">{{ old('description', $listing?->description) }}</textarea>
    </label>

    <label class="block">
        <span class="text-sm font-semibold">Monthly rent (KES)</span>
        <input type="number" name="monthly_rent" value="{{ old('monthly_rent', $listing?->monthly_rent) }}"
               min="1" step="0.01" required class="form-control mt-1">
    </label>
    <label class="block">
        <span class="text-sm font-semibold">Contact phone (optional)</span>
        <input type="tel" name="contact_phone" value="{{ old('contact_phone', $listing?->contact_phone) }}"
               maxlength="30" class="form-control mt-1">
    </label>
    <label class="block">
        <span class="text-sm font-semibold">Bedrooms</span>
        <input type="number" name="bedrooms" value="{{ old('bedrooms', $listing?->bedrooms) }}"
               min="0" max="50" class="form-control mt-1">
    </label>
    <label class="block">
        <span class="text-sm font-semibold">Bathrooms</span>
        <input type="number" name="bathrooms" value="{{ old('bathrooms', $listing?->bathrooms) }}"
               min="0" max="50" class="form-control mt-1">
    </label>
    <label class="block md:col-span-2">
        <span class="text-sm font-semibold">Photos (select up to 8 total, JPG/PNG/WEBP, max 4 MB each)</span>
        <input type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
               class="form-control mt-1" data-photo-input>
        <small class="form-help" data-photo-count>Choose multiple files using Ctrl or Shift.</small>
    </label>
</div>
<script>
    const propertySelect = document.querySelector('[name="property_id"]');
    const propertyName = document.querySelector('[name="property_name"]');
    if (propertySelect && propertyName) {
        const syncPropertyChoice = () => {
            propertySelect.required = !propertyName.value.trim();
            propertyName.required = !propertySelect.value;
        };
        propertySelect.addEventListener('change', syncPropertyChoice);
        propertyName.addEventListener('input', syncPropertyChoice);
        syncPropertyChoice();
    }

    document.querySelectorAll('[data-photo-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const count = input.files.length;
            input.parentElement.querySelector('[data-photo-count]').textContent =
                count ? `${count} photo${count === 1 ? '' : 's'} selected` : 'Choose multiple files using Ctrl or Shift.';
        });
    });
</script>
