<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px">
    <label class="form-group">Listing title
        <input class="form-control" name="title" value="{{ old('title', $accommodation?->title) }}" required maxlength="255">
    </label>
    <label class="form-group">Place / town
        <input class="form-control" name="place_name" value="{{ old('place_name', $accommodation?->place_name) }}" required maxlength="150">
    </label>
    <label class="form-group">Apartment / property (optional)
        <select class="form-control" name="property_id">
            <option value="">Not linked to an apartment</option>
            @foreach($properties as $property)
                <option value="{{ $property->id }}"
                    @selected(old('property_id', $accommodation?->property_id) == $property->id)>
                    {{ $property->property_name }}
                </option>
            @endforeach
        </select>
    </label>
    <label class="form-group" style="grid-column:1/-1">Street address
        <input class="form-control" name="address" value="{{ old('address', $accommodation?->address) }}" maxlength="255">
    </label>
    <label class="form-group">Price per night (KES)
        <input class="form-control" type="number" name="price_per_night" min="1" step="0.01"
               value="{{ old('price_per_night', $accommodation?->price_per_night) }}" required>
    </label>
    <label class="form-group">Maximum guests
        <input class="form-control" type="number" name="max_guests" min="1" max="100"
               value="{{ old('max_guests', $accommodation?->max_guests ?? 1) }}" required>
    </label>
    <label class="form-group">Bedrooms
        <input class="form-control" type="number" name="bedrooms" min="0" max="50"
               value="{{ old('bedrooms', $accommodation?->bedrooms) }}">
    </label>
    <label class="form-group">Bathrooms
        <input class="form-control" type="number" name="bathrooms" min="0" max="50"
               value="{{ old('bathrooms', $accommodation?->bathrooms) }}">
    </label>
    <label class="form-group" style="grid-column:1/-1">Description
        <textarea class="form-control" name="description" rows="5" maxlength="10000">{{ old('description', $accommodation?->description) }}</textarea>
    </label>
    <label class="form-group" style="grid-column:1/-1">Photos (up to 8, JPG/PNG/WEBP, 4 MB max each)
        <input class="form-control" type="file" name="images[]" multiple
               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small class="form-help">Use Ctrl or Shift to select several photos at once.</small>
    </label>
</div>
