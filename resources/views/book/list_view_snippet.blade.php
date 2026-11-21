{{-- Insert near the Add/Register book button in resources/views/book/list.blade.php --}}
@auth
    @if(Auth::user()->canPublishBooks())
        <a class="btn btn-primary" href="{{ route('book.create') }}">{{ __('Register Book') }}</a>
    @endif
@endauth

{{-- Within each $book row: only the owner or admin may edit/delete. --}}
@auth
    @if(Auth::user()->canManageBook($book))
        <a href="{{ route('book.edit', ['book' => $book->id]) }}">{{ __('Edit') }}</a>
        <form action="{{ route('book.destroy', ['book' => $book->id]) }}" method="POST" class="d-inline"
              data-confirm="{{ __('Would you like to delete?') }}"
              onsubmit="return confirm(this.dataset.confirm);">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm" type="submit">{{ __('Delete') }}</button>
        </form>
    @endif
@endauth
