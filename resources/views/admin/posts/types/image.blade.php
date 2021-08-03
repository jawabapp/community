<div class="row">
    <div class="col">
        <img src="{{ $post->content }}" style="max-width: 200px; max-height: 200px;">
        <div>
            <a href="{{ route('community.posts.index', ['parent_post_id' => $post->id]) }}">
                comments : <strong>{{ $post->children_count }}</strong>
            </a>
        </div>
    </div>
    @foreach($post->related as $related)
        <div class="col">
            {!! $related->draw() !!}
        </div>
    @endforeach
</div>
