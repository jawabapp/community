<div class="row">
    <div class="col">
        <span>Video not Supported</span>
        <div>
            <a href="{{ $post->content }}" target="_blank">{{ $post->content }}</a>
        </div>
        <div>
            <a href="{{ route('posts.index', ['parent_post_id' => $post->id]) }}">
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
