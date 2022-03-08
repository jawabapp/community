@extends('community::layouts.app')

@section('content')
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container .select2-selection--single{
                height: calc(1.5em + 0.75rem + 2px);
                padding: 0.375rem 0.75rem;
                display: flex;
                align-items: center;
                border: 1px solid #ced4d9;
            }
        </style>
    @endpush
    @if($errors->any())
        <div class="alert alert-danger">
            <p><strong>Opps Something went wrong</strong></p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card">
        <div class="card-header">
            Add / Static Page
        </div>

        <div class="card-body">
            <form method="POST" action="{{route('community.posts.store_comment', ['parent_post_id' => request()->parent_post_id])}}" class="form-horizontal">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="hash">User</label>
                    <select required name="account_id" class="form-control select2" style="width: 100%;"></select>
                </div>
                <input type="hidden" name="parent_post_id" value="{{request()->parent_post_id}}">
                <div class="form-group">
                    <label for="hash">Comment</label>
                    <textarea required name="post" id="post" class="form-control" cols="30" rows="4"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>
    </div>
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $("select.select2").select2({
                placeholder: 'Select User',
                minimumInputLength: 3,
                ajax: {
                    url: "{{url('/en/admin/api/user/search')}}",
                    dataType: 'json',
                    data: (params) => {
                        return {
                            phone: params.term,
                        }
                    },
                    processResults: (data, params) => {
                        const results = data.data.map(item => {
                            return {
                                id: item.id,
                                text: item.phone
                            };
                        });
                        return {
                            results: results,
                        }
                    },
                },
            });
        });
    </script>
@endpush

@endsection
