<div class="admin-grid">
    <label class="admin-form">
        <span>Vacancy title</span>
        <input class="admin-input" name="title" value="{{ old('title', $vacancy?->title) }}" required maxlength="255">
    </label>
    <label class="admin-form">
        <span>Place name / town</span>
        <input class="admin-input" name="place_name" value="{{ old('place_name', $vacancy?->place_name) }}" required maxlength="150">
    </label>
    <label class="admin-form">
        <span>Street / address</span>
        <input class="admin-input" name="address" value="{{ old('address', $vacancy?->address) }}" maxlength="255">
    </label>
    <label class="admin-form">
        <span>Apartment / unit type</span>
        <input class="admin-input" name="unit_type" value="{{ old('unit_type', $vacancy?->unit_type) }}" maxlength="80" placeholder="Bedsitter, 1 bedroom">
    </label>
    <label class="admin-form">
        <span>Monthly rent (KES)</span>
        <input class="admin-input" type="number" step="0.01" min="1" name="monthly_rent" value="{{ old('monthly_rent', $vacancy?->monthly_rent) }}" required>
    </label>
    <label class="admin-form">
        <span>Contact phone</span>
        <input class="admin-input" type="tel" name="contact_phone" value="{{ old('contact_phone', $vacancy?->contact_phone) }}" maxlength="30">
    </label>
    <label class="admin-form">
        <span>Bedrooms</span>
        <input class="admin-input" type="number" min="0" max="50" name="bedrooms" value="{{ old('bedrooms', $vacancy?->bedrooms) }}">
    </label>
    <label class="admin-form">
        <span>Bathrooms</span>
        <input class="admin-input" type="number" min="0" max="50" name="bathrooms" value="{{ old('bathrooms', $vacancy?->bathrooms) }}">
    </label>
    <label class="admin-form" style="grid-column:1/-1">
        <span>Details</span>
        <textarea class="admin-input" name="description" rows="5" maxlength="5000">{{ old('description', $vacancy?->description) }}</textarea>
    </label>
    <label class="admin-form" style="grid-column:1/-1">
        <span>Photos (up to 8, JPG/PNG/WEBP, 4 MB max each)</span>
        <input class="admin-input" type="file" name="images[]" multiple accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
    </label>
</div>
