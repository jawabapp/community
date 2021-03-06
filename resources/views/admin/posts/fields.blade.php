<?php
/**
 * @var $item \Jawabapp\Community\Models\StaticPage
 */
?>
<div class="form-group row">
    <label class="col-md-2 col-form-label">Post</label>

    <div class="col-md-8">
        {!! $item->draw() !!}
    </div>
</div>

<div class="form-group row">
    <label for="post_tags" class="col-md-2 col-form-label">Post Tagss</label>

    <div class="col-md-8">
        <community-multiple-select
            api-search="/{{config('community.route.prefix')}}/admin/api/search-tags"
            api-selected="/{{config('community.route.prefix')}}/admin/api/selected-tags"
            label="Hash-Tags"
            name="hashtags"
            preselect="{{$item->tags->pluck('id')}}"
        ></community-multiple-select>

        @if ($errors->has('post_tags'))
            <span class="invalid-feedback"><strong>{{ $errors->first('post_tags') }}</strong></span>
        @endif
    </div>
</div>

<div class="form-group row">
    <div class="col-md-8 offset-md-2">
        <button type="submit" class="btn btn-primary">
            Save
        </button>
        <a href="{{route('community.posts.index')}}" class="btn btn-default">Cancel</a>
    </div>
</div>
