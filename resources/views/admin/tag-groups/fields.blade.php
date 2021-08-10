<?php
/**
 * @var $item \Jawabapp\Community\Models\TagGroup
 */
?>
<div class="form-group row">
    <label for="parent_id" class="col-md-2 col-form-label">Parent</label>

    <div class="col-md-8">
        <select id="parent_id" class="form-control {{ $errors->has('parent_id') ? 'is-invalid' : '' }}" name="parent_id">
            <option value="">Select One</option>
            @foreach(\Jawabapp\Community\Models\TagGroup::whereNull('parent_id')->get() as $tagGroup)
            <option value="{{$tagGroup->id}}" {{ $tagGroup->id == old('parent_id', $item->parent_id ?? null) ? 'selected' : '' }}>{{$tagGroup->name[config('app.locale')] ?? '-'}}</option>
            @endforeach
        </select>

        @if ($errors->has('parent_id'))
            <span class="invalid-feedback"><strong>{{ $errors->first('parent_id') }}</strong></span>
        @endif
    </div>
</div>

@foreach(config('app.locales') as $locale_key => $locale_value)
    <div class="form-group row">
        <label for="name-{{$locale_key}}" class="col-md-2 col-form-label">Name ({{$locale_value}})</label>

        <div class="col-md-8">
            <input id="name-{{$locale_key}}" type="text" class="form-control {{ $errors->has("name.{$locale_key}") ? 'is-invalid' : '' }}" name="name[{{$locale_key}}]" value="{{ old("name.{$locale_key}", $item->name[$locale_key] ?? null) }}">

            @if ($errors->has("name.{$locale_key}"))
                <span class="invalid-feedback"><strong>{{ $errors->first("name.{$locale_key}") }}</strong></span>
            @endif
        </div>
    </div>
@endforeach

<div class="form-group row">
    <label for="image_file" class="col-md-2 col-form-label">Image</label>

    <div class="col-md-8">
        @if (isset($item->image) && $item->image)
            <div class="mb-3">
                <img src="{{$item->image}}" class="img-thumbnail"
                     style="max-height:200px; max-weight:200px;"/>
            </div>
        @endif
        <input type="file" id="image_file"
               class="form-control-file{{ $errors->has('image_file') ? ' is-invalid' : '' }}"
               name="image_file"/>
        @if ($errors->has('image_file'))
            <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('image_file') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group row">
    <label for="post_tags" class="col-md-2 col-form-label">Services</label>

    <div class="col-md-8">
        <multiple-select
            api-search="/admin/api/search-services"
            api-selected="/admin/api/selected-services"
            label="Services"
            name="services"
            preselect="{{ json_encode(old('services', $item->services ?? [])) }}"
        ></multiple-select>

        @if ($errors->has('services'))
            <span class="invalid-feedback"><strong>{{ $errors->first('services') }}</strong></span>
        @endif
    </div>
</div>

<hr>

<div class="form-group row">
    <label for="order" class="col-md-2 col-form-label">Order</label>

    <div class="col-md-8">
        <input id="order" type="text" class="form-control {{ $errors->has('order') ? 'is-invalid' : '' }}" name="order" value="{{ old('order', trans($item->order ?? '')) }}">

        @if ($errors->has('order'))
            <span class="invalid-feedback"><strong>{{ $errors->first('order') }}</strong></span>
        @endif
    </div>
</div>

<div class="form-group row">
    <label for="country_code" class="col-md-2 col-form-label">Country</label>

    <div class="col-md-8">
        <select id="country_code" class="form-control {{ $errors->has('country_code') ? 'is-invalid' : '' }}" name="country_code">
            <option value="">Global</option>
            @foreach(config('community.user_class')::distinct()->select(config('community.country_code_field_name'))->where(config('community.country_code_field_name'), '!=', '')->get()->pluck(config('community.country_code_field_name'))->all() as $phoneCountry)
                <option value="{{$phoneCountry}}" {{ $phoneCountry == old(config('community.country_code_field_name'), $item->country_code ?? null) ? 'selected' : '' }}>{{$phoneCountry}}</option>
            @endforeach
        </select>

        @if ($errors->has(config('community.country_code_field_name')))
            <span class="invalid-feedback"><strong>{{ $errors->first(config('community.country_code_field_name')) }}</strong></span>
        @endif
    </div>
</div>

<div class="form-group row">
    <label for="is_published" class="col-md-2 col-form-label">Publish</label>

    <div class="col-md-8">
        <?php $is_published = old('is_published', $item->is_published ?? true) ?>
        <select id="is_published" class="form-control {{ $errors->has('is_published') ? 'is-invalid' : '' }}" name="is_published">
            <option value="0"{{ $is_published ? '' : ' selected' }}> No</option>
            <option value="1"{{ $is_published ? ' selected' : '' }}> Yes</option>
        </select>

        @if ($errors->has('is_published'))
            <span class="invalid-feedback"><strong>{{ $errors->first('is_published') }}</strong></span>
        @endif
    </div>
</div>

<div class="form-group row">
    <label for="hide_in_public" class="col-md-2 col-form-label">Hide in public</label>

    <div class="col-md-8">
        <?php $hide_in_public = old('hide_in_public', $item->hide_in_public ?? false) ?>
        <select id="hide_in_public" class="form-control {{ $errors->has('hide_in_public') ? 'is-invalid' : '' }}" name="hide_in_public">
            <option value="0"{{ $hide_in_public ? '' : ' selected' }}> No</option>
            <option value="1"{{ $hide_in_public ? ' selected' : '' }}> Yes</option>
        </select>

        @if ($errors->has('hide_in_public'))
            <span class="invalid-feedback"><strong>{{ $errors->first('hide_in_public') }}</strong></span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-8 offset-md-2">
        <button type="submit" class="btn btn-primary">
            Save
        </button>
        <a href="{{route('community.tag-groups.index')}}" class="btn btn-default">Cancel</a>
    </div>
</div>
