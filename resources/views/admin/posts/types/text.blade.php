<div>
    <div class="mb-3">
        <span>{{ $post->content }}</span>
        <div>
            <a href="{{ route('posts.index', ['parent_post_id' => $post->id]) }}">
                comments : <strong>{{ $post->children_count }}</strong>
            </a>
        </div>
    </div>
    <div class="row">
        @foreach($post->related as $related)
            <div class="col">
                {!! $related->draw() !!}
            </div>
        @endforeach
    </div>
</div>
