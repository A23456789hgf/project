<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="code" class="form-label">الكود</label>
            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                   id="code" name="code" value="{{ old('code', $stage->code ?? '') }}" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="type" class="form-label">النوع</label>
            <input type="text" class="form-control @error('type') is-invalid @enderror" 
                   id="type" name="type" value="{{ old('type', $stage->type ?? '') }}" required>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name_ar" class="form-label">الاسم (عربي)</label>
            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" 
                   id="name_ar" name="name_ar" value="{{ old('name_ar', $stage->name_ar ?? '') }}" required>
            @error('name_ar')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="name_en" class="form-label">الاسم (إنجليزي)</label>
            <input type="text" class="form-control @error('name_en') is-invalid @enderror" 
                   id="name_en" name="name_en" value="{{ old('name_en', $stage->name_en ?? '') }}">
            @error('name_en')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="description_ar" class="form-label">الوصف (عربي)</label>
    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
              id="description_ar" name="description_ar" rows="3">{{ old('description_ar', $stage->description_ar ?? '') }}</textarea>
    @error('description_ar')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description_en" class="form-label">الوصف (إنجليزي)</label>
    <textarea class="form-control @error('description_en') is-invalid @enderror" 
              id="description_en" name="description_en" rows="3">{{ old('description_en', $stage->description_en ?? '') }}</textarea>
    @error('description_en')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="order" class="form-label">الترتيب</label>
            <input type="number" class="form-control @error('order') is-invalid @enderror" 
                   id="order" name="order" value="{{ old('order', $stage->order ?? '') }}" required>
            @error('order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3 pt-4">
            <div class="form-check">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                       {{ old('is_active', $stage->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    نشط
                </label>
            </div>
        </div>
    </div>
</div>